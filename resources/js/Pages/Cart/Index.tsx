import React, { useState } from "react";
import { Head, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { useCart } from "@/contexts/CartContext";
import {
    ShoppingBag,
    Trash2,
    Plus,
    Minus,
    ArrowLeft,
    ArrowRight,
    ShieldCheck,
    Truck,
    RotateCcw,
    Tag,
    Check,
    Cpu,
} from "lucide-react";
import { toast } from "sonner";

export default function CartIndex() {
    return (
        <AppLayout>
            <CartContent />
        </AppLayout>
    );
}

function CartContent() {
    const { items, updateQuantity, removeFromCart, clearCart, totalItems, totalPrice } =
        useCart();
    const [couponCode, setCouponCode] = useState("");
    const [appliedCoupon, setAppliedCoupon] = useState<string | null>(null);
    const [discountAmount, setDiscountAmount] = useState(0);

    const formatPrice = (val: number) =>
        `Rs. ${Number(val || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;

    const handleApplyCoupon = (e: React.FormEvent) => {
        e.preventDefault();
        const code = couponCode.trim().toUpperCase();
        if (!code) {
            toast.error("Please enter a coupon code");
            return;
        }

        if (code === "COMLINK10" || code === "VICKY10") {
            const discount = totalPrice * 0.1;
            setDiscountAmount(discount);
            setAppliedCoupon(code);
            toast.success(`Coupon "${code}" applied! 10% discount added.`);
        } else if (code === "FREESHIP") {
            setAppliedCoupon(code);
            toast.success(`Coupon "${code}" applied! Free islandwide shipping.`);
        } else {
            toast.error("Invalid coupon code. Try 'COMLINK10'");
        }
    };

    const finalTotal = Math.max(0, totalPrice - discountAmount);

    return (
        <>
            <Head title="Shopping Cart - Vicky Store / ComLink" />

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
                        <span className="text-accent font-semibold">Shopping Cart</span>
                    </nav>

                    {/* Page Header */}
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-border gap-4">
                        <div>
                            <h1 className="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display uppercase tracking-tight text-fg flex items-center gap-3">
                                <ShoppingBag className="w-8 h-8 text-accent" />
                                Shopping Cart
                            </h1>
                            <p className="text-sm text-muted mt-1">
                                Review your selected hardware, update quantities, and proceed to checkout.
                            </p>
                        </div>

                        {items.length > 0 && (
                            <span className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-accent/10 border border-accent/20 text-accent font-semibold text-xs tracking-wide self-start sm:self-auto">
                                <Check className="w-4 h-4" />
                                {totalItems} {totalItems === 1 ? "item" : "items"} in cart
                            </span>
                        )}
                    </div>

                    {/* Content Section */}
                    {items.length === 0 ? (
                        /* Empty State */
                        <div className="text-center py-20 px-4 rounded-3xl bg-card border border-border my-6 shadow-sm">
                            <div className="w-20 h-20 rounded-full bg-accent/10 text-accent mx-auto flex items-center justify-center mb-5">
                                <ShoppingBag className="w-10 h-10" />
                            </div>
                            <h2 className="font-display font-bold text-2xl text-fg mb-2 uppercase tracking-wide">
                                Your Shopping Cart is Empty
                            </h2>
                            <p className="text-sm text-muted max-w-md mx-auto mb-8">
                                Looks like you haven't added any components or accessories yet. Explore our verified hardware collection or build your custom PC!
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
                                    className="inline-flex items-center gap-2 px-6 py-3 bg-card border border-border text-fg font-semibold rounded-xl text-sm hover:border-accent transition-colors"
                                >
                                    <Cpu className="w-4 h-4 text-accent" />
                                    Build My PC
                                </Link>
                            </div>
                        </div>
                    ) : (
                        /* Cart Grid */
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            {/* Left Column: Cart Items List */}
                            <div className="lg:col-span-8 space-y-4">
                                <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
                                    {/* Table Header (Desktop) */}
                                    <div className="hidden md:grid grid-cols-12 gap-4 px-6 py-3.5 bg-muted/10 border-b border-border text-xs font-bold uppercase tracking-wider text-muted">
                                        <div className="col-span-6">Product</div>
                                        <div className="col-span-2 text-center">Price</div>
                                        <div className="col-span-2 text-center">Quantity</div>
                                        <div className="col-span-2 text-right">Total</div>
                                    </div>

                                    {/* Items List */}
                                    <div className="divide-y divide-border">
                                        {items.map((item) => {
                                            const itemSubtotal = (item.price || 0) * item.quantity;
                                            return (
                                                <div
                                                    key={item.id}
                                                    className="p-4 sm:p-6 flex flex-col md:grid md:grid-cols-12 md:items-center gap-4 hover:bg-muted/5 transition-colors"
                                                >
                                                    {/* Product info (Col 6) */}
                                                    <div className="md:col-span-6 flex items-center gap-4">
                                                        <Link
                                                            href={`/product/${item.productId}`}
                                                            className="w-18 h-18 sm:w-20 sm:h-20 shrink-0 rounded-xl bg-card border border-border/80 overflow-hidden p-2 flex items-center justify-center hover:border-accent transition-colors"
                                                        >
                                                            <img
                                                                src={item.image}
                                                                alt={item.name}
                                                                className="max-h-full max-w-full object-contain"
                                                            />
                                                        </Link>

                                                        <div className="min-w-0 flex-1">
                                                            <Link
                                                                href={`/product/${item.productId}`}
                                                                className="font-semibold text-sm sm:text-base text-fg hover:text-accent transition-colors line-clamp-2"
                                                            >
                                                                {item.name}
                                                            </Link>
                                                            <div className="text-xs text-muted mt-1 flex items-center gap-3">
                                                                <span>Unit: {formatPrice(item.price)}</span>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => removeFromCart(item.id)}
                                                                    className="text-red-500 hover:text-red-600 inline-flex items-center gap-1 cursor-pointer font-medium"
                                                                >
                                                                    <Trash2 className="w-3.5 h-3.5" />
                                                                    <span>Remove</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {/* Unit Price (Col 2 - Desktop) */}
                                                    <div className="hidden md:block md:col-span-2 text-center text-sm font-semibold text-fg">
                                                        {formatPrice(item.price)}
                                                    </div>

                                                    {/* Quantity Control (Col 2) */}
                                                    <div className="md:col-span-2 flex items-center md:justify-center justify-between pt-2 md:pt-0 border-t md:border-t-0 border-border/40">
                                                        <span className="text-xs text-muted md:hidden font-medium">Quantity:</span>
                                                        <div className="inline-flex items-center rounded-lg border border-border bg-bg p-1 shadow-xs">
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    updateQuantity(item.id, Math.max(1, item.quantity - 1))
                                                                }
                                                                disabled={item.quantity <= 1}
                                                                className="h-7 w-7 flex items-center justify-center rounded text-muted hover:text-fg hover:bg-muted/20 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                                                aria-label="Decrease quantity"
                                                            >
                                                                <Minus className="w-3.5 h-3.5" />
                                                            </button>
                                                            <span className="w-9 text-center text-xs font-bold text-fg">
                                                                {item.quantity}
                                                            </span>
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    updateQuantity(item.id, item.quantity + 1)
                                                                }
                                                                className="h-7 w-7 flex items-center justify-center rounded text-muted hover:text-fg hover:bg-muted/20 transition-colors"
                                                                aria-label="Increase quantity"
                                                            >
                                                                <Plus className="w-3.5 h-3.5" />
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {/* Line Total (Col 2) */}
                                                    <div className="md:col-span-2 flex items-center md:justify-end justify-between">
                                                        <span className="text-xs text-muted md:hidden font-medium">Subtotal:</span>
                                                        <span className="text-base font-bold font-display text-accent">
                                                            {formatPrice(itemSubtotal)}
                                                        </span>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Action Buttons Below Items */}
                                <div className="flex flex-wrap items-center justify-between gap-4 pt-2">
                                    <Link
                                        href="/shop"
                                        className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-accent transition-colors"
                                    >
                                        <ArrowLeft className="w-4 h-4" />
                                        Continue Shopping
                                    </Link>

                                    <button
                                        type="button"
                                        onClick={() => {
                                            if (window.confirm("Are you sure you want to clear your shopping cart?")) {
                                                clearCart();
                                                toast.info("Shopping cart cleared");
                                            }
                                        }}
                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-muted hover:text-red-500 transition-colors cursor-pointer"
                                    >
                                        <Trash2 className="w-3.5 h-3.5" />
                                        Clear Entire Cart
                                    </button>
                                </div>
                            </div>

                            {/* Right Column: Order Summary & Checkout */}
                            <div className="lg:col-span-4 space-y-6">
                                <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
                                    <h2 className="font-display font-bold text-lg uppercase tracking-wider text-fg pb-4 border-b border-border">
                                        Order Summary
                                    </h2>

                                    {/* Cost Breakdown */}
                                    <div className="py-4 space-y-3 text-sm border-b border-border">
                                        <div className="flex justify-between text-muted">
                                            <span>Subtotal ({totalItems} items)</span>
                                            <span className="text-fg font-semibold">{formatPrice(totalPrice)}</span>
                                        </div>

                                        {discountAmount > 0 && (
                                            <div className="flex justify-between text-emerald-500 font-medium">
                                                <span>Coupon Discount</span>
                                                <span>-{formatPrice(discountAmount)}</span>
                                            </div>
                                        )}

                                        <div className="flex justify-between text-muted">
                                            <span>Shipping Fee</span>
                                            <span className="text-xs text-emerald-500 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded">
                                                Islandwide Free
                                            </span>
                                        </div>

                                        <div className="flex justify-between text-muted">
                                            <span>Estimated Tax / VAT</span>
                                            <span className="text-xs text-muted">Included</span>
                                        </div>
                                    </div>

                                    {/* Grand Total */}
                                    <div className="py-4 flex items-baseline justify-between">
                                        <div>
                                            <span className="font-display font-extrabold text-base text-fg uppercase">
                                                Total Amount
                                            </span>
                                            <p className="text-[11px] text-muted">Official warranty included</p>
                                        </div>
                                        <span className="text-2xl font-black font-display text-accent">
                                            {formatPrice(finalTotal)}
                                        </span>
                                    </div>

                                    {/* Promo Code Form */}
                                    <form onSubmit={handleApplyCoupon} className="pt-2 pb-4">
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-muted mb-2">
                                            Promo or Coupon Code
                                        </label>
                                        <div className="flex gap-2">
                                            <div className="relative flex-1">
                                                <Tag className="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" />
                                                <input
                                                    type="text"
                                                    value={couponCode}
                                                    onChange={(e) => setCouponCode(e.target.value)}
                                                    placeholder="e.g. COMLINK10"
                                                    className="w-full pl-9 pr-3 py-2 text-xs uppercase bg-bg border border-border rounded-xl text-fg focus:outline-none focus:border-accent"
                                                />
                                            </div>
                                            <button
                                                type="submit"
                                                className="px-4 py-2 bg-muted/20 hover:bg-muted/30 text-fg font-bold text-xs rounded-xl transition-colors cursor-pointer"
                                            >
                                                Apply
                                            </button>
                                        </div>
                                        {appliedCoupon && (
                                            <p className="text-[11px] text-emerald-500 mt-1.5 flex items-center gap-1 font-medium">
                                                <Check className="w-3.5 h-3.5" />
                                                Active coupon: {appliedCoupon}
                                            </p>
                                        )}
                                    </form>

                                    {/* Checkout Button */}
                                    <button
                                        type="button"
                                        onClick={() => {
                                            toast.success("Proceeding to checkout! (Order processing ready)");
                                        }}
                                        className="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-accent text-accent-fg font-bold uppercase tracking-wider text-sm transition-all hover:opacity-90 active:scale-[0.99] shadow-md cursor-pointer"
                                    >
                                        <span>Proceed to Checkout</span>
                                        <ArrowRight className="w-4 h-4" />
                                    </button>

                                    {/* Secure Guarantee Badges */}
                                    <div className="mt-6 pt-5 border-t border-border/60 space-y-2.5">
                                        <div className="flex items-center gap-2.5 text-xs text-muted">
                                            <ShieldCheck className="w-4 h-4 text-emerald-500 shrink-0" />
                                            <span>100% Genuine Hardware with Manufacturer Warranty</span>
                                        </div>
                                        <div className="flex items-center gap-2.5 text-xs text-muted">
                                            <Truck className="w-4 h-4 text-cyan-500 shrink-0" />
                                            <span>Secure Islandwide Courier Delivery</span>
                                        </div>
                                        <div className="flex items-center gap-2.5 text-xs text-muted">
                                            <RotateCcw className="w-4 h-4 text-amber-500 shrink-0" />
                                            <span>7 Days Easy Return / Replacement Policy</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
