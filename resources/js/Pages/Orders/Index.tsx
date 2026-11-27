import React, { useState, useMemo } from "react";
import { Head, Link, router } from "@inertiajs/react";
import axios from "axios";
import AppLayout from "@/Layouts/AppLayout";
import { formatPrice } from "@/utils/currency";
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
    Download,
    RotateCcw,
    AlertTriangle,
    Upload,
    Landmark,
    Image as ImageIcon,
    XCircle,
    Loader2,
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
    returnableQuantity?: number;
    unitPrice: number;
    totalPrice: number;
}

export interface RefundRequestData {
    id: number;
    returnNumber: string;
    status: "pending" | "approved" | "rejected";
    refundAmount: number;
    reason: string;
    rejectionReason?: string | null;
    bankName?: string | null;
    bankBranch?: string | null;
    accountNumber?: string | null;
    accountName?: string | null;
    createdAt: string;
    refundedAt?: string | null;
    items: {
        productName: string;
        quantity: number;
        refundSubtotal: number;
        condition: string;
    }[];
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
    refundStatus?: "none" | "partial" | "full";
    refundedAmount?: number;
    canRequestRefund?: boolean;
    refundDeadline?: string | null;
    refundRequests?: RefundRequestData[];
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
    guestToken?: string | null;
}

const TABS: { key: string; label: string; icon?: any }[] = [
    { key: "All", label: "All" },
    { key: "Processing", label: "Processing", icon: Clock },
    { key: "Packaging", label: "Packaging", icon: Box },
    { key: "Shipping", label: "Shipping", icon: Truck },
    { key: "Delivered", label: "Delivered", icon: CheckCircle2 },
];

export default function OrdersIndex({ orders = [], guestToken = null }: OrdersPageProps) {
    return (
        <AppLayout>
            <OrdersContent orders={orders} guestToken={guestToken} />
        </AppLayout>
    );
}

