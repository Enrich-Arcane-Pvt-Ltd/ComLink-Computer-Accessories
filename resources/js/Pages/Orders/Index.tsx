import React, { useState, useMemo } from "react";
import { Head, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import {
    Package,
    Clock,
    Box,
    Truck,
    CheckCircle2,
    Calendar,
    Eye,
    X,
    MapPin,
    Phone,
    ShoppingBag,
    ArrowRight,
    Tag,
} from "lucide-react";
import { toast } from "sonner";

export type OrderStatusType = "Processing" | "Packaging" | "Shipping" | "Delivered";

export interface OrderItem {
    id: number;
    productId: string | number;
    name: string;
    image: string;
    category: string;
    quantity: number;
    unitPrice: number;
    totalPrice: number;
}

export interface OrderData {
    id: string;
    orderNumber: string;
    createdAt: string;
    status: OrderStatusType;
    statusLabel: string;
    estimatedDelivery: string;
    paymentMethod: string;
    paymentStatus: string;
    subtotal: number;
    shippingFee: number;
    discount: number;
    total: number;
    shippingAddress: {
        recipient: string;
        address: string;
        city: string;
        phone: string;
    };
    courier?: {
        name: string;
        trackingNumber: string;
    };
    items: OrderItem[];
}

interface OrdersPageProps {
    orders: OrderData[];
}

const TABS: { key: string; label: string; icon?: any }[] = [
    { key: "All", label: "All" },
    { key: "Processing", label: "Processing", icon: Clock },
    { key: "Packaging", label: "Packaging", icon: Box },
    { key: "Shipping", label: "Shipping", icon: Truck },
    { key: "Delivered", label: "Delivered", icon: CheckCircle2 },
];

export default function OrdersIndex({ orders = [] }: OrdersPageProps) {
    return (
        <AppLayout>
            <OrdersContent orders={orders} />
        </AppLayout>
    );
}

function OrdersContent({ orders }: OrdersPageProps) {
    const [activeTab, setActiveTab] = useState<string>("All");
    const [selectedOrder, setSelectedOrder] = useState<OrderData | null>(null);

    const formatPrice = (val: number) =>
        `Rs. ${Number(val || 0).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;

    // Filter orders based on active tab
    const filteredOrders = useMemo(() => {
        if (activeTab === "All") return orders;
        return orders.filter(
            (order) => order.status.toLowerCase() === activeTab.toLowerCase(),
        );
    }, [orders, activeTab]);

    // Order counts per status
    const counts = useMemo(() => {
        return {
            All: orders.length,
            Processing: orders.filter((o) => o.status === "Processing").length,
            Packaging: orders.filter((o) => o.status === "Packaging").length,
            Shipping: orders.filter((o) => o.status === "Shipping").length,
            Delivered: orders.filter((o) => o.status === "Delivered").length,
        };
    }, [orders]);

    const getStatusBadge = (status: OrderStatusType) => {
        switch (status) {
            case "Processing":
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/25">
                        <Clock className="w-3.5 h-3.5" />
                        Processing
                    </span>
                );
            case "Packaging":
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/10 text-purple-400 border border-purple-500/25">
                        <Box className="w-3.5 h-3.5" />
                        Packaging
                    </span>
                );
            case "Shipping":
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/25">
                        <Truck className="w-3.5 h-3.5" />
                        Shipping
                    </span>
                );
            case "Delivered":
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        Delivered
                    </span>
                );
        }
    };

    return (
        <>
            <Head title="My Orders - Vicky Store / ComLink" />

            <div className="min-h-screen bg-bg text-fg py-8 sm:py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumbs */}
                    <nav className="flex items-center gap-2 text-xs sm:text-sm text-muted mb-6">
                        <Link href="/" className="hover:text-accent transition-colors">
                            Home
                        </Link>
                        <span>/</span>
                        <Link href="/profile" className="hover:text-accent transition-colors">
                            Account
                        </Link>
                        <span>/</span>
                        <span className="text-accent font-semibold">My Orders</span>
                    </nav>

                    {/* Page Title */}
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-border gap-4">
                        <div>
                            <h1 className="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display uppercase tracking-tight text-fg flex items-center gap-3">
                                <Package className="w-8 h-8 text-accent" />
                                My Orders
                            </h1>
                            <p className="text-sm text-muted mt-1">
                                View your purchase history, track live deliveries, and check hardware details.
                            </p>
                        </div>

                        <Link
                            href="/shop"
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-90 self-start sm:self-auto transition-transform active:scale-95"
                        >
                            <ShoppingBag className="w-4 h-4" />
                            Continue Shopping
                        </Link>
                    </div>

                    {/* Top Status Tabs */}
                    <div className="flex items-center gap-2 overflow-x-auto pb-4 mb-8 border-b border-border/80 scrollbar-none">
                        {TABS.map((tab) => {
                            const count = counts[tab.key as keyof typeof counts] ?? 0;
                            const isActive = activeTab === tab.key;
                            const TabIcon = tab.icon;

                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => setActiveTab(tab.key)}
                                    className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer ${
                                        isActive
                                            ? "bg-accent text-accent-fg shadow-md shadow-accent/20"
                                            : "bg-card border border-border text-muted hover:text-fg hover:border-accent/40"
                                    }`}
                                >
                                    {TabIcon && <TabIcon className="w-3.5 h-3.5" />}
                                    <span>{tab.label}</span>
                                    <span
                                        className={`px-2 py-0.5 rounded-full text-[10px] font-extrabold ${
                                            isActive
                                                ? "bg-black/15 text-accent-fg"
                                                : "bg-muted/15 text-muted"
                                        }`}
                                    >
                                        {count}
                                    </span>
                                </button>
                            );
                        })}
                    </div>

                    {/* Orders Responsive Cards List (Replaces Rigid Admin Table) */}
                    {filteredOrders.length === 0 ? (
                        /* Empty State */
                        <div className="text-center py-20 px-4 rounded-3xl bg-card border border-border my-6 shadow-sm">
                            <div className="w-16 h-16 rounded-full bg-muted/10 flex items-center justify-center mb-4 mx-auto text-muted">
                                <Package className="w-8 h-8" />
                            </div>
                            <h3 className="text-lg font-bold text-fg uppercase tracking-wide">
                                No {activeTab === "All" ? "" : activeTab} Orders Found
                            </h3>
                            <p className="text-sm text-muted max-w-sm mt-1.5 mb-6 mx-auto">
                                You do not have any orders in this category right now.
                            </p>
                            <Link
                                href="/shop"
                                className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-90"
                            >
                                <ShoppingBag className="w-4 h-4" />
                                Explore Products
                            </Link>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {filteredOrders.map((order) => {
                                const totalUnits = order.items.reduce(
                                    (sum, it) => sum + (it.quantity || 1),
                                    0,
                                );
                                const firstItem = order.items[0];

                                return (
                                    <div
                                        key={order.id}
                                        className="rounded-2xl border border-border bg-card p-5 sm:p-6 transition-all duration-300 hover:border-accent/50 hover:shadow-lg hover:shadow-accent/5 group"
                                    >
                                        {/* Top Meta Header */}
                                        <div className="flex flex-wrap items-center justify-between gap-3 pb-3 mb-4 border-b border-border/70">
                                            <div className="flex items-center gap-2 text-xs text-muted">
                                                <Calendar className="w-3.5 h-3.5 text-accent opacity-80 shrink-0" />
                                                <span className="font-medium text-fg/90">{order.createdAt}</span>
                                            </div>

                                            {/* Status Badge */}
                                            {getStatusBadge(order.status)}
                                        </div>

                                        {/* Main Content Row: Image + Details + Total & Action */}
                                        <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                                            {/* Left: First product image & details */}
                                            <div className="flex items-center gap-4 min-w-0 flex-1">
                                                {/* Single First Product Image Box */}
                                                <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-bg border border-border/80 p-2 overflow-hidden shadow-xs shrink-0 flex items-center justify-center group-hover:border-accent/40 transition-colors">
                                                    <img
                                                        src={
                                                            firstItem?.image ||
                                                             "/images/products/sample-keyboard.jpg"
                                                        }
                                                        alt={`Order #${order.orderNumber}`}
                                                        className="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                    />
                                                </div>

                                                {/* Order title & summary */}
                                                <div className="min-w-0 flex-1">
                                                    <h3 className="font-display font-black text-base sm:text-lg text-fg tracking-tight group-hover:text-accent transition-colors">
                                                        #{order.orderNumber}
                                                    </h3>
                                                    <div className="flex flex-wrap items-center gap-2 mt-1.5">
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-md bg-accent/10 border border-accent/20 text-accent font-bold text-[10px]">
                                                            {totalUnits} {totalUnits === 1 ? "unit" : "units"}
                                                        </span>
                                                        <span className="text-xs text-muted">
                                                            ({order.items.length}{" "}
                                                            {order.items.length === 1
                                                                ? "product"
                                                                : "products"} in this order)
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Right: Total Amount (Clean: no text below) + View My Order button */}
                                            <div className="flex items-center justify-between lg:justify-end gap-6 pt-3 lg:pt-0 border-t lg:border-t-0 border-border/60 shrink-0">
                                                {/* Clean Total: Just the amount */}
                                                <div className="text-left lg:text-right">
                                                    <span className="text-[11px] font-bold uppercase tracking-wider text-muted block mb-0.5">
                                                        Total
                                                    </span>
                                                    <span className="font-display font-black text-lg sm:text-2xl text-accent tracking-tight block">
                                                        {formatPrice(order.total)}
                                                    </span>
                                                </div>

                                                {/* Action: View My Order button */}
                                                <button
                                                    type="button"
                                                    onClick={() => setSelectedOrder(order)}
                                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider hover:opacity-95 shadow-md active:scale-95 transition-all cursor-pointer whitespace-nowrap"
                                                >
                                                    <Eye className="w-4 h-4" />
                                                    <span>View My Order</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {/* Order Details Modal Drawer */}
                    {selectedOrder && (
                        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                            {/* Backdrop */}
                            <div
                                className="fixed inset-0 bg-black/70 backdrop-blur-xs transition-opacity"
                                onClick={() => setSelectedOrder(null)}
                            />

                            {/* Modal Dialog */}
                            <div className="relative w-full max-w-2xl bg-card border border-border rounded-3xl shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh]">
                                {/* Modal Header */}
                                <div className="p-5 sm:p-6 bg-gradient-to-r from-card to-muted/10 border-b border-border flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-accent text-accent-fg flex items-center justify-center">
                                            <Package className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <h3 className="font-display font-black text-lg text-fg">
                                                    Order #{selectedOrder.orderNumber}
                                                </h3>
                                                {getStatusBadge(selectedOrder.status)}
                                            </div>
                                            <p className="text-xs text-muted mt-0.5">
                                                Placed on {selectedOrder.createdAt}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => setSelectedOrder(null)}
                                        className="p-2 rounded-xl text-muted hover:text-fg hover:bg-muted/10 transition-colors"
                                        aria-label="Close"
                                    >
                                        <X className="w-5 h-5" />
                                    </button>
                                </div>

                                {/* Modal Scrollable Body */}
                                <div className="p-5 sm:p-6 overflow-y-auto space-y-6">
                                    {/* Status Notice */}
                                    <div className="p-4 rounded-2xl bg-muted/10 border border-border flex items-center gap-3">
                                        <div className="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                            <Clock className="w-4 h-4" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-xs font-bold uppercase tracking-wider text-fg">
                                                Current Stage: {selectedOrder.status}
                                            </p>
                                            <p className="text-xs text-muted">
                                                {selectedOrder.statusLabel} • {selectedOrder.estimatedDelivery}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Items List */}
                                    <div>
                                        <h4 className="text-xs font-bold uppercase tracking-wider text-muted mb-3">
                                            Purchased Items ({selectedOrder.items.length})
                                        </h4>
                                        <div className="divide-y divide-border border border-border rounded-2xl overflow-hidden bg-bg">
                                            {selectedOrder.items.map((item) => (
                                                <div
                                                    key={item.id}
                                                    className="p-3.5 flex items-center gap-4"
                                                >
                                                    <Link
                                                        href={`/product/${item.productId}`}
                                                        className="w-14 h-14 rounded-xl bg-card border border-border p-1 shrink-0 flex items-center justify-center hover:border-accent transition-colors"
                                                    >
                                                        <img
                                                            src={item.image}
                                                            alt={item.name}
                                                            className="max-h-full max-w-full object-contain"
                                                        />
                                                    </Link>

                                                    <div className="flex-1 min-w-0">
                                                        <Link
                                                            href={`/product/${item.productId}`}
                                                            className="text-xs sm:text-sm font-semibold text-fg hover:text-accent transition-colors line-clamp-1"
                                                        >
                                                            {item.name}
                                                        </Link>
                                                        <div className="flex items-center gap-2 mt-1">
                                                            <span className="inline-flex items-center px-2 py-0.5 rounded-md bg-accent/10 border border-accent/20 text-accent font-bold text-[10px]">
                                                                Qty: {item.quantity}
                                                            </span>
                                                            <span className="text-[11px] text-muted">
                                                                @ {formatPrice(item.unitPrice)} each
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {/* Clean Line Price: Just the amount without "Line Total" */}
                                                    <div className="text-right shrink-0">
                                                        <span className="font-display font-black text-xs sm:text-sm text-fg block">
                                                            {formatPrice(item.totalPrice)}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Delivery & Payment Information */}
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div className="p-4 rounded-2xl bg-muted/5 border border-border space-y-1 text-xs">
                                            <span className="font-bold uppercase tracking-wider text-muted block mb-1">
                                                Shipping Address
                                            </span>
                                            <p className="font-semibold text-fg">
                                                {selectedOrder.shippingAddress.recipient}
                                            </p>
                                            <p className="text-muted">
                                                {selectedOrder.shippingAddress.address},{" "}
                                                {selectedOrder.shippingAddress.city}
                                            </p>
                                            <p className="text-muted flex items-center gap-1 pt-1">
                                                <Phone className="w-3 h-3 text-muted" />
                                                {selectedOrder.shippingAddress.phone}
                                            </p>
                                        </div>

                                        <div className="p-4 rounded-2xl bg-muted/5 border border-border space-y-1 text-xs">
                                            <span className="font-bold uppercase tracking-wider text-muted block mb-1">
                                                Payment Breakdown
                                            </span>
                                            <div className="flex justify-between text-muted">
                                                <span>Subtotal</span>
                                                <span className="text-fg font-medium">
                                                    {formatPrice(selectedOrder.subtotal)}
                                                </span>
                                            </div>
                                            <div className="flex justify-between text-muted">
                                                <span>Shipping</span>
                                                <span className="text-emerald-500 font-medium">
                                                    Islandwide Free
                                                </span>
                                            </div>
                                            <div className="flex justify-between text-fg font-bold pt-2 border-t border-border mt-1">
                                                <span>Total Amount</span>
                                                <span className="text-accent font-display font-black text-sm">
                                                    {formatPrice(selectedOrder.total)}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Modal Footer */}
                                <div className="p-4 bg-muted/10 border-t border-border flex items-center justify-between gap-3">
                                    <span className="text-xs text-muted">
                                        Payment Method: {selectedOrder.paymentMethod}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            toast.success(
                                                `Downloading receipt for Order #${selectedOrder.orderNumber}...`,
                                            );
                                        }}
                                        className="px-4 py-2 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-sm hover:opacity-90 transition-opacity cursor-pointer"
                                    >
                                        Download Receipt
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
