<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the shopping cart page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Cart/Index', [
            'appName' => config('app.name', 'ComLink'),
        ]);
    }

    /**
     * Return live available stock counts for items currently in the user's cart.
     */
    public function stockCheck(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        if (! is_array($items)) {
            return response()->json(['stocks' => []]);
        }

        $stocks = [];
        foreach ($items as $item) {
            $cartId = (string) ($item['id'] ?? '');
            if ($cartId === '') {
                continue;
            }

            $variantId = is_numeric($item['variantId'] ?? null) ? (int) $item['variantId'] : null;
            $productId = is_numeric($item['productId'] ?? null) ? (int) $item['productId'] : null;

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant) {
                    $stocks[$cartId] = max(0, (int) ($variant->stock ?? $variant->show_quantity ?? 0));
                    continue;
                }
            }

            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $stocks[$cartId] = max(0, (int) ($product->quantity ?? 0));
                }
            }
        }

        return response()->json(['stocks' => $stocks]);
    }
}