function OrdersContent({ orders, guestToken = null }: OrdersPageProps) {
    const [activeTab, setActiveTab] = useState<string>("All");
    const [selectedOrder, setSelectedOrder] = useState<OrderData | null>(
        guestToken && orders.length === 1 ? orders[0] : null
    );

    // Refund Request Modal State
    const [refundOrder, setRefundOrder] = useState<OrderData | null>(null);
    const [refundQuantities, setRefundQuantities] = useState<Record<number, number>>({});
    const [refundReason, setRefundReason] = useState<string>("Damaged / Defective Product");
    const [refundNotes, setRefundNotes] = useState<string>("");
    const [bankName, setBankName] = useState<string>("");
    const [bankBranch, setBankBranch] = useState<string>("");
    const [accountNumber, setAccountNumber] = useState<string>("");
    const [accountName, setAccountName] = useState<string>("");
    const [paymentReceiptFile, setPaymentReceiptFile] = useState<File | null>(null);
    const [paymentReceiptPreview, setPaymentReceiptPreview] = useState<string | null>(null);
    const [evidenceImageFile, setEvidenceImageFile] = useState<File | null>(null);
    const [evidenceImagePreview, setEvidenceImagePreview] = useState<string | null>(null);
    const [refundErrors, setRefundErrors] = useState<Record<string, string>>({});
    const [isSubmittingRefund, setIsSubmittingRefund] = useState<boolean>(false);

    const openRefundModal = (order: OrderData) => {
        const initialQty: Record<number, number> = {};
        order.items.forEach((item) => {
            const retQty = item.returnableQuantity ?? item.quantity;
            if (retQty > 0) {
                initialQty[item.id] = retQty;
            }
        });
        setRefundOrder(order);
        setRefundQuantities(initialQty);
        setRefundReason("Damaged / Defective Product");
        setRefundNotes("");
        setBankName("");
        setBankBranch("");
        setAccountNumber("");
        setAccountName("");
        setPaymentReceiptFile(null);
        setPaymentReceiptPreview(null);
        setEvidenceImageFile(null);
        setEvidenceImagePreview(null);
        setRefundErrors({});
    };

    const closeRefundModal = () => {
        if (isSubmittingRefund) return;
        setRefundOrder(null);
    };

    const handleImageSelect = (
        e: React.ChangeEvent<HTMLInputElement>,
        type: "payment_receipt" | "evidence_image"
    ) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const maxBytes = 1024 * 1024; // 1 MB strict limit
        const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "image/webp"];

        if (!allowedTypes.includes(file.type)) {
            const msg = "Only JPG, PNG, or WEBP image files are allowed.";
            setRefundErrors((prev) => ({ ...prev, [type]: msg }));
            toast.error(msg);
            e.target.value = "";
            return;
        }

        if (file.size > maxBytes) {
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const msg = `Image size (${sizeMb} MB) exceeds the 1 MB limit. Please upload an image under 1 MB.`;
            setRefundErrors((prev) => ({ ...prev, [type]: msg }));
            toast.error(msg);
            e.target.value = "";
            return;
        }

        setRefundErrors((prev) => {
            const copy = { ...prev };
            delete copy[type];
            return copy;
        });

        const previewUrl = URL.createObjectURL(file);
        if (type === "payment_receipt") {
            setPaymentReceiptFile(file);
            setPaymentReceiptPreview(previewUrl);
        } else {
            setEvidenceImageFile(file);
            setEvidenceImagePreview(previewUrl);
        }
    };

    const calculatedRefundTotal = useMemo(() => {
        if (!refundOrder) return 0;
        return refundOrder.items.reduce((sum, item) => {
            const qty = refundQuantities[item.id] ?? 0;
            return sum + qty * item.unitPrice;
        }, 0);
    }, [refundOrder, refundQuantities]);

    const handleSubmitRefund = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!refundOrder || isSubmittingRefund) return;

        const errors: Record<string, string> = {};
        const selectedItems = refundOrder.items
            .map((item) => ({
                order_item_id: item.id,
                quantity: refundQuantities[item.id] ?? 0,
            }))
            .filter((row) => row.quantity > 0);

        if (selectedItems.length === 0) {
            errors.items = "Please select at least one item with quantity greater than 0.";
        }
        if (!refundReason.trim()) errors.reason = "Please specify a return reason.";
        if (!bankName.trim()) errors.bank_name = "Bank name is required.";
        if (!bankBranch.trim()) errors.bank_branch = "Bank branch is required.";
        if (!accountNumber.trim()) errors.account_number = "Account number is required.";
        if (!accountName.trim()) errors.account_name = "Account holder name is required.";
        if (!paymentReceiptFile) errors.payment_receipt = "Payment receipt image (< 1 MB) is required.";
        if (!evidenceImageFile) errors.evidence_image = "Evidence image for return (< 1 MB) is required.";

        if (Object.keys(errors).length > 0) {
            setRefundErrors(errors);
            toast.error("Please fill all required fields and upload both images under 1 MB.");
            return;
        }

        const formData = new FormData();
        selectedItems.forEach((item, idx) => {
            formData.append(`items[${idx}][order_item_id]`, String(item.order_item_id));
            formData.append(`items[${idx}][quantity]`, String(item.quantity));
        });
        formData.append("reason", refundReason.trim());
        formData.append("notes", refundNotes.trim());
        formData.append("bank_name", bankName.trim());
        formData.append("bank_branch", bankBranch.trim());
        formData.append("account_number", accountNumber.trim());
        formData.append("account_name", accountName.trim());
        if (paymentReceiptFile) formData.append("payment_receipt", paymentReceiptFile);
        if (evidenceImageFile) formData.append("evidence_image", evidenceImageFile);

        setIsSubmittingRefund(true);
        setRefundErrors({});

        try {
            const refundUrl = guestToken
                ? `/guest/orders/${guestToken}/refund-request`
                : `/orders/${refundOrder.id}/refund-request`;

            const res = await axios.post(refundUrl, formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            toast.success(res.data?.message || "Refund request submitted and pending review.");
            setRefundOrder(null);
            router.reload({ only: ["orders"] });
        } catch (err: any) {
            const serverErrors = err?.response?.data?.errors;
            const message = err?.response?.data?.message || "Failed to submit refund request. Please check your inputs.";
            if (serverErrors) {
                const flat: Record<string, string> = {};
                Object.keys(serverErrors).forEach((key) => {
                    flat[key] = Array.isArray(serverErrors[key]) ? serverErrors[key][0] : serverErrors[key];
                });
                setRefundErrors(flat);
            }
            toast.error(message);
        } finally {
            setIsSubmittingRefund(false);
        }
    };

    const getRefundStatusBadge = (status: "pending" | "approved" | "rejected") => {
        switch (status) {
            case "pending":
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-500 border border-amber-500/30">
                        <Clock className="w-3 h-3" />
                        Refund Pending
                    </span>
                );
            case "approved":
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
                        <CheckCircle2 className="w-3 h-3" />
                        Refund Approved
                    </span>
                );
            case "rejected":
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-rose-500/15 text-rose-500 border border-rose-500/30">
                        <XCircle className="w-3 h-3" />
                        Refund Rejected
                    </span>
                );
        }
    };

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
            <Head title="My Orders" />

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

                                            {/* Status Badges */}
                                            <div className="flex flex-wrap items-center gap-2">
                                                {order.refundRequests && order.refundRequests.length > 0 && (
                                                    getRefundStatusBadge(order.refundRequests[0].status)
                                                )}
                                                {getStatusBadge(order.status)}
                                            </div>
                                        </div>

                                        {/* Rejected Refund Notice Banner */}
                                        {order.refundRequests && order.refundRequests.some((r) => r.status === "rejected") && (
                                            <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-start gap-2.5 text-xs text-rose-400">
                                                <AlertTriangle className="w-4 h-4 shrink-0 mt-0.5 text-rose-500" />
                                                <div>
                                                    <span className="font-bold text-rose-500 block">
                                                        Refund Request Rejected
                                                    </span>
                                                    <span>
                                                        For more information, please call us at{" "}
                                                        <a href="tel:+94771234567" className="underline font-bold text-fg hover:text-accent">
                                                            +94 77 123 4567
                                                        </a>
                                                        .
                                                    </span>
                                                </div>
                                            </div>
                                        )}

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

                                                <div className="flex flex-wrap items-center gap-2.5">
                                                    {order.canRequestRefund && (
                                                        <button
                                                            type="button"
                                                            onClick={() => openRefundModal(order)}
                                                            className="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-500/15 border border-amber-500/40 text-amber-500 font-bold text-xs uppercase tracking-wider hover:bg-amber-500/25 active:scale-95 transition-all cursor-pointer whitespace-nowrap"
                                                        >
                                                            <RotateCcw className="w-4 h-4" />
                                                            <span>Refund</span>
                                                        </button>
                                                    )}

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

                                    {/* Refund Requests Status History */}
                                    {selectedOrder.refundRequests && selectedOrder.refundRequests.length > 0 && (
                                        <div className="space-y-3 pt-2 border-t border-border">
                                            <h4 className="text-xs font-bold uppercase tracking-wider text-muted">
                                                Refund & Return Requests
                                            </h4>
                                            {selectedOrder.refundRequests.map((req) => (
                                                <div
                                                    key={req.id}
                                                    className="p-4 rounded-xl bg-bg/60 border border-border space-y-2.5 text-xs"
                                                >
                                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                                        <span className="font-bold text-fg">
                                                            {req.returnNumber} — {formatPrice(req.refundAmount)}
                                                        </span>
                                                        {getRefundStatusBadge(req.status)}
                                                    </div>
                                                    <div className="text-muted">
                                                        Reason: <span className="text-fg">{req.reason}</span>
                                                    </div>
                                                    {req.bankName && (
                                                        <div className="text-muted">
                                                            Bank: <span className="text-fg">{req.bankName} ({req.bankBranch})</span> — Acc: <span className="text-fg font-mono">{req.accountNumber}</span> ({req.accountName})
                                                        </div>
                                                    )}
                                                    {req.status === "rejected" && (
                                                        <div className="p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400">
                                                            <strong className="text-rose-500 block">
                                                                For more information, please call us at +94 77 123 4567
                                                            </strong>
                                                            {req.rejectionReason && (
                                                                <span className="block mt-1">Note: {req.rejectionReason}</span>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                {/* Modal Footer */}
                                <div className="p-4 bg-muted/10 border-t border-border flex flex-wrap items-center justify-between gap-3">
                                    {selectedOrder.canRequestRefund ? (
                                        <div className="flex flex-col gap-1">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    const ord = selectedOrder;
                                                    setSelectedOrder(null);
                                                    openRefundModal(ord);
                                                }}
                                                className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500/15 border border-amber-500/40 text-amber-500 font-bold text-xs uppercase tracking-wider hover:bg-amber-500/25 transition-colors cursor-pointer self-start"
                                            >
                                                <RotateCcw className="w-4 h-4" />
                                                <span>Request Refund</span>
                                            </button>
                                            {selectedOrder.refundDeadline && (
                                                <span className="text-[11px] text-muted">
                                                    7-day return window until {selectedOrder.refundDeadline}
                                                </span>
                                            )}
                                        </div>
                                    ) : <div />}

                                    <div className="flex flex-wrap items-center gap-2.5">
                                        <a
                                            href={
                                                guestToken
                                                    ? `/guest/orders/${guestToken}/receipt?preview=1`
                                                    : `/orders/${selectedOrder.id}/receipt?preview=1`
                                            }
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-card border border-border text-fg font-bold text-xs uppercase tracking-wider hover:border-accent hover:text-accent transition-colors cursor-pointer"
                                        >
                                            <Eye className="w-4 h-4" />
                                            <span>View Receipt</span>
                                        </a>
                                        <a
                                            href={
                                                guestToken
                                                    ? `/guest/orders/${guestToken}/receipt`
                                                    : `/orders/${selectedOrder.id}/receipt`
                                            }
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onClick={() => {
                                                toast.success(
                                                    `Downloading receipt for Order #${selectedOrder.orderNumber}...`,
                                                );
                                            }}
                                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-sm hover:opacity-90 transition-opacity cursor-pointer"
                                        >
                                            <Download className="w-4 h-4" />
                                            <span>Download Receipt</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Customer Refund Request Modal */}
                    {refundOrder && (
                        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                            <div
                                className="fixed inset-0 bg-black/75 backdrop-blur-xs"
                                onClick={closeRefundModal}
                            />

                            <div className="relative w-full max-w-2xl bg-card border border-border rounded-3xl shadow-2xl overflow-hidden z-10 flex flex-col max-h-[92vh]">
                                {/* Header */}
                                <div className="p-5 sm:p-6 bg-gradient-to-r from-card to-amber-500/5 border-b border-border flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-500 border border-amber-500/30 flex items-center justify-center">
                                            <RotateCcw className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h3 className="font-display font-black text-lg text-fg">
                                                Request Refund — #{refundOrder.orderNumber}
                                            </h3>
                                            <p className="text-xs text-muted">
                                                Submit your return details, bank info, and proof images (each under 1 MB).
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={closeRefundModal}
                                        className="w-8 h-8 rounded-full bg-bg border border-border flex items-center justify-center text-muted hover:text-fg cursor-pointer"
                                    >
                                        <X className="w-4 h-4" />
                                    </button>
                                </div>

                                {/* Form Body */}
                                <form onSubmit={handleSubmitRefund} className="overflow-y-auto flex-1 p-5 sm:p-6 space-y-6">
                                    {/* 1. Select Items to Return */}
                                    <div className="space-y-3">
                                        <h4 className="text-xs font-bold uppercase tracking-wider text-muted">
                                            1. Select Items & Quantities to Return
                                        </h4>
                                        {refundErrors.items && (
                                            <p className="text-xs text-rose-500 font-medium">{refundErrors.items}</p>
                                        )}
                                        <div className="space-y-2.5">
                                            {refundOrder.items
                                                .filter((it) => (it.returnableQuantity ?? it.quantity) > 0)
                                                .map((item) => {
                                                    const maxQty = item.returnableQuantity ?? item.quantity;
                                                    const currentQty = refundQuantities[item.id] ?? 0;

                                                    return (
                                                        <div
                                                            key={item.id}
                                                            className="flex items-center justify-between gap-3 p-3 rounded-xl bg-bg/60 border border-border"
                                                        >
                                                            <div className="flex items-center gap-3 min-w-0 flex-1">
                                                                <img
                                                                    src={item.image || "/images/placeholder.png"}
                                                                    alt={item.name}
                                                                    className="w-11 h-11 rounded-lg object-contain bg-card border border-border p-1 shrink-0"
                                                                />
                                                                <div className="min-w-0">
                                                                    <p className="text-xs font-bold text-fg truncate">
                                                                        {item.name}
                                                                    </p>
                                                                    <p className="text-[11px] text-muted">
                                                                        {formatPrice(item.unitPrice)} each (Max: {maxQty})
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div className="flex items-center gap-2 shrink-0">
                                                                <label className="text-[11px] text-muted font-medium">Qty:</label>
                                                                <input
                                                                    type="number"
                                                                    min={0}
                                                                    max={maxQty}
                                                                    value={currentQty}
                                                                    onChange={(e) => {
                                                                        const val = Math.max(0, Math.min(maxQty, parseInt(e.target.value || "0", 10)));
                                                                        setRefundQuantities((prev) => ({ ...prev, [item.id]: val }));
                                                                    }}
                                                                    className="w-16 px-2.5 py-1.5 rounded-lg bg-card border border-border text-fg text-xs font-bold text-center focus:outline-none focus:border-accent"
                                                                />
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                        </div>
                                        <div className="flex justify-between items-center p-3 rounded-xl bg-accent/10 border border-accent/25 text-xs">
                                            <span className="font-bold text-fg">Estimated Refund Amount:</span>
                                            <span className="font-display font-black text-base text-accent">
                                                {formatPrice(calculatedRefundTotal)}
                                            </span>
                                        </div>
                                    </div>

                                    {/* 2. Reason & Notes */}
                                    <div className="space-y-3">
                                        <h4 className="text-xs font-bold uppercase tracking-wider text-muted">
                                            2. Return Reason
                                        </h4>
                                        <div>
                                            <select
                                                value={refundReason}
                                                onChange={(e) => setRefundReason(e.target.value)}
                                                className="w-full px-3.5 py-2.5 rounded-xl bg-bg border border-border text-fg text-xs font-medium focus:outline-none focus:border-accent"
                                            >
                                                <option value="Damaged / Defective Product">Damaged / Defective Product</option>
                                                <option value="Dead on Arrival (Not Working)">Dead on Arrival (Not Working)</option>
                                                <option value="Wrong Item Received">Wrong Item Received</option>
                                                <option value="Missing Parts / Accessories">Missing Parts / Accessories</option>
                                                <option value="Other Issue">Other Issue</option>
                                            </select>
                                            {refundErrors.reason && (
                                                <p className="text-xs text-rose-500 mt-1">{refundErrors.reason}</p>
                                            )}
                                        </div>
                                        <div>
                                            <textarea
                                                rows={2}
                                                value={refundNotes}
                                                onChange={(e) => setRefundNotes(e.target.value)}
                                                placeholder="Describe the issue briefly (optional)..."
                                                className="w-full px-3.5 py-2.5 rounded-xl bg-bg border border-border text-fg text-xs focus:outline-none focus:border-accent"
                                            />
                                        </div>
                                    </div>

                                    {/* 3. Refund Bank Details */}
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-2">
                                            <Landmark className="w-4 h-4 text-accent" />
                                            <h4 className="text-xs font-bold uppercase tracking-wider text-muted">
                                                3. Refund Bank Details
                                            </h4>
                                        </div>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label className="block text-[11px] font-bold text-muted mb-1">
                                                    Bank Name *
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={bankName}
                                                    onChange={(e) => setBankName(e.target.value)}
                                                    placeholder="e.g. Commercial Bank"
                                                    className="w-full px-3.5 py-2 rounded-xl bg-bg border border-border text-fg text-xs focus:outline-none focus:border-accent"
                                                />
                                                {refundErrors.bank_name && (
                                                    <p className="text-[11px] text-rose-500 mt-1">{refundErrors.bank_name}</p>
                                                )}
                                            </div>
                                            <div>
                                                <label className="block text-[11px] font-bold text-muted mb-1">
                                                    Branch *
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={bankBranch}
                                                    onChange={(e) => setBankBranch(e.target.value)}
                                                    placeholder="e.g. Colombo 03"
                                                    className="w-full px-3.5 py-2 rounded-xl bg-bg border border-border text-fg text-xs focus:outline-none focus:border-accent"
                                                />
                                                {refundErrors.bank_branch && (
                                                    <p className="text-[11px] text-rose-500 mt-1">{refundErrors.bank_branch}</p>
                                                )}
                                            </div>
                                            <div>
                                                <label className="block text-[11px] font-bold text-muted mb-1">
                                                    Account Number *
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={accountNumber}
                                                    onChange={(e) => setAccountNumber(e.target.value)}
                                                    placeholder="e.g. 8001234567"
                                                    className="w-full px-3.5 py-2 rounded-xl bg-bg border border-border text-fg text-xs focus:outline-none focus:border-accent"
                                                />
                                                {refundErrors.account_number && (
                                                    <p className="text-[11px] text-rose-500 mt-1">{refundErrors.account_number}</p>
                                                )}
                                            </div>
                                            <div>
                                                <label className="block text-[11px] font-bold text-muted mb-1">
                                                    Account Holder Name *
                                                </label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={accountName}
                                                    onChange={(e) => setAccountName(e.target.value)}
                                                    placeholder="e.g. K. Perera"
                                                    className="w-full px-3.5 py-2 rounded-xl bg-bg border border-border text-fg text-xs focus:outline-none focus:border-accent"
                                                />
                                                {refundErrors.account_name && (
                                                    <p className="text-[11px] text-rose-500 mt-1">{refundErrors.account_name}</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* 4. Required Attachments (< 1 MB each) */}
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-2">
                                            <ImageIcon className="w-4 h-4 text-accent" />
                                            <h4 className="text-xs font-bold uppercase tracking-wider text-muted">
                                                4. Required Attachments (Max 1 MB each — JPG, PNG, WEBP)
                                            </h4>
                                        </div>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            {/* Payment Receipt */}
                                            <div className="p-3.5 rounded-xl bg-bg border border-dashed border-border space-y-2">
                                                <label className="block text-xs font-bold text-fg">
                                                    1. Payment Receipt *
                                                </label>
                                                <input
                                                    type="file"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    onChange={(e) => handleImageSelect(e, "payment_receipt")}
                                                    className="block w-full text-[11px] text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-accent/15 file:text-accent hover:file:bg-accent/25 cursor-pointer"
                                                />
                                                {paymentReceiptPreview && (
                                                    <div className="mt-2 flex items-center gap-2">
                                                        <img
                                                            src={paymentReceiptPreview}
                                                            alt="Receipt Preview"
                                                            className="w-12 h-12 rounded-lg object-cover border border-border"
                                                        />
                                                        <span className="text-[11px] text-emerald-500 font-medium truncate">
                                                            {paymentReceiptFile?.name} ({Math.round((paymentReceiptFile?.size || 0) / 1024)} KB)
                                                        </span>
                                                    </div>
                                                )}
                                                {refundErrors.payment_receipt && (
                                                    <p className="text-[11px] text-rose-500 font-medium">{refundErrors.payment_receipt}</p>
                                                )}
                                            </div>

                                            {/* Evidence Image */}
                                            <div className="p-3.5 rounded-xl bg-bg border border-dashed border-border space-y-2">
                                                <label className="block text-xs font-bold text-fg">
                                                    2. Evidence Image for Return *
                                                </label>
                                                <input
                                                    type="file"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    onChange={(e) => handleImageSelect(e, "evidence_image")}
                                                    className="block w-full text-[11px] text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-accent/15 file:text-accent hover:file:bg-accent/25 cursor-pointer"
                                                />
                                                {evidenceImagePreview && (
                                                    <div className="mt-2 flex items-center gap-2">
                                                        <img
                                                            src={evidenceImagePreview}
                                                            alt="Evidence Preview"
                                                            className="w-12 h-12 rounded-lg object-cover border border-border"
                                                        />
                                                        <span className="text-[11px] text-emerald-500 font-medium truncate">
                                                            {evidenceImageFile?.name} ({Math.round((evidenceImageFile?.size || 0) / 1024)} KB)
                                                        </span>
                                                    </div>
                                                )}
                                                {refundErrors.evidence_image && (
                                                    <p className="text-[11px] text-rose-500 font-medium">{refundErrors.evidence_image}</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Footer Submit */}
                                    <div className="pt-4 border-t border-border flex items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            onClick={closeRefundModal}
                                            disabled={isSubmittingRefund}
                                            className="px-4 py-2.5 rounded-xl border border-border text-xs font-bold text-muted hover:text-fg cursor-pointer"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={isSubmittingRefund}
                                            className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-95 disabled:opacity-50 cursor-pointer"
                                        >
                                            {isSubmittingRefund ? (
                                                <>
                                                    <Loader2 className="w-4 h-4 animate-spin" />
                                                    <span>Submitting...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Upload className="w-4 h-4" />
                                                    <span>Submit Refund Request</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
