<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ChatbotLog;
use App\Models\Order;
use App\Models\PolicyPage;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class ChatBotController extends Controller
{
    /**
     * Handle incoming chatbot query with rich store knowledge and live database workflows.
     */
    public function query(Request $request): JsonResponse
    {
        $message = trim((string) $request->input('message', ''));
        $cart = $request->input('cart', []);
        $wishlist = $request->input('wishlist', []);
        $history = is_array($request->input('history')) ? $request->input('history') : [];
        $user = Auth::user();
        $userName = $user ? (explode(' ', trim($user->name))[0] ?: 'User') : 'User';

        if (empty($message)) {
            return response()->json([
                'reply' => "Hello {$userName}! How can I assist you with your tech needs today? 😊",
                'anim' => 'idle',
            ]);
        }

        $lower = $this->normalizeQuery(strtolower($message));

        // 0. ACCOUNT, REGISTER, LOGIN & PASSWORD RESET WORKFLOW (English & Singlish)
        if ($this->matches($lower, [
            'register', 'sign up', 'signup', 'create account', 'new account',
            'login', 'log in', 'log ven', 'logven', 'sign in', 'signin',
            'forgot password', 'reset password', 'password', 'how to join',
        ])) {
            return $this->handleAccountGuideWorkflow($user, $userName, $lower);
        }

        // 1. ORDERS WORKFLOW (English & Singlish)
        if ($this->matches($lower, [
            'my order', 'track order', 'order status', 'where is my order', 'orders',
            'my purchase', 'bought', 'ord-', 'mage order', 'order eka', 'order koheda',
        ])) {
            return $this->handleOrdersWorkflow($user, $userName, $lower);
        }

        // 2. CART WORKFLOW
        if ($this->matches($lower, ['my cart', 'in my cart', 'shopping cart', 'cart total', 'what is in my cart', 'checkout', 'bag', 'basket', 'cart eka'])) {
            return $this->handleCartWorkflow($userName, $cart);
        }

        // 3. WISHLIST / FAVOURITES WORKFLOW
        if ($this->matches($lower, ['wishlist', 'favourite', 'favorite', 'saved products', 'saved items'])) {
            return $this->handleWishlistWorkflow($userName, $wishlist);
        }

        // 4. CATEGORIES & SUBCATEGORIES WORKFLOW
        if ($this->matches($lower, ['category', 'categories', 'subcategor', 'what do you sell', 'what products do you have', 'product range', 'items available', 'monawada thiyenne', 'monada thiyenne'])) {
            return $this->handleCategoriesWorkflow($userName);
        }

        // 5. NEW ARRIVALS WORKFLOW
        if ($this->matches($lower, ['new arrival', 'newest', 'latest product', 'new stock', 'just arrived', 'recent items', 'aluth stock', 'aluth item'])) {
            return $this->handleNewArrivalsWorkflow($userName);
        }

        // 6. BEST SELLERS / FEATURED WORKFLOW
        if ($this->matches($lower, ['best seller', 'popular', 'top selling', 'hot product', 'featured', 'trending'])) {
            return $this->handleBestSellersWorkflow($userName);
        }

        // 7. PC & LAPTOP UPGRADE RECOMMENDATIONS WORKFLOW
        if ($this->matches($lower, ['upgrade', 'upgrade my pc', 'upgrade laptop', 'add ram', 'increase ram', 'add ssd', 'slow pc', 'faster pc', 'gpu upgrade', 'processor upgrade', 'specs to upgrade', 'upgrade advice'])) {
            return $this->handleUpgradeWorkflow($userName, $lower);
        }

        // 8. SHIPPING & DELIVERY POLICY WORKFLOW (English & Singlish)
        if ($this->matches($lower, [
            'shipping', 'delivery', 'deliver', 'courier', 'dispatch', 'islandwide',
            'ship to', 'how long to deliver', 'shipping fee', 'cash on delivery', 'cod',
            'deliver karan', 'gennanna', 'dawas kiyak', 'kochchara kal',
        ])) {
            return $this->handleShippingWorkflow($userName);
        }

        // 9. RETURNS, REFUNDS & CANCELLATIONS POLICY WORKFLOW (English & Singlish)
        if ($this->matches($lower, ['return', 'refund', 'cancel', 'exchange', 'damaged on arrival', 'broken', 'money back', 'return policy', 'maru karanna', 'salli ganna'])) {
            return $this->handleRefundsWorkflow($userName);
        }

        // 10. WARRANTY & GUARANTEE WORKFLOW (English & Singlish)
        if ($this->matches($lower, ['warranty', 'guarantee', 'agent warranty', 'rma', 'repair', 'broken component', 'defect', 'warenti'])) {
            return $this->handleWarrantyWorkflow($userName);
        }

        // 11. STORE LOCATIONS, HOURS & CONTACT WORKFLOW (English & Singlish)
        if ($this->matches($lower, [
            'location', 'address', 'store', 'shop location', 'where are you', 'opening hours',
            'open time', 'close time', 'contact', 'phone', 'whatsapp', 'call', 'email',
            'colombo', 'kandy', 'koheda shop', 'shop eka koheda', 'katha karanna', 'number eka',
        ])) {
            return $this->handleContactWorkflow($userName);
        }

        // 12. ABOUT US WORKFLOW
        if ($this->matches($lower, ['about', 'about us', 'who are you', 'company', 'comlink history', 'owner', 'founder'])) {
            return $this->handleAboutWorkflow($userName);
        }

        // 13. CUSTOM PC BUILDER WORKFLOW
        if ($this->matches($lower, ['build pc', 'build my pc', 'custom pc', 'pc builder', 'rig', 'gaming setup build', 'pc ekak hadanna'])) {
            return $this->handleBuildPcWorkflow($userName, $user);
        }

        // 13B. BUDGET-BASED PRODUCT RECOMMENDATIONS (e.g. "laptop under 250000", "5000 ta adu mouse")
        $budgetResult = $this->handleBudgetSearchWorkflow($userName, $lower);
        if ($budgetResult) {
            return $budgetResult;
        }

        // 14. SPECIFIC PRODUCT SEARCH / INQUIRY (Dynamic database search for parts, brands, or names)
        $searchResult = $this->handleProductSearchWorkflow($userName, $lower, $message);
        if ($searchResult) {
            return $searchResult;
        }

        // 15. GREETING & DEFAULT FRIENDLY ASSISTANCE (word boundaries prevent "hi" matching inside "thiyena")
        if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening|ayubowan|machan|kohomada)\b/i', $lower)) {
            return response()->json([
                'reply' => "Hello {$userName}! 😊 Welcome to ComLink! I'm your AI shopping companion. I can help you check order statuses, search products in stock, suggest PC/Laptop upgrades, or provide store info. What can I do for you today?",
                'anim' => 'action',
                'chips' => [
                    ['label' => '🖥️ Build My PC', 'query' => 'build pc'],
                    ['label' => '📦 My Orders', 'query' => 'my order'],
                    ['label' => '⚡ New Arrivals', 'query' => 'new arrivals'],
                    ['label' => '🏷️ Best Sellers', 'query' => 'best sellers'],
                    ['label' => '🔧 PC Upgrade Advice', 'query' => 'upgrade my pc'],
                    ['label' => '🚚 Delivery Info', 'query' => 'delivery'],
                ],
            ]);
        }

        // 16. GOOGLE GEMINI AI FALLBACK (Grounded with Live ComLink DB Context, Cache, RateLimiter & Multi-turn History)
        $rateLimitKey = 'chatbot_ai:' . ($user?->id ?? ($request->ip() ?? 'guest'));
        $aiResult = $this->askGeminiWithStoreContext($message, $lower, $history, $rateLimitKey);

        if (! empty($aiResult['reply'])) {
            $this->logChatbotInteraction($user, $userName, $message, 'gemini_ai', $aiResult['reply'], $request->ip());

            return response()->json([
                'reply' => $aiResult['reply'],
                'anim' => 'success',
                'chips' => [
                    ['label' => '🖥️ Explore Categories', 'query' => 'categories'],
                    ['label' => '⚡ New Arrivals', 'query' => 'new arrivals'],
                    ['label' => '🚚 Delivery Info', 'query' => 'delivery'],
                    ['label' => '🛡️ Warranty Policy', 'query' => 'warranty'],
                ],
            ]);
        }

        // 17. FINAL FALLBACK (429 / Rate-Limited / Unmatched) WITH RELAXED DB SUGGESTIONS & IN-SITE CONTACT
        $suggestedProducts = $this->findRelaxedMatchingProducts($lower);
        $isRateLimited = ! empty($aiResult['rate_limited']);

        if ($isRateLimited) {
            $fallbackReply = "I'm receiving a lot of AI requests right now, {$userName}! ⏳ Please wait a minute before asking another custom question.\n\n"
                . ($suggestedProducts ? "Meanwhile, here are some products from our store that might match what you're looking for:" : "In the meantime, you can explore our categories, browse the Shop, or reach us via our Contact page.");
            $logType = 'rate_limited';
        } else {
            $fallbackReply = "I'm right here with you, {$userName}! 😊 "
                . ($suggestedProducts ? "Here are some matching products I found in our store:\n" : "You can ask me about:\n• Checking product stock and prices (e.g. 'Do you have RTX 4060 or laptop under 250000?')\n• PC & Laptop upgrade suggestions\n• Registering, logging in & tracking your orders\n• Islandwide delivery & warranty policies");
            $logType = 'fallback';
        }

        $this->logChatbotInteraction($user, $userName, $message, $logType, $fallbackReply, $request->ip());

        $responsePayload = [
            'reply' => $fallbackReply,
            'anim' => 'thinking',
            'action' => $suggestedProducts
                ? ['label' => '🛒 Browse Full Shop', 'link' => '/shop']
                : ['label' => '📞 Contact Support', 'link' => '/contact'],
            'chips' => [
                ['label' => '🖥️ Explore Categories', 'query' => 'categories'],
                ['label' => '🚚 Delivery Timeline', 'query' => 'delivery'],
                ['label' => '🛡️ Warranty Policy', 'query' => 'warranty'],
                ['label' => '📍 Store Locations', 'query' => 'locations and hours'],
            ],
        ];

        if (! empty($suggestedProducts)) {
            $responsePayload['products'] = $suggestedProducts;
        }

        return response()->json($responsePayload);
    }

    /**
     * Check if string contains any of the search terms
     */
    private function matches(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($text, $term)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Orders workflow
     */
    private function handleOrdersWorkflow($user, string $userName, string $lower): JsonResponse
    {
        // Check if an order number was provided directly in text (e.g. ORD-12345)
        preg_match('/(ord-[\w\d-]+)/i', $lower, $matches);
        $orderNumber = $matches[1] ?? null;

        if ($orderNumber) {
            $order = Order::where('order_number', 'LIKE', "%{$orderNumber}%")->with('items.product')->first();
            if ($order) {
                $statusBadge = strtoupper($order->status);
                $reply = "Found your order details, {$userName}! 📦\n\n";
                $reply .= "• Order Number: #{$order->order_number}\n";
                $reply .= "• Status: {$statusBadge}\n";
                $reply .= "• Date: " . $order->created_at->format('M d, Y') . "\n";
                $reply .= "• Total Amount: Rs. " . number_format($order->total_amount, 2) . "\n";
                if ($order->tracking_number) {
                    $reply .= "• Courier: {$order->courier_name} (Tracking: {$order->tracking_number})\n";
                }
                $reply .= "\nItems in this order:\n";
                foreach ($order->items as $item) {
                    $reply .= "  - {$item->product_name} (Qty: {$item->quantity}) - Rs. " . number_format($item->price, 2) . "\n";
                }

                return response()->json([
                    'reply' => $reply,
                    'anim' => 'success',
                    'action' => [
                        'label' => '📄 View In My Account',
                        'link' => '/orders',
                    ],
                ]);
            }
        }

        if (! $user) {
            return response()->json([
                'reply' => "To view your live order history and tracking, please log in to your ComLink account, {$userName}! Or type your Order Number (e.g. ORD-XXXX) directly here and I'll find it for you! 😊",
                'anim' => 'thinking',
                'action' => [
                    'label' => '🔑 Login to View Orders',
                    'link' => '/login',
                ],
            ]);
        }

        $orders = Order::where('user_id', $user->id)->with('items')->latest()->take(3)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'reply' => "You haven't placed any orders with this account yet, {$userName}! Would you like to check out our newest arrivals or build a custom PC today? 🚀",
                'anim' => 'idle',
                'chips' => [
                    ['label' => '🖥️ Custom PC Builder', 'query' => 'build pc'],
                    ['label' => '⚡ New Arrivals', 'query' => 'new arrivals'],
                    ['label' => '🏷️ Best Selling Gear', 'query' => 'best sellers'],
                ],
            ]);
        }

        $reply = "Here are your recent orders, {$userName}: 📦\n\n";
        $ordersData = [];

        foreach ($orders as $order) {
            $itemCount = $order->items->sum('quantity');
            $status = ucfirst($order->status);
            $reply .= "• Order #{$order->order_number} ({$status})\n";
            $reply .= "  Total: Rs. " . number_format($order->total_amount, 2) . " ({$itemCount} item" . ($itemCount > 1 ? 's' : '') . ")\n";
            if ($order->tracking_number) {
                $reply .= "  Courier: {$order->courier_name} | Track: {$order->tracking_number}\n";
            }
            $reply .= "\n";

            $ordersData[] = [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => 'Rs. ' . number_format($order->total_amount, 2),
                'date' => $order->created_at->format('M d, Y'),
                'items_count' => $itemCount,
            ];
        }

        return response()->json([
            'reply' => trim($reply),
            'anim' => 'success',
            'orders' => $ordersData,
            'action' => [
                'label' => '📦 Open Full Orders Dashboard',
                'link' => '/orders',
            ],
        ]);
    }

    /**
     * Cart workflow
     */
    private function handleCartWorkflow(string $userName, array $cart): JsonResponse
    {
        $items = $cart['items'] ?? [];
        $totalItems = $cart['totalItems'] ?? count($items);
        $totalPrice = $cart['totalPrice'] ?? 0;

        if (empty($items)) {
            return response()->json([
                'reply' => "Your shopping cart is currently empty, {$userName}! 🛒 Need any recommendations for high-performance keyboards, mice, SSDs, or PC parts?",
                'anim' => 'idle',
                'chips' => [
                    ['label' => '🎮 Browse Gaming Gear', 'query' => 'gaming gear'],
                    ['label' => '🖥️ Browse PC Parts', 'query' => 'pc components'],
                    ['label' => '⚡ New Arrivals', 'query' => 'new arrivals'],
                ],
            ]);
        }

        $reply = "You currently have {$totalItems} item" . ($totalItems > 1 ? 's' : '') . " in your shopping cart, {$userName}! 🛍️\n\n";
        foreach (array_slice($items, 0, 4) as $item) {
            $name = $item['name'] ?? 'Product';
            $qty = $item['quantity'] ?? 1;
            $price = isset($item['price']) ? 'Rs. ' . number_format((float) $item['price'], 2) : '';
            $reply .= "• {$name} (x{$qty}) {$price}\n";
        }

        if (count($items) > 4) {
            $more = count($items) - 4;
            $reply .= "• ...and {$more} more item" . ($more > 1 ? 's' : '') . "\n";
        }

        $reply .= "\nTotal: Rs. " . number_format((float) $totalPrice, 2) . "\nReady to checkout?";

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'action' => [
                'label' => '🛒 Proceed to Cart / Checkout',
                'link' => '/cart',
            ],
        ]);
    }

    /**
     * Wishlist workflow
     */
    private function handleWishlistWorkflow(string $userName, array $wishlist): JsonResponse
    {
        $items = is_array($wishlist) ? $wishlist : [];
        $count = count($items);

        if ($count === 0) {
            return response()->json([
                'reply' => "You haven't saved any items to your Wishlist yet, {$userName}! ❤️ You can click the heart icon on any product to save it for later.",
                'anim' => 'idle',
                'chips' => [
                    ['label' => '⚡ Browse New Arrivals', 'query' => 'new arrivals'],
                    ['label' => '🏷️ Browse Best Sellers', 'query' => 'best sellers'],
                ],
            ]);
        }

        $reply = "You have {$count} saved product" . ($count > 1 ? 's' : '') . " in your Wishlist, {$userName}! ❤️\n\n";
        foreach (array_slice($items, 0, 3) as $prod) {
            $name = $prod['name'] ?? 'Product';
            $reply .= "• {$name}\n";
        }

        $reply .= "\nWould you like to view your saved items or see matching accessories for them?";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '❤️ Open My Wishlist',
                'link' => '/wishlist',
            ],
            'chips' => [
                ['label' => '🎮 Matching Accessories', 'query' => 'peripherals'],
                ['label' => '🚚 Check Delivery Times', 'query' => 'delivery'],
            ],
        ]);
    }

    /**
     * Categories and Subcategories workflow
     */
    private function handleCategoriesWorkflow(string $userName): JsonResponse
    {
        $mainCategories = Category::whereNull('parent_id')
            ->orWhere('parent_id', 0)
            ->with(['children' => function ($q) {
                $q->take(6);
            }])
            ->get();

        $reply = "We offer a full spectrum of genuine computer hardware and IT accessories, {$userName}! 💻\n\nHere are our primary departments:\n";
        $chips = [];

        foreach ($mainCategories as $cat) {
            if ($cat->name === 'Test Category') continue;
            $reply .= "• **{$cat->name}**";
            if ($cat->children->isNotEmpty()) {
                $subNames = $cat->children->pluck('name')->implode(', ');
                $reply .= ": {$subNames}";
            }
            $reply .= "\n";

            $chips[] = [
                'label' => $cat->name,
                'query' => "show {$cat->name}",
            ];
        }

        $reply .= "\nWhich category would you like to explore?";

        return response()->json([
            'reply' => trim($reply),
            'anim' => 'action',
            'chips' => array_slice($chips, 0, 6),
            'action' => [
                'label' => '🛍️ Explore Shop Catalogue',
                'link' => '/shop',
            ],
        ]);
    }

    /**
     * New arrivals workflow
     */
    private function handleNewArrivalsWorkflow(string $userName): JsonResponse
    {
        $products = Product::where('is_visible', true)
            ->where('quantity', '>', 0)
            ->latest('id')
            ->take(3)
            ->get();

        $reply = "Check out our latest stock arrivals, {$userName}! ⚡ Fresh from authorized distributors with full warranty:\n\n";
        $cards = [];

        foreach ($products as $prod) {
            $price = $prod->sale_price ?? $prod->price;
            $reply .= "• **{$prod->name}** — Rs. " . number_format($price, 2) . "\n";
            $image = !empty($prod->images) ? (is_array($prod->images) ? $prod->images[0] : $prod->images) : null;
            $cards[] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'slug' => $prod->slug,
                'price' => 'Rs. ' . number_format($price, 2),
                'image' => $image,
                'stock' => $prod->quantity,
            ];
        }

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'products' => $cards,
            'action' => [
                'label' => '⚡ View All New Arrivals',
                'link' => '/shop',
            ],
        ]);
    }

    /**
     * Best sellers workflow
     */
    private function handleBestSellersWorkflow(string $userName): JsonResponse
    {
        $products = Product::where('is_visible', true)
            ->where('is_featured', true)
            ->where('quantity', '>', 0)
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::where('is_visible', true)->where('quantity', '>', 0)->inRandomOrder()->take(3)->get();
        }

        $reply = "Here are our top customer favorites and best sellers right now, {$userName}! 🏆 Highly rated for performance and value:\n\n";
        $cards = [];

        foreach ($products as $prod) {
            $price = $prod->sale_price ?? $prod->price;
            $reply .= "• **{$prod->name}** — Rs. " . number_format($price, 2) . "\n";
            $image = !empty($prod->images) ? (is_array($prod->images) ? $prod->images[0] : $prod->images) : null;
            $cards[] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'slug' => $prod->slug,
                'price' => 'Rs. ' . number_format($price, 2),
                'image' => $image,
                'stock' => $prod->quantity,
            ];
        }

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'products' => $cards,
            'action' => [
                'label' => '🏆 View Featured Deals',
                'link' => '/shop',
            ],
        ]);
    }

    /**
     * PC & Laptop Upgrade advisor workflow
     */
    private function handleUpgradeWorkflow(string $userName, string $lower): JsonResponse
    {
        $reply = "I'd love to help upgrade your PC or Laptop, {$userName}! 🚀 Here is expert advice based on our current stock:\n\n";
        $chips = [];
        $searchTag = '';

        if (str_contains($lower, 'ram') || str_contains($lower, 'memory') || str_contains($lower, '8gb') || str_contains($lower, '16gb')) {
            $reply .= "• **RAM Upgrade**: Upgrading from 8GB to 16GB (or 32GB) in dual-channel significantly boosts multitasking, Chrome tabs, and eliminates micro-stutters in games. We carry both Desktop (DIMM) and Laptop (SO-DIMM) DDR4 and DDR5 memory modules.\n";
            $searchTag = 'RAM';
        } elseif (str_contains($lower, 'ssd') || str_contains($lower, 'storage') || str_contains($lower, 'hard disk') || str_contains($lower, 'nvme') || str_contains($lower, 'slow')) {
            $reply .= "• **SSD / Storage Upgrade**: If your laptop or desktop still boots from an HDD, switching to an NVMe M.2 or 2.5\" SATA SSD will make your PC boot in 10 seconds and load apps instantly. We offer 256GB, 512GB, 1TB, and 2TB high-speed drives!\n";
            $searchTag = 'SSD';
        } elseif (str_contains($lower, 'gpu') || str_contains($lower, 'graphic') || str_contains($lower, 'gaming') || str_contains($lower, 'fps')) {
            $reply .= "• **Graphics Card (GPU) Upgrade**: For better gaming FPS and video rendering, upgrading your GPU gives the biggest visual leap. Be sure to check your Power Supply (PSU) wattage (e.g. 550W+ for mid-tier cards).\n";
            $searchTag = 'Graphic';
        } else {
            $reply .= "• **RAM & SSD**: The two highest-impact upgrades for older laptops and PCs are adding an M.2 NVMe SSD and expanding to 16GB DDR4 RAM.\n";
            $reply .= "• **Cooling & Thermal**: Re-pasting with high-grade thermal paste and adding a tower CPU cooler drops temperatures by 15-20°C for peak clock speeds.\n";
            $searchTag = 'SSD';
        }

        // Query related upgrade items in stock
        $upgradeProducts = Product::where('is_visible', true)
            ->where('quantity', '>', 0)
            ->where('name', 'LIKE', "%{$searchTag}%")
            ->take(3)
            ->get();

        $cards = [];
        if ($upgradeProducts->isNotEmpty()) {
            $reply .= "\nMatching upgrades currently in stock:\n";
            foreach ($upgradeProducts as $prod) {
                $price = $prod->sale_price ?? $prod->price;
                $reply .= "  - **{$prod->name}** (Rs. " . number_format($price, 2) . ")\n";
                $image = !empty($prod->images) ? (is_array($prod->images) ? $prod->images[0] : $prod->images) : null;
                $cards[] = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'slug' => $prod->slug,
                    'price' => 'Rs. ' . number_format($price, 2),
                    'image' => $image,
                    'stock' => $prod->quantity,
                ];
            }
        }

        $reply .= "\nTell me your exact processor model or laptop brand/specs, and I'll find 100% compatible components for you!";

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'products' => $cards,
            'chips' => [
                ['label' => '⚡ Upgrade RAM', 'query' => 'ram upgrade'],
                ['label' => '💾 Upgrade SSD', 'query' => 'ssd upgrade'],
                ['label' => '🎮 Upgrade GPU', 'query' => 'gpu upgrade'],
                ['label' => '🖥️ Custom PC Builder', 'query' => 'build pc'],
            ],
        ]);
    }

    /**
     * Dynamic Product Search / Inquiry workflow
     */
    private function handleProductSearchWorkflow(string $userName, string $lower, string $original): ?JsonResponse
    {
        // Strip common conversational words (English & Singlish)
        $clean = str_replace([
            'do you have', 'is there', 'can i get', 'price of', 'how much is', 'how much',
            'check if', 'in stock', 'available', 'tell me about', 'details of',
            'please', 'show me', 'search for', 'looking for', 'specifications of', 'specs of',
            'tell me', 'find', 'got', 'sell', 'price', 'cost', 'rate',
            'monawada', 'monada', 'mona d', 'monad', 'thiyena', 'tiyena', 'thiyenne', 'tiyenne',
            'ekak', 'eka', 'wage', 'gana',
        ], ' ', $lower);

        // Strip punctuation (? , . ! etc.) and collapse extra whitespace
        $clean = trim(preg_replace('/\s+/', ' ', preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', $clean)));

        if (strlen($clean) < 2) {
            return null;
        }

        // Search in products or categories, prioritizing exact product name matches over description mentions
        $products = Product::where('is_visible', true)
            ->where(function ($q) use ($clean) {
                $q->where('name', 'LIKE', "%{$clean}%")
                  ->orWhere('description', 'LIKE', "%{$clean}%")
                  ->orWhere('sku', 'LIKE', "%{$clean}%")
                  ->orWhereHas('brand', function ($bq) use ($clean) {
                      $bq->where('name', 'LIKE', "%{$clean}%");
                  })
                  ->orWhereHas('categories', function ($cq) use ($clean) {
                      $cq->where('name', 'LIKE', "%{$clean}%");
                  });
            })
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ["%{$clean}%"])
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        $count = $products->count();
        $reply = "Good news, {$userName}! I found {$count} matching item" . ($count > 1 ? 's' : '') . " in our catalogue: 🔍\n\n";
        $cards = [];

        foreach ($products as $prod) {
            $price = $prod->sale_price ?? $prod->price;
            $inStock = $prod->quantity > 0;
            $stockText = $inStock ? "✅ In Stock ({$prod->quantity} units)" : "⚠️ Out of Stock";
            $warranty = $prod->warranty_duration ? " ({$prod->warranty_duration} {$prod->warranty_unit} warranty)" : "";

            $reply .= "• **{$prod->name}**\n";
            $reply .= "  Price: Rs. " . number_format($price, 2) . " | {$stockText}{$warranty}\n";

            $image = !empty($prod->images) ? (is_array($prod->images) ? $prod->images[0] : $prod->images) : null;
            $cards[] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'slug' => $prod->slug,
                'price' => 'Rs. ' . number_format($price, 2),
                'image' => $image,
                'stock' => $prod->quantity,
            ];
        }

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'products' => $cards,
            'action' => count($cards) === 1 ? [
                'label' => "👁️ View {$cards[0]['name']}",
                'link' => "/product/{$cards[0]['slug']}",
            ] : [
                'label' => '🛍️ View In Shop',
                'link' => '/shop?search=' . urlencode($clean),
            ],
        ]);
    }

    /**
     * Shipping and delivery policy workflow
     */
    private function handleShippingWorkflow(string $userName): JsonResponse
    {
        $reply = "Here are our complete Shipping & Delivery details, {$userName}! 🚚\n\n";
        $reply .= "• **Colombo & Suburbs**: 1 – 2 business days (Express delivery within 24h available for urgent orders).\n";
        $reply .= "• **Island-wide Delivery**: 2 – 4 business days via trusted courier partners across Sri Lanka.\n";
        $reply .= "• **Order Dispatch**: Verified within 24 to 48 hours. Custom PC builds undergo an additional 24-hour hardware stress testing and QA before dispatch.\n";
        $reply .= "• **Payment Options**: Cash on Delivery (COD) and Online Card / Bank Transfer supported!\n";
        $reply .= "• **Packaging & Safety**: All sensitive electronic components (CPUs, motherboards, GPUs, monitors) are bubble-wrapped in shock-proof boxes.\n";
        $reply .= "• **Tracking**: You will receive a direct courier tracking link via SMS/Email once your order leaves our hub.";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '🚚 Read Full Delivery Policy',
                'link' => '/shipping-delivery',
            ],
            'chips' => [
                ['label' => '📦 Track My Order', 'query' => 'track order'],
                ['label' => '🛡️ Warranty Policy', 'query' => 'warranty'],
                ['label' => '📞 Contact Hotline', 'query' => 'contact hotline'],
            ],
        ]);
    }

    /**
     * Returns, refunds & cancellations workflow
     */
    private function handleRefundsWorkflow(string $userName): JsonResponse
    {
        $reply = "Here is our Return, Refund & Cancellation Policy, {$userName}! 🔄\n\n";
        $reply .= "• **7-Day Return Window**: You may return items within 7 calendar days of receipt if unopened and unused, with all seals, serials, and packaging intact.\n";
        $reply .= "• **Damaged on Arrival**: If an item arrives damaged or defective, please contact our support desk within 48 hours of delivery with photos of the package and item. We arrange an immediate replacement or refund!\n";
        $reply .= "• **Refund Processing**: Approved refunds are credited back to your original payment method or bank account within 3 – 5 business days.\n";
        $reply .= "• **Manufacturer Warranty**: After 7 days, products are covered under the official distributor warranty. Our technical service center handles all RMA submissions on your behalf.";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '🔄 Read Full Returns Policy',
                'link' => '/return-refund',
            ],
            'chips' => [
                ['label' => '🛡️ Warranty Coverage', 'query' => 'warranty'],
                ['label' => '📞 RMA Support Desk', 'query' => 'contact'],
            ],
        ]);
    }

    /**
     * Warranty workflow
     */
    private function handleWarrantyWorkflow(string $userName): JsonResponse
    {
        $reply = "ComLink guarantees 100% genuine product authenticity, {$userName}! 🛡️\n\n";
        $reply .= "• **Official Agent Warranty**: All brand-new components, laptops, monitors, and peripherals come with verified distributor warranty cards.\n";
        $reply .= "• **Warranty Duration**: Standard 1 to 3 Years on PC components (RAM, Motherboards, Processors, GPUs, SSDs) and up to 5 Years on select storage/power supplies.\n";
        $reply .= "• **Serial Tracking**: Your warranty is linked to the serial number printed on your invoice, making claims quick and hassle-free.\n";
        $reply .= "• **Service Center**: Drop by our Colombo 04 or Kandy service hub or courier the item to us for RMA servicing.";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '🛡️ View Warranty Terms',
                'link' => '/terms-of-service',
            ],
            'chips' => [
                ['label' => '📍 Store Locations', 'query' => 'store locations'],
                ['label' => '📞 Technical Support Desk', 'query' => 'contact hotline'],
            ],
        ]);
    }

    /**
     * Store locations, hours and contact workflow
     */
    private function handleContactWorkflow(string $userName): JsonResponse
    {
        $reply = "Here are our ComLink store locations and contact details, {$userName}! 📍\n\n";
        $reply .= "📍 **Colombo Flagship Store & Service Hub**:\n";
        $reply .= "   No. 45, Galle Road, Colombo 04 (Opposite Majestic City junction)\n\n";
        $reply .= "📍 **Kandy Branch & Pickup Center**:\n";
        $reply .= "   No. 12, Dalada Veediya, Kandy, Sri Lanka\n\n";
        $reply .= "⏰ **Operating Hours**:\n";
        $reply .= "   • Monday – Saturday: 9:00 AM – 7:30 PM\n";
        $reply .= "   • Sunday & Poya Days: 9:30 AM – 4:00 PM\n";
        $reply .= "   • Online Store & Delivery: Active 24/7!\n\n";
        $reply .= "📞 **Hotlines**:\n";
        $reply .= "   • Sales & WhatsApp: +94 (77) 123-4567\n";
        $reply .= "   • Service & Tech Desk: +94 (11) 765-4321\n";
        $reply .= "   • Email: info@comlink.lk | support@comlink.lk";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '💬 Open WhatsApp Support',
                'link' => 'https://wa.me/94771234567',
            ],
            'chips' => [
                ['label' => '🗺️ Google Maps Location', 'query' => 'contact'],
                ['label' => '🚚 Delivery Timelines', 'query' => 'delivery'],
            ],
        ]);
    }

    /**
     * About us workflow
     */
    private function handleAboutWorkflow(string $userName): JsonResponse
    {
        $reply = "Welcome to ComLink, {$userName}! 🌟\n\n";
        $reply .= "Founded by Dilshan Perera with a mission to deliver honest tech advice and fair pricing, ComLink is your premier IT equipment and computer accessory specialist in Sri Lanka.\n\n";
        $reply .= "• **What We Specialize In**: High-performance custom PC builds, laptops, PC components, gaming gear, CCTV & networking systems, and official agent warranty support.\n";
        $reply .= "• **Our Partners**: Official retail partner for Logitech, Corsair, ASUS, MSI, AOAS, and other tier-1 brands.\n";
        $reply .= "• **Locations**: Flagship in Colombo 04 and regional pickup hub in Kandy.";

        return response()->json([
            'reply' => $reply,
            'anim' => 'action',
            'action' => [
                'label' => '📖 Read Our Story',
                'link' => '/about',
            ],
            'chips' => [
                ['label' => '📍 Store Locations', 'query' => 'locations'],
                ['label' => '🖥️ Custom PC Builder', 'query' => 'build pc'],
            ],
        ]);
    }

    /**
     * Custom PC builder workflow
     */
    private function handleBuildPcWorkflow(string $userName, $user): JsonResponse
    {
        $reply = "Looking to assemble a custom gaming rig or workstation, {$userName}? 🖥️⚡\n\n";
        $reply .= "Our interactive **Build My PC** tool lets you pick compatible components step-by-step:\n";
        $reply .= "• Processors (Intel Core & AMD Ryzen)\n";
        $reply .= "• Motherboards with matching socket types\n";
        $reply .= "• DDR4 & DDR5 RAM kits\n";
        $reply .= "• Graphics Cards (NVIDIA RTX & AMD Radeon)\n";
        $reply .= "• High-speed M.2 NVMe SSDs & Power Supplies\n\n";
        $reply .= "Our system automatically calculates total wattage and hardware compatibility in real time!";

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'action' => [
                'label' => '🖥️ Open Build My PC Tool',
                'link' => '/build-my-pc',
                'requiresAuth' => ! $user,
            ],
            'chips' => [
                ['label' => '🔧 PC Upgrade Suggestions', 'query' => 'upgrade my pc'],
                ['label' => '🎮 Gaming Gear', 'query' => 'gaming gear'],
            ],
        ]);
    }

    /**
     * Guide user on how to Register, Log In, or Reset Password (supports English & Singlish)
     */
    private function handleAccountGuideWorkflow($user, string $userName, string $lower): JsonResponse
    {
        if ($user) {
            return response()->json([
                'reply' => "You are already logged in as **{$userName}**! ✅\n\nYou can view your orders, update your profile/password, or start shopping right away.",
                'anim' => 'success',
                'action' => [
                    'label' => '👤 Go to My Profile',
                    'link' => '/profile',
                ],
                'chips' => [
                    ['label' => '📦 My Orders', 'query' => 'my order'],
                    ['label' => '🛒 My Cart', 'query' => 'my cart'],
                    ['label' => '🖥️ Build My PC', 'query' => 'build pc'],
                ],
            ]);
        }

        if ($this->matches($lower, ['forgot password', 'reset password', 'password'])) {
            $reply = "Forgot your password? No worries! 🔐 Here is how to reset it:\n\n";
            $reply .= "1️⃣ Click **\"Go to Forgot Password\"** below (or click **Forgot Password?** on the Login page).\n";
            $reply .= "2️⃣ Enter your registered **Email Address** and click **Send Reset Link**.\n";
            $reply .= "3️⃣ Open the email in your inbox and click the reset link to set a new password.";

            return response()->json([
                'reply' => $reply,
                'anim' => 'action',
                'action' => [
                    'label' => '🔄 Go to Forgot Password',
                    'link' => '/forgot-password',
                ],
                'chips' => [
                    ['label' => '🔑 How to Log In', 'query' => 'how to login'],
                    ['label' => '📝 How to Register', 'query' => 'how to register'],
                ],
            ]);
        }

        $isLoginOnly = $this->matches($lower, ['login', 'log in', 'sign in', 'signin', 'log ven', 'logven'])
            && ! $this->matches($lower, ['register', 'sign up', 'signup', 'create account', 'new account']);

        if ($isLoginOnly) {
            $reply = "Here is how to **Log In** to ComLink: 🔑\n\n";
            $reply .= "1️⃣ Click the **Sign In / Account** icon at the top-right corner of the website (or click the button below).\n";
            $reply .= "2️⃣ Enter your registered **Email Address** and **Password**.\n";
            $reply .= "3️⃣ Click **Sign In** — you can now track orders, save wishlist items, and checkout!\n\n";
            $reply .= "*(New to ComLink? Create an account first using Register!)*";

            return response()->json([
                'reply' => $reply,
                'anim' => 'success',
                'action' => [
                    'label' => '🔑 Go to Login Page',
                    'link' => '/login',
                ],
                'chips' => [
                    ['label' => '📝 How to Register', 'query' => 'how to register'],
                    ['label' => '🔄 Forgot Password', 'query' => 'forgot password'],
                ],
            ]);
        }

        $reply = "Sure! Here is a quick step-by-step guide to **Register & Log In** to ComLink: 😊\n\n";
        $reply .= "**📝 Step 1: Register (Create Account)**\n";
        $reply .= "1️⃣ Click **\"Go to Register Page\"** below (or click **Account → Register** at the top-right).\n";
        $reply .= "2️⃣ Enter your **Full Name**, **Email Address**, and a **Password** (confirm password).\n";
        $reply .= "3️⃣ Click **Create Account** — your account will be created and logged in automatically!\n\n";
        $reply .= "**🔑 Step 2: Log In Next Time**\n";
        $reply .= "• Click **Sign In** at the top-right, enter your **Email & Password**, and you're in!\n";
        $reply .= "• If you ever forget your password, just use **Forgot Password?** on the login page.";

        return response()->json([
            'reply' => $reply,
            'anim' => 'success',
            'action' => [
                'label' => '📝 Go to Register Page',
                'link' => '/register',
            ],
            'chips' => [
                ['label' => '🔑 Go to Login Guide', 'query' => 'how to login'],
                ['label' => '🔄 Forgot Password', 'query' => 'forgot password'],
                ['label' => '🖥️ Build My PC', 'query' => 'build pc'],
            ],
        ]);
    }

    /**
     * Normalize common tech typos and Singlish phrases before intent matching.
     */
    private function normalizeQuery(string $text): string
    {
        $replacements = [
            'keybord' => 'keyboard',
            'key board' => 'keyboard',
            'moniter' => 'monitor',
            'moniters' => 'monitors',
            'grafic' => 'graphic',
            'grphics' => 'graphics',
            'lap top' => 'laptop',
            'lptop' => 'laptop',
            'proccesor' => 'processor',
            'procesor' => 'processor',
            'mothrboard' => 'motherboard',
            'mause' => 'mouse',
            'mous' => 'mouse',
            'headfone' => 'headphone',
            'headfones' => 'headphones',
            'spekar' => 'speaker',
            'camra' => 'camera',
            'kiyada' => 'price',
            'kiyatada' => 'price',
            'keeyada' => 'price',
            'gana kohomada' => 'price',
            'thiyenawada' => 'available',
            'thiyeda' => 'available',
            'thiyenvd' => 'available',
        ];

        foreach ($replacements as $typo => $fix) {
            $text = preg_replace('/\b' . preg_quote($typo, '/') . '\b/u', $fix, $text);
        }

        return $text;
    }

    /**
     * Handle budget-based product queries (e.g. "laptop under 250000", "5000 ta adu mouse").
     */
    private function handleBudgetSearchWorkflow(string $userName, string $lower): ?JsonResponse
    {
        $hasBudgetCue = $this->matches($lower, [
            'under ', 'below ', 'less than', 'within ', 'budget', 'around ', 'max ',
            'ta adu', 'walata adu', 'ata adu', 'ta wada adu', 'athara',
        ]);

        if (! $hasBudgetCue) {
            return null;
        }

        // Extract budget amount (supports "250,000", "250000", "250k")
        if (! preg_match('/(\d[\d,]*\.?\d*)\s*(k)?/i', $lower, $m)) {
            return null;
        }

        $num = (float) str_replace(',', '', $m[1]);
        if (! empty($m[2])) {
            $num *= 1000;
        }

        if ($num < 100) {
            return null;
        }

        $maxBudget = $this->matches($lower, ['around ']) ? $num * 1.15 : $num;

        // Detect optional item keyword
        $itemKeywords = ['laptop', 'mouse', 'keyboard', 'monitor', 'ssd', 'ram', 'graphic', 'gpu', 'processor', 'headset', 'speaker', 'camera', 'cctv', 'router', 'ups', 'chair', 'bag'];
        $matchedKeyword = null;
        foreach ($itemKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                $matchedKeyword = $kw;
                break;
            }
        }

        $priceSql = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END';
        $query = Product::where('is_visible', true)
            ->whereRaw("({$priceSql}) > 0")
            ->whereRaw("({$priceSql}) <= ?", [$maxBudget]);

        if ($matchedKeyword) {
            $query->where(function ($q) use ($matchedKeyword) {
                $q->where('name', 'like', "%{$matchedKeyword}%")
                    ->orWhereHas('categories', fn ($cq) => $cq->where('name', 'like', "%{$matchedKeyword}%"));
            });
        }

        $products = $query->orderByRaw("({$priceSql}) DESC")->take(5)->get();

        if ($products->isEmpty()) {
            return response()->json([
                'reply' => "Sorry {$userName}, I couldn't find any" . ($matchedKeyword ? " **{$matchedKeyword}**" : '') . ' products under **Rs. ' . number_format($maxBudget, 0) . "** right now. Try increasing your budget or browse our Shop!",
                'anim' => 'thinking',
                'action' => [
                    'label' => '🛒 Browse Shop',
                    'link' => '/shop',
                ],
            ]);
        }

        $cards = $products->map(function (Product $p) {
            $effPrice = ($p->sale_price !== null && (float) $p->sale_price > 0) ? (float) $p->sale_price : (float) $p->price;
            $img = (is_array($p->images) && ! empty($p->images)) ? $p->images[0] : '/images/placeholder.png';

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => 'Rs. ' . number_format($effPrice, 2),
                'image' => $img,
                'stock' => (int) ($p->current_inventory_stock ?? $p->quantity ?? 0),
            ];
        })->values()->all();

        $label = $matchedKeyword ? "**{$matchedKeyword}** options" : 'products';

        return response()->json([
            'reply' => "Here are the best {$label} under **Rs. " . number_format($maxBudget, 0) . "** for you, {$userName}! 💡",
            'anim' => 'success',
            'products' => $cards,
            'action' => [
                'label' => '🛒 View More in Shop',
                'link' => '/shop?max_price=' . (int) $maxBudget,
            ],
        ]);
    }

    /**
     * Call Google Gemini AI (Free Tier) grounded with live ComLink database context, caching, rate limiting & 429 cooldown.
     *
     * @return array{reply: ?string, rate_limited: bool}
     */
    private function askGeminiWithStoreContext(string $message, string $lower, array $history, string $rateLimitKey): array
    {
        $apiKey = (string) config('services.gemini.api_key');
        if (empty($apiKey)) {
            return ['reply' => null, 'rate_limited' => false];
        }

        // Cache repeated questions (4 hours) to preserve Free Tier quota
        $lastTurn = ! empty($history) ? (string) (end($history)['text'] ?? '') : '';
        $cacheKey = 'chatbot_ai_resp:' . md5($lower . '|' . count($history) . '|' . $lastTurn);
        $cachedReply = Cache::get($cacheKey);
        if (is_string($cachedReply) && $cachedReply !== '') {
            return ['reply' => $cachedReply, 'rate_limited' => false];
        }

        // Respect global 429 circuit-breaker cooldown (60s) & per-user/IP rate limit (5 AI calls/min)
        if (Cache::get('chatbot_gemini_429_cooldown') || RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return ['reply' => null, 'rate_limited' => true];
        }

        RateLimiter::hit($rateLimitKey, 60);
        $model = (string) config('services.gemini.model', 'gemini-3.5-flash-lite');

        try {
            // 1. Gather relevant live products from DB to ground Gemini (top 8 matching items, no personal data sent)
            $words = $this->extractSearchKeywords($lower);

            $productContextLines = [];
            if ($words->isNotEmpty()) {
                $dbProducts = Product::where('is_visible', true)
                    ->where(function ($q) use ($words) {
                        foreach ($words as $w) {
                            $q->orWhere('name', 'like', "%{$w}%");
                        }
                    })
                    ->take(8)
                    ->get(['name', 'price', 'sale_price', 'quantity', 'condition']);

                foreach ($dbProducts as $p) {
                    $eff = ($p->sale_price !== null && (float) $p->sale_price > 0) ? (float) $p->sale_price : (float) $p->price;
                    $stock = (int) ($p->quantity ?? 0);
                    $productContextLines[] = "- {$p->name} | Price: Rs. " . number_format($eff, 2) . " | Stock: {$stock} | Condition: {$p->condition}";
                }
            }

            $categories = Cache::remember('chatbot_visible_categories', 3600, function () {
                return Category::whereNull('parent_id')->where('is_visible', true)->pluck('name')->join(', ');
            });
            $shippingNote = PolicyPage::where('slug', 'shipping-delivery')->value('title') ?? 'Islandwide Delivery (2-4 business days)';

            $systemPrompt = "You are ComLink AI, the friendly shopping assistant for ComLink Computer Accessories (Sri Lanka).\n"
                . "Store Info: Website sells Laptops, PC Components, Peripherals, Gaming Gear, Networking & CCTV Cameras. Currency is LKR (Rs.).\n"
                . "Store Categories: {$categories}\n"
                . "Contact: info@comlink.lk | Phone: +94 77 123 4567 | Delivery: {$shippingNote} (1-2 days Colombo, 2-4 days Islandwide).\n"
                . "Relevant Live Products in Database:\n"
                . (! empty($productContextLines) ? implode("\n", $productContextLines) : "(No exact keyword product matches found in DB — give general helpful guidance without inventing specific prices or stock numbers.)") . "\n\n"
                . "Rules:\n"
                . "- Reply concisely (2-5 short sentences), friendly, and helpful.\n"
                . "- If the user wrote in Singlish, you may reply in clear friendly English or light Singlish.\n"
                . "- NEVER invent product prices or stock counts that are not listed above.";

            $contents = [];
            foreach (array_slice($history, -5) as $turn) {
                $role = ($turn['sender'] ?? 'user') === 'bot' ? 'model' : 'user';
                $text = trim((string) ($turn['text'] ?? ''));
                if ($text !== '') {
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $text]],
                    ];
                }
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]],
            ];

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $http = Http::timeout(12);
            if (app()->isLocal() && ! ini_get('curl.cainfo') && ! ini_get('openssl.cafile')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($url, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 300,
                ],
            ]);

            if ($response->status() === 429) {
                Cache::put('chatbot_gemini_429_cooldown', true, 60);

                return ['reply' => null, 'rate_limited' => true];
            }

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
                if (is_string($text) && trim($text) !== '') {
                    $cleanText = trim($text);
                    Cache::put($cacheKey, $cleanText, now()->addHours(4));

                    return ['reply' => $cleanText, 'rate_limited' => false];
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return ['reply' => null, 'rate_limited' => false];
    }

    /**
     * Extract meaningful keywords from a normalized query for DB product matching.
     */
    private function extractSearchKeywords(string $lower)
    {
        $stopWords = ['the', 'and', 'for', 'you', 'have', 'what', 'can', 'this', 'that', 'with', 'oka', 'eka', 'mata', 'oyata', 'kohomada', 'thiyenawada', 'available', 'price', 'want', 'need', 'how', 'much'];

        return collect(preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $lower)))
            ->filter(fn ($w) => strlen($w) >= 3 && ! in_array($w, $stopWords))
            ->take(5);
    }

    /**
     * Relaxed DB product lookup for graceful fallback when AI is unavailable or rate-limited.
     */
    private function findRelaxedMatchingProducts(string $lower): array
    {
        $words = $this->extractSearchKeywords($lower);
        if ($words->isEmpty()) {
            return [];
        }

        $products = Product::where('is_visible', true)
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('name', 'like', "%{$w}%");
                }
            })
            ->take(4)
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        return $products->map(function (Product $p) {
            $effPrice = ($p->sale_price !== null && (float) $p->sale_price > 0) ? (float) $p->sale_price : (float) $p->price;
            $img = (is_array($p->images) && ! empty($p->images)) ? $p->images[0] : '/images/placeholder.png';

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => 'Rs. ' . number_format($effPrice, 2),
                'image' => $img,
                'stock' => (int) ($p->current_inventory_stock ?? $p->quantity ?? 0),
            ];
        })->values()->all();
    }

    /**
     * Log AI-handled or unmatched queries for admin review & continuous improvement.
     */
    private function logChatbotInteraction($user, string $userName, string $message, string $type, string $reply, ?string $ip): void
    {
        try {
            ChatbotLog::create([
                'user_id' => $user?->id,
                'user_name' => $userName,
                'message' => mb_substr($message, 0, 1000),
                'resolution_type' => $type,
                'bot_reply' => mb_substr($reply, 0, 2000),
                'ip_address' => $ip,
            ]);
        } catch (\Throwable) {
            // Never interrupt chat response if logging fails
        }
    }
}
