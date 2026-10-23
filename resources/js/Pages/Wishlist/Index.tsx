import React, { useState, useMemo } from "react";
import { Head, Link, usePage, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import ProductCard from "@/Components/product/ProductCard";
import { useWishlist } from "@/contexts/WishlistContext";
import { useCart } from "@/contexts/CartContext";
import { formatPrice } from "@/utils/currency";
import { cn } from "@/utils/cn";
import { toast } from "sonner";
import { motion, AnimatePresence } from "framer-motion";
import {
    Heart,
    ShoppingCart,
    Trash2,
    ArrowLeft,
    Search,
    Grid,
    List,
    Cpu,
    Check,
    Sparkles,
    ShoppingBag,
    Share2,
    CheckCircle2,
    AlertCircle,
    X,
    Filter,
} from "lucide-react";

export default function WishlistIndex() {
    return (
        <AppLayout>
            <WishlistContent />
        </AppLayout>
    );
}

function WishlistContent() {
    const { auth } = usePage().props as any;
    const user = auth?.user ?? null;

    const { items, count, removeFromWishlist, clearWishlist } = useWishlist();
    const { addToCart } = useCart();

    const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
    const [searchQuery, setSearchQuery] = useState("");
    const [selectedCategory, setSelectedCategory] = useState<string>("all");
    const [stockFilter, setStockFilter] = useState<"all" | "in_stock">("all");
    const [isClearModalOpen, setIsClearModalOpen] = useState(false);
    const [movingAllToCart, setMovingAllToCart] = useState(false);
    const [addedIds, setAddedIds] = useState<Record<string, boolean>>({});

    // Unique categories from wishlisted items
    const categories = useMemo(() => {
        const set = new Set<string>();
        items.forEach((item) => {
            if (item.category) set.add(item.category);
        });
        return Array.from(set);
    }, [items]);

    // Filtered wishlist items
    const filteredItems = useMemo(() => {
        return items.filter((item) => {
            const matchesSearch =
                !searchQuery.trim() ||
                item.name.toLowerCase().includes(searchQuery.toLowerCase().trim()) ||
                (item.brand && item.brand.toLowerCase().includes(searchQuery.toLowerCase().trim())) ||
                (item.category && item.category.toLowerCase().includes(searchQuery.toLowerCase().trim()));

            const matchesCategory =
                selectedCategory === "all" || item.category === selectedCategory;

            const matchesStock =
                stockFilter === "all" || item.inStock !== false;

            return matchesSearch && matchesCategory && matchesStock;
        });
    }, [items, searchQuery, selectedCategory, stockFilter]);

    // Count in-stock items
    const inStockCount = useMemo(() => {
        return items.filter((i) => i.inStock !== false).length;
    }, [items]);

    // Handle single item add to cart from List View
    const handleAddSingleToCart = (item: any) => {
        if (!user) {
            toast.error("Please login or register to add items to your cart.");
            router.visit("/login");
            return;
        }

        if (item.inStock === false) {
            toast.error(`${item.name} is currently out of stock`);
            return;
        }

        addToCart({
            productId: String(item.id),
            name: item.name,
            price: Number(item.latestPrice ?? item.price),
            image: item.image ?? item.images?.[0] ?? "/images/products/sample-keyboard.jpg",
            quantity: 1,
        });

        setAddedIds((prev) => ({ ...prev, [item.id]: true }));
        setTimeout(() => {
            setAddedIds((prev) => ({ ...prev, [item.id]: false }));
        }, 1800);

        toast.success(`Added "${item.name}" to cart!`);
    };

    // Move all in-stock items to cart
    const handleMoveAllToCart = () => {
        if (!user) {
            toast.error("Please login or register to manage your cart.");
            router.visit("/login");
            return;
        }

        const available = items.filter((i) => i.inStock !== false);
        if (available.length === 0) {
            toast.error("No in-stock items available to add to cart.");
            return;
        }

        setMovingAllToCart(true);

        available.forEach((item) => {
            addToCart({
                productId: String(item.id),
                name: item.name,
                price: Number(item.latestPrice ?? item.price),
                image: item.image ?? item.images?.[0] ?? "/images/products/sample-keyboard.jpg",
                quantity: 1,
            });
        });

        setTimeout(() => {
            setMovingAllToCart(false);
            toast.success(
                `Added ${available.length} ${available.length === 1 ? "item" : "items"} to your shopping cart!`,
            );
        }, 500);
    };

    // Share wishlist URL
    const handleShareWishlist = () => {
        if (navigator.share) {
            navigator
                .share({
                    title: "My Wishlist - Vicky Store / ComLink",
                    text: `Check out my curated hardware wishlist with ${items.length} items!`,
                    url: window.location.href,
                })
                .catch(() => {});
        } else {
            navigator.clipboard.writeText(window.location.href);
            toast.success("Wishlist link copied to clipboard!");
        }
    };

    return (
        <>
            <Head title="My Wishlist - Vicky Store / ComLink" />

            <div className="min-h-screen bg-bg text-fg py-8 sm:py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumbs */}
                    <nav className="flex items-center gap-2 text-xs sm:text-sm text-muted mb-6">
                        <Link href="/" className="hover:text-accent transition-colors">
                            Home
                        </Link>
                        <span>/</span>
                        <Link href="/shop" className="hover:text-accent transition-colors">
                            Shop
                        </Link>
                        <span>/</span>
                        <span className="text-accent font-semibold">My Wishlist</span>
                    </nav>

                    {/* Guest Notification Banner */}
                    {!user && (
                        <div className="mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 sm:p-5 text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <AlertCircle className="w-5 h-5 text-amber-400 shrink-0" />
                                <div>
                                    <p className="text-sm font-semibold text-foreground">
                                        You are currently browsing as a guest
                                    </p>
                                    <p className="text-xs text-muted">
                                        Sign in to permanently sync your wishlist across your phone, tablet, and PC.
                                    </p>
                                </div>
                            </div>
                            <Link
                                href="/login"
                                className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 transition-colors shrink-0"
                            >
                                Sign In / Register
                            </Link>
                        </div>
                    )}

                    {/* Page Header */}
                    <div className="flex flex-col md:flex-row md:items-end justify-between pb-6 mb-8 border-b border-border gap-6">
                        <div>
                            <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold font-display tracking-tight text-fg flex items-center gap-3">
                                My Wishlist
                            </h1>
                            <p className="text-sm sm:text-base text-muted mt-2 max-w-2xl">
                                Keep track of components you plan to buy, build configurations, and favorite accessories.
                                Move items directly to your cart whenever you are ready.
                            </p>
                        </div>

                        {items.length > 0 && (
                            <div className="flex flex-wrap items-center gap-3 self-start md:self-end">
                                {/* Share button */}
                                <button
                                    type="button"
                                    onClick={handleShareWishlist}
                                    className="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-border bg-card text-xs font-semibold text-fg hover:border-accent hover:text-accent transition-colors cursor-pointer"
                                    title="Share Wishlist"
                                >
                                    <Share2 className="w-3.5 h-3.5" />
                                    <span>Share</span>
                                </button>

                                {/* Clear Wishlist button */}
                                <button
                                    type="button"
                                    onClick={() => setIsClearModalOpen(true)}
                                    className="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-border bg-card text-xs font-semibold text-muted hover:text-rose-500 hover:border-rose-500/40 transition-colors cursor-pointer"
                                    title="Clear All Wishlist"
                                >
                                    <Trash2 className="w-3.5 h-3.5" />
                                    <span>Clear Wishlist</span>
                                </button>

                                {/* Add all to cart button */}
                                <button
                                    type="button"
                                    onClick={handleMoveAllToCart}
                                    disabled={inStockCount === 0 || movingAllToCart}
                                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-90 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
                                >
                                    <ShoppingCart className="w-4 h-4" />
                                    <span>
                                        {movingAllToCart
                                            ? "Adding..."
                                            : `Add All In-Stock to Cart (${inStockCount})`}
                                    </span>
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Main Content Area */}
                    {items.length === 0 ? (
                        /* Empty State */
                        <div className="text-center py-20 px-4 rounded-3xl bg-card border border-border my-6 shadow-sm relative overflow-hidden">
                            {/* Ambient background glow */}
                            <div className="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-rose-500/5 blur-[100px] rounded-full" />

                            <div className="relative z-10">
                                <div className="w-20 h-20 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-500 mx-auto flex items-center justify-center mb-6 shadow-inner animate-pulse">
                                    <Heart className="w-10 h-10 fill-rose-500" />
                                </div>
                                <h2 className="font-display font-bold text-2xl sm:text-3xl text-fg mb-3 uppercase tracking-wide">
                                    Your Wishlist is Empty
                                </h2>
                                <p className="text-sm text-muted max-w-md mx-auto mb-8 leading-relaxed">
                                    You haven't saved any hardware or accessories yet. Click the heart icon on any product
                                    card to keep track of items you love.
                                </p>
                                <div className="flex flex-wrap items-center justify-center gap-4">
                                    <Link
                                        href="/shop"
                                        className="inline-flex items-center gap-2 px-6 py-3 bg-accent text-accent-fg font-bold rounded-xl text-sm transition-transform active:scale-95 shadow-md hover:opacity-90"
                                    >
                                        <ShoppingBag className="w-4 h-4" />
                                        Explore Shop
                                    </Link>
                                    <Link
                                        href="/build-my-pc"
                                        className="inline-flex items-center gap-2 px-6 py-3 bg-card border border-border text-fg font-semibold rounded-xl text-sm hover:border-accent hover:text-accent transition-colors"
                                    >
                                        <Cpu className="w-4 h-4 text-accent" />
                                        Build Custom PC
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ) : (
                        /* Items List View & Controls */
                        <div className="space-y-6">
                            {/* Toolbar: Search, Category Filter, Stock Toggle, View Mode Switcher */}
                            <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 p-4 rounded-2xl bg-card border border-border shadow-xs">
                                {/* Search box */}
                                <div className="relative flex-1 max-w-md">
                                    <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" />
                                    <input
                                        type="text"
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        placeholder="Search wishlisted items..."
                                        className="w-full pl-10 pr-4 py-2 rounded-xl bg-bg border border-border text-sm text-fg placeholder:text-muted focus:outline-none focus:border-accent transition-colors"
                                    />
                                    {searchQuery && (
                                        <button
                                            type="button"
                                            onClick={() => setSearchQuery("")}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-fg"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    )}
                                </div>

                                {/* Filters and View toggles */}
                                <div className="flex flex-wrap items-center gap-3">
                                    {/* Category Filter Dropdown */}
                                    {categories.length > 0 && (
                                        <div className="flex items-center gap-2">
                                            <Filter className="w-3.5 h-3.5 text-muted hidden sm:block" />
                                            <select
                                                value={selectedCategory}
                                                onChange={(e) => setSelectedCategory(e.target.value)}
                                                className="px-3 py-2 rounded-xl bg-bg border border-border text-xs font-medium text-fg focus:outline-none focus:border-accent cursor-pointer"
                                            >
                                                <option value="all">All Categories ({items.length})</option>
                                                {categories.map((cat) => (
                                                    <option key={cat} value={cat}>
                                                        {cat}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    )}

                                    {/* In-Stock Only Pill */}
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setStockFilter((prev) =>
                                                prev === "all" ? "in_stock" : "all",
                                            )
                                        }
                                        className={cn(
                                            "inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer",
                                            stockFilter === "in_stock"
                                                ? "border-emerald-500/50 bg-emerald-500/10 text-emerald-400"
                                                : "border-border bg-bg text-muted hover:text-fg",
                                        )}
                                    >
                                        <span
                                            className={cn(
                                                "w-2 h-2 rounded-full",
                                                stockFilter === "in_stock"
                                                    ? "bg-emerald-400"
                                                    : "bg-muted",
                                            )}
                                        />
                                        <span>In Stock Only</span>
                                    </button>

                                    {/* View Mode Toggle */}
                                    <div className="inline-flex items-center rounded-xl border border-border bg-bg p-1">
                                        <button
                                            type="button"
                                            onClick={() => setViewMode("grid")}
                                            className={cn(
                                                "p-1.5 rounded-lg transition-colors cursor-pointer",
                                                viewMode === "grid"
                                                    ? "bg-card text-accent shadow-xs"
                                                    : "text-muted hover:text-fg",
                                            )}
                                            aria-label="Grid View"
                                            title="Grid View"
                                        >
                                            <Grid className="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setViewMode("list")}
                                            className={cn(
                                                "p-1.5 rounded-lg transition-colors cursor-pointer",
                                                viewMode === "list"
                                                    ? "bg-card text-accent shadow-xs"
                                                    : "text-muted hover:text-fg",
                                            )}
                                            aria-label="List View"
                                            title="List View"
                                        >
                                            <List className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Results stats */}
                            <div className="flex items-center justify-between text-xs text-muted px-1">
                                <span>
                                    Showing <strong className="text-fg">{filteredItems.length}</strong> of{" "}
                                    <strong className="text-fg">{items.length}</strong> saved items
                                </span>
                                {searchQuery && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearchQuery("");
                                            setSelectedCategory("all");
                                            setStockFilter("all");
                                        }}
                                        className="text-accent hover:underline cursor-pointer"
                                    >
                                        Reset filters
                                    </button>
                                )}
                            </div>

                            {/* Zero search results */}
                            {filteredItems.length === 0 && (
                                <div className="text-center py-16 px-4 rounded-2xl bg-card border border-border">
                                    <Search className="w-10 h-10 text-muted mx-auto mb-3" />
                                    <p className="text-base font-semibold text-fg">No matching items found</p>
                                    <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                                        We couldn't find any items matching your search or active filter. Try clearing the
                                        search term.
                                    </p>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearchQuery("");
                                            setSelectedCategory("all");
                                            setStockFilter("all");
                                        }}
                                        className="mt-4 px-4 py-2 rounded-xl bg-muted/10 border border-border text-xs font-semibold text-fg hover:border-accent transition-colors"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
                            )}

                            {/* Mode 1: Grid View (Using ProductCard) */}
                            {viewMode === "grid" && filteredItems.length > 0 && (
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    <AnimatePresence>
                                        {filteredItems.map((item) => {
                                            // Format for ProductCard
                                            const formattedProduct = {
                                                id: item.id,
                                                name: item.name,
                                                image:
                                                    item.image ??
                                                    item.images?.[0] ??
                                                    "/images/products/sample-keyboard.jpg",
                                                category: item.category || "Hardware",
                                                subCategory: item.subCategory,
                                                brand: item.brand || "ComLink",
                                                rating: item.rating ?? 5,
                                                reviewCount: item.reviews ?? 12,
                                                originalPrice: item.originalPrice ?? item.price,
                                                latestPrice: item.latestPrice ?? item.price,
                                                discountPercent:
                                                    item.discountPercent ??
                                                    (item.originalPrice && item.price && item.originalPrice > item.price
                                                        ? Math.round(
                                                              ((item.originalPrice - item.price) / item.originalPrice) * 100,
                                                          )
                                                        : 0),
                                                inStock: item.inStock !== false,
                                                condition: "Brand New",
                                            };

                                            return (
                                                <motion.div
                                                    key={item.id}
                                                    layout
                                                    initial={{ opacity: 0, scale: 0.95 }}
                                                    animate={{ opacity: 1, scale: 1 }}
                                                    exit={{ opacity: 0, scale: 0.9 }}
                                                    transition={{ duration: 0.2 }}
                                                >
                                                    <ProductCard product={formattedProduct} />
                                                </motion.div>
                                            );
                                        })}
                                    </AnimatePresence>
                                </div>
                            )}

                            {/* Mode 2: Table / List View */}
                            {viewMode === "list" && filteredItems.length > 0 && (
                                <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-xs divide-y divide-border">
                                    {/* Header (Desktop) */}
                                    <div className="hidden md:grid grid-cols-12 gap-4 px-6 py-3.5 bg-muted/10 border-b border-border text-xs font-bold uppercase tracking-wider text-muted">
                                        <div className="col-span-6">Product Details</div>
                                        <div className="col-span-2 text-center">Status</div>
                                        <div className="col-span-2 text-right">Unit Price</div>
                                        <div className="col-span-2 text-right">Actions</div>
                                    </div>

                                    {/* List rows */}
                                    <AnimatePresence>
                                        {filteredItems.map((item) => {
                                            const isInStock = item.inStock !== false;
                                            const displayPrice = item.latestPrice ?? item.price;
                                            const hasDiscount =
                                                item.originalPrice && item.originalPrice > displayPrice;
                                            const isItemAdded = addedIds[item.id];

                                            return (
                                                <motion.div
                                                    key={item.id}
                                                    layout
                                                    initial={{ opacity: 0 }}
                                                    animate={{ opacity: 1 }}
                                                    exit={{ opacity: 0 }}
                                                    className="p-4 sm:p-6 flex flex-col md:grid md:grid-cols-12 md:items-center gap-4 hover:bg-muted/5 transition-colors"
                                                >
                                                    {/* Product Details (Col 6) */}
                                                    <div className="md:col-span-6 flex items-center gap-4">
                                                        <Link
                                                            href={`/product/${item.id}`}
                                                            className="w-20 h-20 shrink-0 rounded-2xl bg-muted/10 border border-border/80 overflow-hidden p-2 flex items-center justify-center hover:border-accent transition-colors"
                                                        >
                                                            <img
                                                                src={
                                                                    item.image ??
                                                                    item.images?.[0] ??
                                                                    "/images/products/sample-keyboard.jpg"
                                                                }
                                                                alt={item.name}
                                                                className="max-h-full max-w-full object-contain"
                                                            />
                                                        </Link>

                                                        <div className="min-w-0 flex-1">
                                                            <div className="flex items-center gap-2 mb-1">
                                                                <span className="text-[11px] font-bold uppercase tracking-wider text-accent">
                                                                    {item.brand || "Hardware"}
                                                                </span>
                                                                {item.category && (
                                                                    <>
                                                                        <span className="text-muted text-[10px]">•</span>
                                                                        <span className="text-[11px] text-muted">
                                                                            {item.category}
                                                                        </span>
                                                                    </>
                                                                )}
                                                            </div>
                                                            <Link
                                                                href={`/product/${item.id}`}
                                                                className="font-semibold text-sm sm:text-base text-fg hover:text-accent transition-colors line-clamp-2"
                                                            >
                                                                {item.name}
                                                            </Link>
                                                        </div>
                                                    </div>

                                                    {/* Stock status (Col 2 - Desktop) */}
                                                    <div className="md:col-span-2 flex items-center md:justify-center">
                                                        <span
                                                            className={cn(
                                                                "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider",
                                                                isInStock
                                                                    ? "border-emerald-500/30 bg-emerald-500/10 text-emerald-400"
                                                                    : "border-rose-500/30 bg-rose-500/10 text-rose-400",
                                                            )}
                                                        >
                                                            <span
                                                                className={cn(
                                                                    "w-1.5 h-1.5 rounded-full",
                                                                    isInStock ? "bg-emerald-400" : "bg-rose-400",
                                                                )}
                                                            />
                                                            {isInStock ? "In Stock" : "Out of Stock"}
                                                        </span>
                                                    </div>

                                                    {/* Unit Price (Col 2 - Desktop) */}
                                                    <div className="md:col-span-2 flex flex-col items-start md:items-end justify-center">
                                                        <span className="text-base font-bold font-display text-accent">
                                                            {formatPrice(displayPrice)}
                                                        </span>
                                                        {hasDiscount && (
                                                            <span className="text-xs text-muted line-through">
                                                                {formatPrice(item.originalPrice!)}
                                                            </span>
                                                        )}
                                                    </div>

                                                    {/* Action Buttons (Col 2 - Desktop) */}
                                                    <div className="md:col-span-2 flex items-center justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-border/40">
                                                        {/* Move to Cart */}
                                                        <button
                                                            type="button"
                                                            onClick={() => handleAddSingleToCart(item)}
                                                            disabled={!isInStock}
                                                            className={cn(
                                                                "inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all cursor-pointer shadow-xs",
                                                                isItemAdded
                                                                    ? "bg-emerald-500 text-white"
                                                                    : isInStock
                                                                      ? "bg-accent text-accent-fg hover:opacity-90 active:scale-95"
                                                                      : "bg-muted/20 text-muted cursor-not-allowed",
                                                            )}
                                                        >
                                                            {isItemAdded ? (
                                                                <>
                                                                    <Check className="w-3.5 h-3.5" />
                                                                    <span>Added</span>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <ShoppingCart className="w-3.5 h-3.5" />
                                                                    <span>Add to Cart</span>
                                                                </>
                                                            )}
                                                        </button>

                                                        {/* Wishlist toggle / Remove button */}
                                                        <button
                                                            type="button"
                                                            onClick={() => removeFromWishlist(item.id)}
                                                            aria-label="Remove from wishlist"
                                                            className="p-2 rounded-xl border border-border bg-bg text-muted hover:text-rose-500 hover:border-rose-500/30 transition-colors cursor-pointer"
                                                            title="Remove from wishlist"
                                                        >
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </motion.div>
                                            );
                                        })}
                                    </AnimatePresence>
                                </div>
                            )}

                            {/* Bottom Navigation Link */}
                            <div className="pt-4 flex items-center justify-between">
                                <Link
                                    href="/shop"
                                    className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-accent transition-colors"
                                >
                                    <ArrowLeft className="w-4 h-4" />
                                    Continue Shopping
                                </Link>
                                <span className="text-xs text-muted">
                                    All prices are in LKR and inclusive of standard warranties.
                                </span>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Clear Wishlist Confirmation Modal */}
            <AnimatePresence>
                {isClearModalOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={() => setIsClearModalOpen(false)}
                            className="fixed inset-0 bg-black/60 backdrop-blur-sm"
                        />

                        <motion.div
                            initial={{ opacity: 0, scale: 0.95, y: 10 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.95, y: 10 }}
                            className="relative w-full max-w-md rounded-3xl border border-border bg-card p-6 shadow-2xl z-10"
                        >
                            <div className="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center mb-4">
                                <Trash2 className="w-6 h-6" />
                            </div>

                            <h3 className="text-lg font-bold text-fg font-display uppercase tracking-wide">
                                Clear Wishlist?
                            </h3>
                            <p className="text-sm text-muted mt-2 leading-relaxed">
                                Are you sure you want to remove all {items.length} items from your wishlist? This action
                                cannot be undone.
                            </p>

                            <div className="mt-6 flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => setIsClearModalOpen(false)}
                                    className="px-4 py-2.5 rounded-xl border border-border text-xs font-semibold text-fg hover:bg-muted/10 transition-colors cursor-pointer"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        clearWishlist();
                                        setIsClearModalOpen(false);
                                    }}
                                    className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold uppercase tracking-wider shadow-md transition-all cursor-pointer"
                                >
                                    Yes, Clear All
                                </button>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </>
    );
}
