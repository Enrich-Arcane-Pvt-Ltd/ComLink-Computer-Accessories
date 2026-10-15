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
    CreditCard,
    Check,
    ShoppingBag,
    ArrowRight,
    Search,
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
                                View your purchase history, payment status, and track package stages.
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
                    <div className="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-border/80 scrollbar-none">
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

                    {/* Orders Table Container */}
                    <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                {/* Table Head */}
                                <thead>
                                    <tr className="border-b border-border bg-muted/10 text-xs font-bold uppercase tracking-wider text-muted">
                                        <th scope="col" className="py-4 px-6">
                                            Order
                                        </th>
                                        <th scope="col" className="py-4 px-6">
                                            Date
                                        </th>
                                        {activeTab === "All" && (
                                            <th scope="col" className="py-4 px-6">
                                                Status
                                            </th>
                                        )}
                                        <th scope="col" className="py-4 px-6">
                                            Total
                                        </th>
                                        <th scope="col" className="py-4 px-6 text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                {/* Table Body */}
                                <tbody className="divide-y divide-border">
                                    {filteredOrders.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={activeTab === "All" ? 5 : 4}
                                                className="py-16 text-center text-muted"
                                            >
                                                <div className="flex flex-col items-center justify-center">
                                                    <div className="w-14 h-14 rounded-full bg-muted/10 flex items-center justify-center mb-3 text-muted">
                                                        <Package className="w-7 h-7" />
                                                    </div>
                                                    <p className="text-base font-bold text-fg">
                                                        No {activeTab === "All" ? "" : activeTab} orders found
                                                    </p>
                                                    <p className="text-xs text-muted max-w-sm mt-1">
                                                        You haven't placed any orders under this category yet.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredOrders.map((order) => (
                                            <tr
                                                key={order.id}
                                                className="hover:bg-muted/5 transition-colors group"
                                            >
                                                {/* 1. Order Column */}
                                                <td className="py-4 px-6">
                                                    <div className="flex items-center gap-3.5">
                                                        {/* First product image */}
                                                        <div className="w-12 h-12 rounded-xl bg-card border border-border/80 p-1.5 overflow-hidden shadow-xs shrink-0 flex items-center justify-center">
                                                            <img
                                                                src={order.items[0]?.image || "/images/products/sample-keyboard.jpg"}
                                                                alt={order.items[0]?.name || "Product"}
                                                                className="max-h-full max-w-full object-contain"
                                                            />
                                                        </div>

                                                        <div>
                                                            <span className="font-display font-black text-sm text-fg tracking-tight block">
                                                                #{order.orderNumber}
                                                            </span>
                                                            <span className="text-xs text-muted">
                                                                {order.items.reduce((sum, it) => sum + (it.quantity || 1), 0)} units ({order.items.length} {order.items.length === 1 ? 'product' : 'products'})
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* 2. Date Column */}
                                                <td className="py-4 px-6 text-xs text-muted whitespace-nowrap">
                                                    <div className="flex items-center gap-1.5">
                                                        <Calendar className="w-3.5 h-3.5 opacity-60" />
                                                        <span className="font-medium text-fg">
                                                            {order.createdAt}
                                                        </span>
                                                    </div>
                                                </td>

                                                {/* 3. Status Column (Shown only in "All" tab) */}
                                                {activeTab === "All" && (
                                                    <td className="py-4 px-6 whitespace-nowrap">
                                                        {getStatusBadge(order.status)}
                                                    </td>
                                                )}

                                                {/* 4. Total Column */}
                                                <td className="py-4 px-6 whitespace-nowrap">
                                                    <span className="font-display font-black text-sm text-accent">
                                                        {formatPrice(order.total)}
                                                    </span>
                                                    <span className="block text-[11px] text-muted">
                                                        {order.paymentStatus} via {order.paymentMethod.split("(")[0]}
                                                    </span>
                                                </td>

                                                {/* 5. Action Column: View My Order Button */}
                                                <td className="py-4 px-6 text-right whitespace-nowrap">
                                                    <button
                                                        type="button"
                                                        onClick={() => setSelectedOrder(order)}
                                                        className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-bg border border-border text-xs font-bold uppercase tracking-wider text-fg hover:border-accent hover:text-accent transition-all duration-200 shadow-2xs hover:shadow-sm active:scale-95 cursor-pointer"
                                                    >
                                                        <Eye className="w-3.5 h-3.5 text-accent" />
                                                        <span>View My Order</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Order Details Modal Drawer (When user clicks "View My Order") */}
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
                                <div className="p-6 bg-gradient-to-r from-card to-muted/10 border-b border-border flex items-center justify-between">
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
                                <div className="p-6 overflow-y-auto space-y-6">
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

                                                    <div className="text-right shrink-0">
                                                        <span className="font-display font-black text-xs sm:text-sm text-fg block">
                                                            {formatPrice(item.totalPrice)}
                                                        </span>
                                                        <span className="text-[10px] text-muted">
                                                            Line Total
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
                                        className="px-4 py-2 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-sm hover:opacity-90 transition-opacity"
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
