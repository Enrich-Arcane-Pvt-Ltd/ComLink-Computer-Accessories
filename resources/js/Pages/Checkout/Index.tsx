import React, { useState, useEffect, useRef } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { useCart } from "@/contexts/CartContext";
import { formatPrice } from "@/utils/currency";
import {
    CreditCard,
    ShieldCheck,
    Truck,
    Lock,
    ShoppingBag,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    User,
    Mail,
    Phone,
    MapPin,
    Building,
    Globe,
    Loader2,
} from "lucide-react";
import { toast } from "sonner";
import axios from "axios";

declare global {
    interface Window {
        Stripe?: any;
    }
}

interface CheckoutPrefillData {
    name: string;
    email: string;
    phone: string;
    secondary_phone: string;
    address_line_1: string;
    address_line_2: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
}

interface CheckoutProps {
    prefill: CheckoutPrefillData;
    stripeKey: string;
}

export default function CheckoutIndex({ prefill, stripeKey }: CheckoutProps) {
    return (
        <AppLayout>
            <CheckoutContent prefill={prefill} stripeKey={stripeKey} />
        </AppLayout>
    );
}

function CheckoutContent({ prefill, stripeKey }: CheckoutProps) {
    const { items, totalPrice, clearCart, totalItems } = useCart();

    // Form state prefilled from profile
    const [name, setName] = useState(prefill?.name || "");
    const [email, setEmail] = useState(prefill?.email || "");
    const [phone, setPhone] = useState(prefill?.phone || "");
    const [secondaryPhone, setSecondaryPhone] = useState(prefill?.secondary_phone || "");

    const [addressLine1, setAddressLine1] = useState(prefill?.address_line_1 || "");
    const [addressLine2, setAddressLine2] = useState(prefill?.address_line_2 || "");
    const [city, setCity] = useState(prefill?.city || "");
    const [state, setState] = useState(prefill?.state || "");
    const [postalCode, setPostalCode] = useState(prefill?.postal_code || "");
    const [country, setCountry] = useState(prefill?.country || "Sri Lanka");

    // Stripe states
    const [stripe, setStripe] = useState<any>(null);
    const [elements, setElements] = useState<any>(null);
    const [cardElement, setCardElement] = useState<any>(null);
    const [stripeLoaded, setStripeLoaded] = useState(false);
    const [cardError, setCardError] = useState<string | null>(null);
    const [isProcessing, setIsProcessing] = useState(false);

    const cardContainerRef = useRef<HTMLDivElement>(null);

    const shippingFee = 0; // Free Islandwide delivery
    const finalTotal = totalPrice + shippingFee;

    // Load Stripe.js library
    useEffect(() => {
        if (window.Stripe) {
            initializeStripe();
            return;
        }

        const script = document.createElement("script");
        script.src = "https://js.stripe.com/v3/";
        script.async = true;
        script.onload = () => {
            initializeStripe();
        };
        document.body.appendChild(script);

        return () => {
            // cleanup if needed
        };
    }, [stripeKey]);

    const initializeStripe = () => {
        if (!window.Stripe || !stripeKey) return;
        const stripeInstance = window.Stripe(stripeKey);
        setStripe(stripeInstance);

        const elementsInstance = stripeInstance.elements();
        setElements(elementsInstance);
        setStripeLoaded(true);
    };

    // Mount Card Element once Stripe elements is ready
    useEffect(() => {
        if (!elements || !cardContainerRef.current) return;

        // Customize card element style for dark/light themes
        const card = elements.create("card", {
            style: {
                base: {
                    color: "#f8fafc",
                    fontFamily: '"Outfit", sans-serif',
                    fontSmoothing: "antialiased",
                    fontSize: "14px",
                    "::placeholder": {
                        color: "#94a3b8",
                    },
                },
                invalid: {
                    color: "#ef4444",
                    iconColor: "#ef4444",
                },
            },
            hidePostalCode: true,
        });

        card.mount(cardContainerRef.current);
        card.on("change", (event: any) => {
            if (event.error) {
                setCardError(event.error.message);
            } else {
                setCardError(null);
            }
        });

        setCardElement(card);

        return () => {
            card.destroy();
        };
    }, [elements]);

    const handlePayNow = async (e: React.FormEvent) => {
        e.preventDefault();

        // 1. Validation
        if (!name.trim()) {
            toast.error("Please enter your User Name.");
            return;
        }
        if (!email.trim()) {
            toast.error("Please enter a valid Email Address.");
            return;
        }
        if (!phone.trim()) {
            toast.error("Please enter your Primary Phone Number.");
            return;
        }
        if (!addressLine1.trim()) {
            toast.error("Please enter your Street Address.");
            return;
        }
        if (!city.trim()) {
            toast.error("Please enter your City / Town.");
            return;
        }
        if (items.length === 0) {
            toast.error("Your cart is empty. Please add items to checkout.");
            return;
        }

        if (!stripe || !cardElement) {
            toast.error("Payment system is still initializing. Please wait a moment.");
            return;
        }

        setIsProcessing(true);
        setCardError(null);

        try {
            // 2. Request PaymentIntent from backend
            const intentRes = await axios.post("/checkout/payment-intent", {
                amount: finalTotal,
                email: email.trim(),
                name: name.trim(),
            });

            const { clientSecret, paymentIntentId } = intentRes.data;

            // 3. Confirm card payment with Stripe
            const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(
                clientSecret,
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: name.trim(),
                            email: email.trim(),
                            phone: phone.trim(),
                            address: {
                                line1: addressLine1.trim(),
                                line2: addressLine2.trim() || undefined,
                                city: city.trim(),
                                state: state.trim() || undefined,
                                postal_code: postalCode.trim() || undefined,
                                country: "LK",
                            },
                        },
                    },
                },
            );

            if (stripeError) {
                setCardError(stripeError.message || "Card verification failed.");
                toast.error(stripeError.message || "Payment unsuccessful. Please check card details.");
                setIsProcessing(false);
                return;
            }

            // 4. Send completed order to backend
            const orderRes = await axios.post("/checkout/process-order", {
                customer_name: name.trim(),
                customer_email: email.trim(),
                customer_phone: phone.trim(),
                customer_secondary_phone: secondaryPhone.trim() || null,
                shipping_address_line_1: addressLine1.trim(),
                shipping_address_line_2: addressLine2.trim() || null,
                shipping_city: city.trim(),
                shipping_state: state.trim() || null,
                shipping_postal_code: postalCode.trim() || null,
                shipping_country: country.trim() || "Sri Lanka",
                items: items.map((i) => ({
                    productId: i.productId,
                    name: i.name,
                    price: i.price,
                    quantity: i.quantity,
                    image: i.image,
                })),
                subtotal: totalPrice,
                shipping_fee: shippingFee,
                discount_amount: 0,
                total_amount: finalTotal,
                payment_intent_id: paymentIntent?.id || paymentIntentId,
            });

            if (orderRes.data.success) {
                clearCart();
                toast.success("Payment successful! Your order has been placed in Processing status.");
                router.visit("/orders");
            } else {
                toast.error("Order recording encountered an issue. Please contact support.");
                setIsProcessing(false);
            }
        } catch (err: any) {
            console.error(err);
            const errMsg =
                err.response?.data?.error ||
                err.response?.data?.message ||
                "Failed to process transaction. Please try again.";
            setCardError(errMsg);
            toast.error(errMsg);
            setIsProcessing(false);
        }
    };

    if (items.length === 0) {
        return (
            <div className="min-h-[70vh] bg-bg flex items-center justify-center px-4 py-16">
                <div className="text-center max-w-md w-full bg-card p-8 rounded-3xl border border-border shadow-lg">
                    <div className="w-16 h-16 rounded-2xl bg-accent/10 text-accent flex items-center justify-center mx-auto mb-4">
                        <ShoppingBag className="w-8 h-8" />
                    </div>
                    <h2 className="text-xl font-bold font-display uppercase tracking-wide text-fg mb-2">
                        Your Cart is Empty
                    </h2>
                    <p className="text-xs text-muted mb-6">
                        There are no products in your shopping cart to checkout.
                    </p>
                    <Link
                        href="/shop"
                        className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider hover:opacity-90 transition-all shadow-md"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Return to Shop</span>
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <>
            <Head title="Secure Checkout - Vicky Store / ComLink" />

            <div className="min-h-screen bg-bg text-fg py-8 sm:py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8 flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-border/70">
                        <div>
                            <div className="flex items-center gap-2 text-xs text-muted mb-2">
                                <Link href="/cart" className="hover:text-accent transition-colors flex items-center gap-1">
                                    <ArrowLeft className="w-3.5 h-3.5" />
                                    <span>Back to Shopping Cart</span>
                                </Link>
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-black font-display uppercase tracking-tight text-fg flex items-center gap-3">
                                <Lock className="w-7 h-7 text-emerald-500" />
                                Secure Checkout
                            </h1>
                            <p className="text-xs text-muted mt-1">
                                Complete your customer details and payment to finalize your official hardware order.
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handlePayNow} className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        {/* ==================================================== */}
                        {/* LEFT COLUMN: CUSTOMER DETAILS & SHIPPING FORM        */}
                        {/* ==================================================== */}
                        <div className="lg:col-span-7 space-y-6">
                            {/* Section 1: User Information */}
                            <div className="rounded-2xl border border-border/80 bg-card p-6 shadow-sm">
                                <div className="flex items-center justify-between mb-5 pb-3 border-b border-border/50">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                                            <User className="w-4 h-4" />
                                        </div>
                                        <h2 className="font-display font-extrabold text-base uppercase tracking-wide text-fg">
                                            1. User Information
                                        </h2>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {/* User Name */}
                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            User Name <span className="text-rose-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <User className="w-4 h-4 text-muted absolute left-3.5 top-1/2 -translate-y-1/2" />
                                            <input
                                                type="text"
                                                required
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                placeholder="e.g. Pasindu Senanayake"
                                                className="w-full rounded-xl border border-border bg-bg/80 pl-10 pr-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                            />
                                        </div>
                                    </div>

                                    {/* Email Address */}
                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Email Address <span className="text-rose-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <Mail className="w-4 h-4 text-muted absolute left-3.5 top-1/2 -translate-y-1/2" />
                                            <input
                                                type="email"
                                                required
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                placeholder="e.g. customer@example.com"
                                                className="w-full rounded-xl border border-border bg-bg/80 pl-10 pr-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                            />
                                        </div>
                                    </div>

                                    {/* Primary Phone */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Primary Phone <span className="text-rose-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <Phone className="w-4 h-4 text-muted absolute left-3.5 top-1/2 -translate-y-1/2" />
                                            <input
                                                type="tel"
                                                required
                                                value={phone}
                                                onChange={(e) => setPhone(e.target.value)}
                                                placeholder="e.g. +94 77 123 4567"
                                                className="w-full rounded-xl border border-border bg-bg/80 pl-10 pr-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                            />
                                        </div>
                                    </div>

                                    {/* Secondary Phone (Optional) */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Secondary Phone (Optional)
                                        </label>
                                        <div className="relative">
                                            <Phone className="w-4 h-4 text-muted absolute left-3.5 top-1/2 -translate-y-1/2" />
                                            <input
                                                type="tel"
                                                value={secondaryPhone}
                                                onChange={(e) => setSecondaryPhone(e.target.value)}
                                                placeholder="e.g. +94 11 234 5678"
                                                className="w-full rounded-xl border border-border bg-bg/80 pl-10 pr-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Section 2: Delivery & Shipping Address */}
                            <div className="rounded-2xl border border-border/80 bg-card p-6 shadow-sm">
                                <div className="flex items-center justify-between mb-5 pb-3 border-b border-border/50">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                                            <MapPin className="w-4 h-4" />
                                        </div>
                                        <h2 className="font-display font-extrabold text-base uppercase tracking-wide text-fg">
                                            2. Delivery & Shipping Address
                                        </h2>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {/* Street Address */}
                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Street Address <span className="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={addressLine1}
                                            onChange={(e) => setAddressLine1(e.target.value)}
                                            placeholder="e.g. 142 Galle Road, Bambalapitiya"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>

                                    {/* Apartment, Suite, Unit, etc. (Optional) */}
                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Apartment, Suite, Unit, etc. (Optional)
                                        </label>
                                        <input
                                            type="text"
                                            value={addressLine2}
                                            onChange={(e) => setAddressLine2(e.target.value)}
                                            placeholder="e.g. Level 4, Suite 402"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>

                                    {/* City / Town */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            City / Town <span className="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            required
                                            value={city}
                                            onChange={(e) => setCity(e.target.value)}
                                            placeholder="e.g. Colombo"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>

                                    {/* State / Province */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            State / Province
                                        </label>
                                        <input
                                            type="text"
                                            value={state}
                                            onChange={(e) => setState(e.target.value)}
                                            placeholder="e.g. Western Province"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>

                                    {/* Postal Code */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Postal Code
                                        </label>
                                        <input
                                            type="text"
                                            value={postalCode}
                                            onChange={(e) => setPostalCode(e.target.value)}
                                            placeholder="e.g. 00400"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>

                                    {/* Country */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                            Country
                                        </label>
                                        <input
                                            type="text"
                                            value={country}
                                            onChange={(e) => setCountry(e.target.value)}
                                            placeholder="e.g. Sri Lanka"
                                            className="w-full rounded-xl border border-border bg-bg/80 px-3.5 py-2.5 text-xs text-fg focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Section 3: Stripe Payment Gateway */}
                            <div className="rounded-2xl border border-cyan-500/30 bg-card p-6 shadow-sm relative overflow-hidden">
                                <div className="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none" />

                                <div className="flex items-center justify-between mb-5 pb-3 border-b border-border/50">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-lg bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
                                            <CreditCard className="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h2 className="font-display font-extrabold text-base uppercase tracking-wide text-fg">
                                                3. Stripe Payment Gateway
                                            </h2>
                                            <p className="text-[11px] text-muted">
                                                Fast & secure test credit / debit card processing
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Stripe Card Element Box */}
                                <div className="space-y-3">
                                    <label className="block text-xs font-bold uppercase tracking-wider text-muted">
                                        Card Details (Number, Expiry, CVC) <span className="text-rose-500">*</span>
                                    </label>
                                    <div className="p-3.5 rounded-xl border border-border bg-slate-900/90 focus-within:border-accent focus-within:ring-1 focus-within:ring-accent transition-all">
                                        <div ref={cardContainerRef} id="card-element" className="min-h-[24px]" />
                                    </div>

                                    {cardError && (
                                        <div className="flex items-center gap-2 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs">
                                            <AlertCircle className="w-4 h-4 shrink-0" />
                                            <span>{cardError}</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* ==================================================== */}
                        {/* RIGHT COLUMN: ORDER SUMMARY & PAY NOW BUTTON        */}
                        {/* ==================================================== */}
                        <div className="lg:col-span-5 space-y-6">
                            <div className="rounded-2xl border border-border/80 bg-card p-6 shadow-sm sticky top-24">
                                <h2 className="font-display font-extrabold text-base uppercase tracking-wide text-fg mb-4 pb-3 border-b border-border/50 flex items-center justify-between">
                                    <span>Order Summary</span>
                                    <span className="text-xs text-muted font-normal">
                                        {totalItems} {totalItems === 1 ? "unit" : "units"} ({items.length} {items.length === 1 ? "product" : "products"})
                                    </span>
                                </h2>

                                {/* Product Items List */}
                                <div className="divide-y divide-border/40 max-h-72 overflow-y-auto pr-1 mb-5">
                                    {items.map((item) => (
                                        <div key={item.id} className="py-3 flex items-center gap-3">
                                            <div className="w-14 h-14 rounded-xl overflow-hidden bg-bg border border-border shrink-0 flex items-center justify-center p-1">
                                                <img
                                                    src={item.image || "/images/placeholder.png"}
                                                    alt={item.name}
                                                    className="w-full h-full object-cover rounded-lg"
                                                />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <h4 className="text-xs font-semibold text-fg truncate">
                                                    {item.name}
                                                </h4>
                                                <p className="text-[11px] text-muted mt-0.5">
                                                    {formatPrice(item.price)} × {item.quantity}
                                                </p>
                                            </div>
                                            <span className="text-xs font-bold text-fg shrink-0">
                                                {formatPrice(item.price * item.quantity)}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                {/* Price Breakdown */}
                                <div className="space-y-2.5 pt-3 border-t border-border/60 text-xs">
                                    <div className="flex justify-between text-muted">
                                        <span>Subtotal</span>
                                        <span className="font-semibold text-fg">{formatPrice(totalPrice)}</span>
                                    </div>
                                    <div className="flex justify-between text-muted">
                                        <span>Courier Delivery</span>
                                        <span className="text-emerald-500 font-semibold uppercase text-[11px]">
                                            Free Islandwide
                                        </span>
                                    </div>
                                    <div className="pt-3 border-t border-border/60 flex items-baseline justify-between">
                                        <div>
                                            <span className="font-display font-extrabold text-sm uppercase tracking-wide text-fg">
                                                Grand Total
                                            </span>
                                            <p className="text-[10px] text-muted">Includes taxes & official warranty</p>
                                        </div>
                                        <span className="text-2xl font-black font-display text-accent">
                                            {formatPrice(finalTotal)}
                                        </span>
                                    </div>
                                </div>

                                {/* Pay Now Button */}
                                <button
                                    type="submit"
                                    disabled={isProcessing}
                                    className="w-full mt-6 py-4 px-6 rounded-xl bg-accent text-accent-fg font-black uppercase tracking-wider text-sm flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.99] transition-all shadow-lg hover:shadow-cyan-500/25 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    {isProcessing ? (
                                        <>
                                            <Loader2 className="w-5 h-5 animate-spin" />
                                            <span>Processing Payment...</span>
                                        </>
                                    ) : (
                                        <>
                                            <Lock className="w-4 h-4" />
                                            <span>Pay Now ({formatPrice(finalTotal)})</span>
                                        </>
                                    )}
                                </button>

                                {/* Guarantees */}
                                <div className="mt-5 pt-4 border-t border-border/50 space-y-2 text-[11px] text-muted">
                                    <div className="flex items-center gap-2">
                                        <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                        <span>Official ComLink hardware warranty active on dispatch</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Truck className="w-3.5 h-3.5 text-cyan-500 shrink-0" />
                                        <span>Track real-time status in Processing under My Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
