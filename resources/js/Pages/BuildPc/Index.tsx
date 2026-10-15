import AppLayout from "@/Layouts/AppLayout";
import { useCart } from "@/contexts/CartContext";
import { cn } from "@/utils/cn";
import { Head, Link } from "@inertiajs/react";
import {
    AlertTriangle,
    Armchair,
    ArrowRight,
    Box,
    Camera,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    CircuitBoard,
    Copy,
    Cpu,
    Database,
    Disc,
    Download,
    Eye,
    Fan,
    FileText,
    HardDrive,
    Headphones,
    HelpCircle,
    Info,
    Keyboard,
    Layers,
    LayoutGrid,
    Lightbulb,
    Lock,
    Monitor,
    MonitorPlay,
    Mouse,
    Plus,
    RefreshCw,
    Search,
    Share2,
    ShieldAlert,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    SlidersHorizontal,
    Sparkles,
    Trash2,
    Volume2,
    Wind,
    X,
    Zap,
} from "lucide-react";
import React, { useMemo, useState } from "react";
import { toast } from "sonner";

/* -------------------------------------------------------------------------- */
/*  Interfaces                                                                */
/* -------------------------------------------------------------------------- */

export interface BuilderProduct {
    id: number;
    name: string;
    slot: string;
    category: string;
    subCategory: string;
    brand: string;
    chipset?: "AMD" | "Intel" | string;
    socket?: string;
    wattage?: number;
    price: number;
    originalPrice?: number;
    image: string;
    inStock: boolean;
    specs?: Record<string, string>;
}

export interface SlotDefinition {
    id: string;
    name: string;
    shortName: string;
    icon: React.ElementType;
    description: string;
    badge?: string;
}

export interface PreBuiltTemplate {
    id: string;
    name: string;
    subtitle: string;
    badge: string;
    badgeColor: string;
    targetChipset: "AMD" | "Intel" | string;
    totalWattage: number;
    estimatedPrice: number;
    parts: Record<string, number>;
}

interface BuildPcPageProps {
    categories: any[];
    products: BuilderProduct[];
    templates: PreBuiltTemplate[];
}

/* -------------------------------------------------------------------------- */
/*  Slot Configurations matching reference & CategorySeeder                    */
/* -------------------------------------------------------------------------- */

const ESSENTIALS_SLOTS: SlotDefinition[] = [
    {
        id: "processors",
        name: "PROCESSORS",
        shortName: "CPU",
        icon: Cpu,
        description: "Core central processor (AMD / Intel)",
    },
    {
        id: "motherboards",
        name: "MOTHERBOARDS",
        shortName: "Motherboard",
        icon: CircuitBoard,
        description: "Mainboard with matching CPU socket",
    },
    {
        id: "memory",
        name: "MEMORY",
        shortName: "RAM",
        icon: Layers,
        description: "High speed DDR5 / DDR4 system memory",
    },
    {
        id: "graphic-cards",
        name: "GRAPHICS CARDS",
        shortName: "GPU",
        icon: MonitorPlay,
        description: "Dedicated gaming & rendering graphics card",
    },
    {
        id: "ssd",
        name: "SSD & NVME",
        shortName: "Primary SSD",
        icon: HardDrive,
        description: "Ultra-fast M.2 NVMe operating system drive",
    },
    {
        id: "hard-disk",
        name: "HARD DISK",
        shortName: "HDD",
        icon: Database,
        description: "High capacity secondary bulk storage",
    },
    {
        id: "power-supply",
        name: "POWER SUPPLY",
        shortName: "PSU",
        icon: Zap,
        description: "80 Plus Certified power delivery unit",
    },
    {
        id: "coolers",
        name: "COOLERS",
        shortName: "CPU Cooler",
        icon: Fan,
        description: "Liquid AIO cooler or dual tower air cooler",
    },
    {
        id: "pc-cases",
        name: "PC CASE",
        shortName: "Chassis",
        icon: Box,
        description: "High-airflow tempered glass computer case",
    },
    {
        id: "fans",
        name: "FANS & THERMAL",
        shortName: "Fans / Paste",
        icon: Wind,
        description: "Thermal compound and case cooling fans",
    },
    {
        id: "extra-ssd",
        name: "EXTRA SSD & NVME",
        shortName: "Secondary SSD",
        icon: Disc,
        description: "Additional gaming and project storage",
    },
];

const ACCESSORIES_SLOTS: SlotDefinition[] = [
    {
        id: "monitors-displays",
        name: "MONITORS & DISPLAYS",
        shortName: "Monitor",
        icon: Monitor,
        description: "High refresh rate gaming & creator displays",
    },
    {
        id: "keyboards",
        name: "KEYBOARDS",
        shortName: "Keyboard",
        icon: Keyboard,
        description: "Mechanical RGB gaming keyboards",
    },
    {
        id: "mouse",
        name: "MOUSE",
        shortName: "Mouse",
        icon: Mouse,
        description: "High-precision optical gaming mice",
    },
    {
        id: "headsets",
        name: "HEADSETS",
        shortName: "Headset",
        icon: Headphones,
        description: "Spatial 7.1 audio gaming headsets",
    },
    {
        id: "ups",
        name: "UPS & POWER",
        shortName: "UPS Backup",
        icon: ShieldAlert,
        description: "Battery backup surge protection unit",
    },
];

/* -------------------------------------------------------------------------- */
/*  Helpers                                                                   */
/* -------------------------------------------------------------------------- */

const formatLkr = (num: number) => {
    return `Rs. ${num.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

/* -------------------------------------------------------------------------- */
/*  Main Page Component                                                       */
/* -------------------------------------------------------------------------- */

export default function BuildPc(props: BuildPcPageProps) {
    return (
        <AppLayout>
            <BuildPcContent {...props} />
        </AppLayout>
    );
}

function BuildPcContent({
    categories = [],
    products = [],
    templates = [],
}: BuildPcPageProps) {
    const { addToCart } = useCart();

    // Active Category Slot selection
    const [activeTab, setActiveTab] = useState<"essentials" | "accessories">(
        "essentials",
    );
    const [activeSlotId, setActiveSlotId] = useState<string>("processors");

    // Chipset Filter (All | AMD | Intel)
    const [chipsetFilter, setChipsetFilter] = useState<"ALL" | "AMD" | "Intel">(
        "ALL",
    );

    // Auto-advance toggle (matches competitor reference!)
    const [autoAdvance, setAutoAdvance] = useState<boolean>(true);

    // Selected components state: slotId -> BuilderProduct
    const [selectedParts, setSelectedParts] = useState<
        Record<string, BuilderProduct>
    >({});

    // Filters for products in active category
    const [searchQuery, setSearchQuery] = useState<string>("");
    const [selectedBrand, setSelectedBrand] = useState<string>("ALL");
    const [sortBy, setSortBy] = useState<
        "featured" | "price_asc" | "price_desc"
    >("featured");

    // Modals
    const [isTemplatesModalOpen, setIsTemplatesModalOpen] =
        useState<boolean>(false);
    const [isExportModalOpen, setIsExportModalOpen] = useState<boolean>(false);
    const [isSubmittingCart, setIsSubmittingCart] = useState<boolean>(false);

    // Current slots list based on active tab
    const currentSlots =
        activeTab === "essentials" ? ESSENTIALS_SLOTS : ACCESSORIES_SLOTS;

    // Active slot definition
    const activeSlotDef = useMemo(() => {
        return (
            [...ESSENTIALS_SLOTS, ...ACCESSORIES_SLOTS].find(
                (s) => s.id === activeSlotId,
            ) || ESSENTIALS_SLOTS[0]
        );
    }, [activeSlotId]);

    // Available products for the currently active slot
    const slotProducts = useMemo(() => {
        return products.filter((p) => {
            // Match slot
            if (p.slot !== activeSlotId) return false;

            // Apply Chipset filter for CPU and Motherboard
            if (
                (activeSlotId === "processors" ||
                    activeSlotId === "motherboards") &&
                chipsetFilter !== "ALL"
            ) {
                if (p.chipset && p.chipset !== chipsetFilter) {
                    return false;
                }
            }

            // Apply Brand filter
            if (selectedBrand !== "ALL" && p.brand !== selectedBrand) {
                return false;
            }

            // Apply Search Query
            if (searchQuery.trim()) {
                const q = searchQuery.toLowerCase();
                const matchName = p.name.toLowerCase().includes(q);
                const matchBrand = p.brand.toLowerCase().includes(q);
                const matchSpecs = Object.values(p.specs || {}).some((val) =>
                    val.toLowerCase().includes(q),
                );
                if (!matchName && !matchBrand && !matchSpecs) return false;
            }

            return true;
        });
    }, [products, activeSlotId, chipsetFilter, selectedBrand, searchQuery]);

    // Available brands in the active slot
    const availableBrands = useMemo(() => {
        const brandsSet = new Set<string>();
        products
            .filter((p) => p.slot === activeSlotId)
            .forEach((p) => {
                if (p.brand) brandsSet.add(p.brand);
            });
        return Array.from(brandsSet);
    }, [products, activeSlotId]);

    // Sorted products for active slot
    const sortedSlotProducts = useMemo(() => {
        const items = [...slotProducts];
        if (sortBy === "price_asc") {
            items.sort((a, b) => a.price - b.price);
        } else if (sortBy === "price_desc") {
            items.sort((a, b) => b.price - a.price);
        }
        return items;
    }, [slotProducts, sortBy]);

    // Calculations
    const selectedCount = Object.keys(selectedParts).length;

    const totalCost = useMemo(() => {
        return Object.values(selectedParts).reduce(
            (acc, curr) => acc + (curr.price || 0),
            0,
        );
    }, [selectedParts]);

    const totalWattage = useMemo(() => {
        return Object.values(selectedParts).reduce(
            (acc, curr) => acc + (curr.wattage || 0),
            0,
        );
    }, [selectedParts]);

    const psuProduct = selectedParts["power-supply"];
    const psuWattage = psuProduct?.wattage || 0;

    // Compatibility Checker Logic
    const compatibilityCheck = useMemo(() => {
        if (selectedCount === 0) {
            return {
                status: "--",
                isOk: true,
                message: "Select parts to evaluate compatibility",
                color: "text-muted",
            };
        }

        const cpu = selectedParts["processors"];
        const mobo = selectedParts["motherboards"];

        // Check Socket Compatibility
        if (cpu && mobo) {
            if (cpu.socket && mobo.socket && cpu.socket !== mobo.socket) {
                return {
                    status: "Socket Mismatch",
                    isOk: false,
                    message: `CPU requires ${cpu.socket} socket, but Motherboard has ${mobo.socket}`,
                    color: "text-rose-500",
                };
            }
        }

        // Check PSU Wattage Headroom
        if (psuProduct && totalWattage > 0) {
            const recommended = Math.round(totalWattage * 1.25);
            if (psuWattage < totalWattage) {
                return {
                    status: "Low PSU Wattage",
                    isOk: false,
                    message: `System draws ~${totalWattage}W. PSU delivers only ${psuWattage}W. Upgrade PSU to at least ${recommended}W.`,
                    color: "text-rose-500",
                };
            }
        }

        return {
            status: "Compatible",
            isOk: true,
            message: "All selected components are verified compatible!",
            color: "text-emerald-500",
        };
    }, [selectedParts, selectedCount, psuProduct, psuWattage, totalWattage]);

    // Handler to select a product into the active slot
    const handleSelectProduct = (product: BuilderProduct) => {
        setSelectedParts((prev) => ({
            ...prev,
            [activeSlotId]: product,
        }));

        toast.success(`Selected ${product.name}`);

        // If auto-advance is on, advance to next unselected slot
        if (autoAdvance) {
            const slotsList =
                activeTab === "essentials"
                    ? ESSENTIALS_SLOTS
                    : ACCESSORIES_SLOTS;
            const currentIndex = slotsList.findIndex(
                (s) => s.id === activeSlotId,
            );

            // Find next unselected slot in this tab
            let nextSlot = slotsList.slice(currentIndex + 1).find((s) => {
                return !selectedParts[s.id] && s.id !== activeSlotId;
            });

            // If none found after, loop back from beginning
            if (!nextSlot) {
                nextSlot = slotsList.find((s) => {
                    return !selectedParts[s.id] && s.id !== activeSlotId;
                });
            }

            if (nextSlot) {
                setActiveSlotId(nextSlot.id);
            }
        }
    };

    // Remove component from slot
    const handleRemoveProduct = (slotId: string) => {
        setSelectedParts((prev) => {
            const copy = { ...prev };
            delete copy[slotId];
            return copy;
        });
        toast.info(
            `Removed ${
                [...ESSENTIALS_SLOTS, ...ACCESSORIES_SLOTS].find(
                    (s) => s.id === slotId,
                )?.shortName || "component"
            }`,
        );
    };

    // Apply Pre-built Template
    const handleApplyTemplate = (template: PreBuiltTemplate) => {
        const newSelected: Record<string, BuilderProduct> = {};

        Object.entries(template.parts).forEach(([slotKey, prodId]) => {
            const found = products.find((p) => p.id === prodId);
            if (found) {
                newSelected[slotKey] = found;
            }
        });

        setSelectedParts(newSelected);
        setIsTemplatesModalOpen(false);

        if (template.targetChipset === "AMD") {
            setChipsetFilter("AMD");
        } else if (template.targetChipset === "Intel") {
            setChipsetFilter("Intel");
        }

        toast.success(`Loaded "${template.name}" configuration!`);
    };

    // Add All Selected Parts to Global Cart
    const handleAddToCartAll = () => {
        const partsList = Object.values(selectedParts);
        if (partsList.length === 0) {
            toast.error("Please select at least one component to add to cart.");
            return;
        }

        setIsSubmittingCart(true);

        try {
            partsList.forEach((part) => {
                addToCart({
                    productId: String(part.id),
                    name: part.name,
                    price: part.price,
                    image: part.image,
                    quantity: 1,
                });
            });

            toast.success(
                `Added all ${partsList.length} PC components to your cart!`,
            );
        } catch (error) {
            toast.error("Failed to add items to cart. Please try again.");
        } finally {
            setIsSubmittingCart(false);
        }
    };

    // Save build to localStorage
    const handleSaveBuild = () => {
        if (selectedCount === 0) {
            toast.error("Select components before saving.");
            return;
        }
        try {
            localStorage.setItem(
                "comlink_saved_pc_build",
                JSON.stringify(selectedParts),
            );
            toast.success("Build configuration saved to this browser!");
        } catch (err) {
            toast.error("Could not save build configuration.");
        }
    };

    // Share build
    const handleShareBuild = () => {
        if (selectedCount === 0) {
            toast.error("Add some components to share your build.");
            return;
        }

        const lines = [
            "🖥️ My Custom PC Build from ComLink Computer Accessories",
            "--------------------------------------------------",
            ...Object.entries(selectedParts).map(([slotKey, part]) => {
                const sDef = [
                    ...ESSENTIALS_SLOTS,
                    ...ACCESSORIES_SLOTS,
                ].find((s) => s.id === slotKey);
                return `• ${sDef?.shortName || slotKey}: ${part.name} - ${formatLkr(part.price)}`;
            }),
            "--------------------------------------------------",
            `⚡ Estimated Wattage: ${totalWattage}W`,
            `💰 Total Investment: ${formatLkr(totalCost)}`,
            `🔗 Build Yours at: ${window.location.href}`,
        ];

        const text = lines.join("\n");
        navigator.clipboard.writeText(text);
        toast.success("Build summary copied to clipboard! Ready to share.");
    };

    // Clear Build
    const handleClearBuild = () => {
        if (selectedCount === 0) return;
        if (
            window.confirm(
                "Are you sure you want to clear your current PC build?",
            )
        ) {
            setSelectedParts({});
            setActiveSlotId("processors");
            toast.info("Build cleared.");
        }
    };

    // Generate formatted text receipt for export
    const generateConfigText = () => {
        return [
            "==========================================================",
            "          COMLINK COMPUTER ACCESSORIES - PC BUILD         ",
            "                    CONFIG & SPEC SHEET                   ",
            "==========================================================",
            `Generated on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}`,
            `Status: ${compatibilityCheck.status} (${compatibilityCheck.message})`,
            `Total Components: ${selectedCount}`,
            `Estimated Power: ${totalWattage}W`,
            "----------------------------------------------------------",
            "SELECTED COMPONENTS:",
            ...Object.entries(selectedParts).map(([slot, item]) => {
                const s = [...ESSENTIALS_SLOTS, ...ACCESSORIES_SLOTS].find(
                    (x) => x.id === slot,
                );
                return `[${s?.name || slot}]\n  Name:  ${item.name}\n  Brand: ${item.brand}\n  Price: ${formatLkr(item.price)}\n`;
            }),
            "----------------------------------------------------------",
            `TOTAL BUILD COST: ${formatLkr(totalCost)}`,
            "==========================================================",
            "Contact ComLink: 127.0.0.1:8000 | support@comlink.lk",
        ].join("\n");
    };

    const handleDownloadConfig = () => {
        const text = generateConfigText();
        const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `ComLink-PC-Build-${Date.now()}.txt`;
        link.click();
        URL.revokeObjectURL(url);
        toast.success("Build configuration file downloaded!");
    };

    return (
        <>
            <Head title="Build My PC | ComLink - Custom PC Builder" />

            <div className="min-h-screen bg-bg text-fg pb-20">
                {/* ---------------- Breadcrumb ---------------- */}
                <div className="border-b border-border/40 bg-card/40 backdrop-blur-sm">
                    <div className="max-w-[1700px] mx-auto px-4 sm:px-6 py-3">
                        <nav className="flex items-center gap-2 text-xs text-muted">
                            <Link
                                href="/"
                                className="hover:text-cyan-500 transition-colors"
                            >
                                Home
                            </Link>
                            <span>/</span>
                            <span className="text-cyan-600 dark:text-cyan-400 font-semibold">
                                Build My PC
                            </span>
                        </nav>
                    </div>
                </div>

                {/* ---------------- Main Builder Workspace ---------------- */}
                <div className="max-w-[1700px] mx-auto px-4 sm:px-6 pt-6 sm:pt-8">
                    {/* 3-Column Responsive Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {/* ========================================================== */}
                        {/* LEFT PANEL: COMPONENTS SELECTOR (col-span-3)               */}
                        {/* ========================================================== */}
                        <div className="lg:col-span-3 space-y-4">
                            <div className="rounded-2xl border border-border bg-card p-5 shadow-xs">
                                {/* Header */}
                                <div className="mb-4">
                                    <h2 className="font-display text-xl sm:text-2xl font-bold tracking-tight text-fg uppercase">
                                        Components
                                    </h2>
                                    <p className="text-xs text-muted mt-0.5">
                                        Select parts to build your dream PC
                                    </p>
                                </div>

                                {/* Chipset Toggle Pills (All | AMD | Intel) */}
                                <div className="mb-4 flex items-center justify-between border-b border-border/50 pb-4">
                                    <span className="text-xs font-bold uppercase tracking-wider text-muted">
                                        Chipset
                                    </span>
                                    <div className="inline-flex rounded-xl bg-bg p-1 border border-border/80">
                                        {(["ALL", "AMD", "Intel"] as const).map(
                                            (chip) => {
                                                const isActive =
                                                    chipsetFilter === chip;
                                                return (
                                                    <button
                                                        key={chip}
                                                        type="button"
                                                        onClick={() =>
                                                            setChipsetFilter(
                                                                chip,
                                                            )
                                                        }
                                                        className={cn(
                                                            "px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-lg transition-all cursor-pointer",
                                                            isActive
                                                                ? chip === "AMD"
                                                                    ? "bg-rose-600 text-white shadow-xs"
                                                                    : chip ===
                                                                        "Intel"
                                                                      ? "bg-blue-600 text-white shadow-xs"
                                                                      : "bg-cyan-600 text-white shadow-xs"
                                                                : "text-muted hover:text-fg",
                                                        )}
                                                    >
                                                        {chip}
                                                    </button>
                                                );
                                            },
                                        )}
                                    </div>
                                </div>

                                {/* Category Mode Tabs (Essentials | Accessories) */}
                                <div className="grid grid-cols-2 gap-2 mb-4">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setActiveTab("essentials");
                                            if (
                                                !ESSENTIALS_SLOTS.some(
                                                    (s) => s.id === activeSlotId,
                                                )
                                            ) {
                                                setActiveSlotId(
                                                    ESSENTIALS_SLOTS[0].id,
                                                );
                                            }
                                        }}
                                        className={cn(
                                            "py-2 px-3 text-xs font-bold uppercase tracking-wider rounded-xl transition-all cursor-pointer border text-center",
                                            activeTab === "essentials"
                                                ? "bg-cyan-600/10 border-cyan-500 text-cyan-600 dark:text-cyan-400 shadow-xs"
                                                : "border-border/70 text-muted hover:text-fg hover:border-border",
                                        )}
                                    >
                                        Essentials
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setActiveTab("accessories");
                                            if (
                                                !ACCESSORIES_SLOTS.some(
                                                    (s) => s.id === activeSlotId,
                                                )
                                            ) {
                                                setActiveSlotId(
                                                    ACCESSORIES_SLOTS[0].id,
                                                );
                                            }
                                        }}
                                        className={cn(
                                            "py-2 px-3 text-xs font-bold uppercase tracking-wider rounded-xl transition-all cursor-pointer border text-center",
                                            activeTab === "accessories"
                                                ? "bg-cyan-600/10 border-cyan-500 text-cyan-600 dark:text-cyan-400 shadow-xs"
                                                : "border-border/70 text-muted hover:text-fg hover:border-border",
                                        )}
                                    >
                                        Accessories
                                    </button>
                                </div>

                                {/* Component Slots Vertical List */}
                                <div className="space-y-2">
                                    {currentSlots.map((slot) => {
                                        const isSelected = Boolean(
                                            selectedParts[slot.id],
                                        );
                                        const selectedProduct =
                                            selectedParts[slot.id];
                                        const isActiveSlot =
                                            activeSlotId === slot.id;
                                        const IconComponent = slot.icon;

                                        return (
                                            <button
                                                key={slot.id}
                                                type="button"
                                                onClick={() =>
                                                    setActiveSlotId(slot.id)
                                                }
                                                className={cn(
                                                    "w-full text-left p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between group relative overflow-hidden",
                                                    isActiveSlot
                                                        ? "border-cyan-500 bg-gradient-to-r from-cyan-500/15 via-cyan-500/5 to-transparent ring-1 ring-cyan-500/30 shadow-xs"
                                                        : isSelected
                                                          ? "border-emerald-500/40 bg-emerald-500/5 hover:border-emerald-500"
                                                          : "border-border/60 bg-card/60 hover:border-cyan-500/40 hover:bg-card",
                                                )}
                                            >
                                                <div className="flex items-center gap-3 min-w-0 pr-2">
                                                    {/* Slot Icon */}
                                                    <div
                                                        className={cn(
                                                            "h-10 w-10 shrink-0 rounded-xl flex items-center justify-center transition-colors",
                                                            isActiveSlot
                                                                ? "bg-cyan-600 text-white shadow-xs"
                                                                : isSelected
                                                                  ? "bg-emerald-600 text-white"
                                                                  : "bg-muted/10 text-muted group-hover:text-fg group-hover:bg-muted/20",
                                                        )}
                                                    >
                                                        <IconComponent className="h-5 w-5" />
                                                    </div>

                                                    {/* Slot Info */}
                                                    <div className="min-w-0">
                                                        <div className="flex items-center gap-1.5">
                                                            <span
                                                                className={cn(
                                                                    "font-display text-xs font-bold uppercase tracking-wider truncate",
                                                                    isActiveSlot
                                                                        ? "text-cyan-600 dark:text-cyan-400"
                                                                        : "text-fg",
                                                                )}
                                                            >
                                                                {slot.name}
                                                            </span>
                                                            {isSelected && (
                                                                <Check className="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                                                            )}
                                                        </div>

                                                        {/* Status */}
                                                        <p
                                                            className={cn(
                                                                "text-[11px] truncate mt-0.5",
                                                                isSelected
                                                                    ? "text-emerald-600 dark:text-emerald-400 font-semibold"
                                                                    : "text-muted",
                                                            )}
                                                        >
                                                            {isSelected
                                                                ? selectedProduct.name
                                                                : "Not Selected"}
                                                        </p>
                                                    </div>
                                                </div>

                                                {/* Price badge / Chevron */}
                                                <div className="shrink-0 flex items-center gap-1">
                                                    {isSelected ? (
                                                        <span className="text-[11px] font-bold text-fg">
                                                            {formatLkr(
                                                                selectedProduct.price,
                                                            )}
                                                        </span>
                                                    ) : (
                                                        <ChevronRight className="h-4 w-4 text-muted group-hover:text-fg transition-transform group-hover:translate-x-0.5" />
                                                    )}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>

                        {/* ========================================================== */}
                        {/* CENTER PANEL: METRICS + PRODUCT PICKER (col-span-6)       */}
                        {/* ========================================================== */}
                        <div className="lg:col-span-6 space-y-6">
                            {/* Header row with Title, Auto-advance, and Templates Button */}
                            <div className="rounded-2xl border border-border bg-card p-5 sm:p-6 shadow-xs">
                                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border/50">
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <h1 className="font-display text-2xl sm:text-3xl font-extrabold tracking-tight text-fg">
                                                Build My PC
                                            </h1>
                                            {/* <span className="rounded-md bg-cyan-500/15 border border-cyan-500/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">
                                                v1.0 beta
                                            </span> */}
                                        </div>
                                        <p className="text-xs text-muted mt-0.5">
                                            by ComLink Computer Accessories
                                        </p>
                                    </div>

                                    {/* Pre-Built Templates Button */}
                                    {/* <button
                                        type="button"
                                        onClick={() =>
                                            setIsTemplatesModalOpen(true)
                                        }
                                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-100 dark:hover:bg-white dark:text-slate-950 px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer"
                                    >
                                        <Sparkles className="h-4 w-4 text-amber-400" />
                                        <span>Pre Built PC Templates</span>
                                    </button> */}
                                </div>

                                {/* Auto-advance switch */}
                                <div className="mt-3.5 flex items-center justify-between">
                                    <label
                                        htmlFor="auto-advance-toggle"
                                        className="text-xs text-muted hover:text-fg cursor-pointer flex items-center gap-2"
                                    >
                                        <span>
                                            Auto-advance to next category after
                                            selecting a product
                                        </span>
                                    </label>
                                    <button
                                        type="button"
                                        id="auto-advance-toggle"
                                        role="switch"
                                        aria-checked={autoAdvance}
                                        onClick={() =>
                                            setAutoAdvance(!autoAdvance)
                                        }
                                        className={cn(
                                            "relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none",
                                            autoAdvance
                                                ? "bg-cyan-600"
                                                : "bg-muted/30",
                                        )}
                                    >
                                        <span
                                            className={cn(
                                                "pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out",
                                                autoAdvance
                                                    ? "translate-x-5"
                                                    : "translate-x-0",
                                            )}
                                        />
                                    </button>
                                </div>

                                {/* ---------------- 4 KPI STATS ROW (matches competitor) ---------------- */}
                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                                    {/* 1. Component Count */}
                                    <div className="rounded-xl border border-border/80 bg-bg/80 p-3.5 flex flex-col justify-between relative overflow-hidden">
                                        <div className="flex items-center justify-between text-muted mb-2">
                                            <Cpu className="h-4 w-4 text-cyan-500" />
                                            <span className="text-[10px] font-bold uppercase tracking-wider rounded bg-card px-1.5 py-0.5 border border-border/60">
                                                Count
                                            </span>
                                        </div>
                                        <div>
                                            <div className="font-display text-2xl font-bold text-fg">
                                                {selectedCount}
                                            </div>
                                            <span className="text-[11px] text-muted">
                                                Component Count
                                            </span>
                                        </div>
                                    </div>

                                    {/* 2. Total Cost */}
                                    <div className="rounded-xl border border-border/80 bg-bg/80 p-3.5 flex flex-col justify-between relative overflow-hidden">
                                        <div className="flex items-center justify-between text-muted mb-2">
                                            <span className="font-bold text-emerald-500 text-sm">
                                                Rs
                                            </span>
                                            <span className="text-[10px] font-bold uppercase tracking-wider rounded bg-card px-1.5 py-0.5 border border-border/60 text-emerald-600 dark:text-emerald-400">
                                                Total
                                            </span>
                                        </div>
                                        <div>
                                            <div className="font-display text-xl sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400 truncate">
                                                {formatLkr(totalCost)}
                                            </div>
                                            <span className="text-[11px] text-muted">
                                                Total Cost
                                            </span>
                                        </div>
                                    </div>

                                    {/* 3. Power Supply (Wattage) */}
                                    <div className="rounded-xl border border-border/80 bg-bg/80 p-3.5 flex flex-col justify-between relative overflow-hidden">
                                        <div className="flex items-center justify-between text-muted mb-2">
                                            <Zap className="h-4 w-4 text-amber-500" />
                                            <span className="text-[10px] font-bold uppercase tracking-wider rounded bg-card px-1.5 py-0.5 border border-border/60 text-amber-600 dark:text-amber-400">
                                                Estimated
                                            </span>
                                        </div>
                                        <div>
                                            <div className="font-display text-2xl font-bold text-fg">
                                                {totalWattage}W
                                            </div>
                                            <span className="text-[11px] text-muted">
                                                Power Supply
                                            </span>
                                        </div>
                                    </div>

                                    {/* 4. Compatibility */}
                                    <div className="rounded-xl border border-border/80 bg-bg/80 p-3.5 flex flex-col justify-between relative overflow-hidden">
                                        <div className="flex items-center justify-between text-muted mb-2">
                                            <ShieldCheck
                                                className={cn(
                                                    "h-4 w-4",
                                                    compatibilityCheck.isOk
                                                        ? "text-cyan-500"
                                                        : "text-rose-500",
                                                )}
                                            />
                                            <span className="text-[10px] font-bold uppercase tracking-wider rounded bg-card px-1.5 py-0.5 border border-border/60">
                                                Status
                                            </span>
                                        </div>
                                        <div>
                                            <div
                                                className={cn(
                                                    "font-display text-lg font-bold truncate",
                                                    compatibilityCheck.color,
                                                )}
                                            >
                                                {compatibilityCheck.status}
                                            </div>
                                            <span className="text-[11px] text-muted truncate block">
                                                Compatibility
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* ---------------- ACTIVE SLOT PRODUCT PICKER ---------------- */}
                            <div className="rounded-2xl border border-border bg-card p-5 sm:p-6 shadow-xs">
                                {/* Slot Title & Search / Brand Filters */}
                                <div className="space-y-4 mb-6">
                                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-border/50">
                                        <div className="flex items-center gap-3">
                                            <div className="h-9 w-9 rounded-xl bg-cyan-600 text-white flex items-center justify-center shrink-0">
                                                {React.createElement(
                                                    activeSlotDef.icon,
                                                    {
                                                        className: "h-5 w-5",
                                                    },
                                                )}
                                            </div>
                                            <div>
                                                <h3 className="font-display text-lg font-bold text-fg uppercase tracking-wide">
                                                    Select {activeSlotDef.name}
                                                </h3>
                                                <p className="text-xs text-muted">
                                                    {activeSlotDef.description}
                                                </p>
                                            </div>
                                        </div>

                                        {/* If a part is already selected in this slot */}
                                        {selectedParts[activeSlotId] && (
                                            <div className="flex items-center gap-2">
                                                <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">
                                                    Part Selected
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handleRemoveProduct(
                                                            activeSlotId,
                                                        )
                                                    }
                                                    className="p-1 text-muted hover:text-rose-500 transition-colors"
                                                    title="Remove selected part"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        )}
                                    </div>

                                    {/* Search & Filter Bar */}
                                    <div className="flex flex-col sm:flex-row gap-3 items-center">
                                        {/* Search Input */}
                                        <div className="relative flex-1 w-full">
                                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted" />
                                            <input
                                                type="text"
                                                value={searchQuery}
                                                onChange={(e) =>
                                                    setSearchQuery(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder={`Search ${activeSlotDef.shortName} by name or specs...`}
                                                className="w-full rounded-xl border border-border bg-bg pl-10 pr-4 py-2 text-xs text-fg focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-colors"
                                            />
                                            {searchQuery && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setSearchQuery("")
                                                    }
                                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-fg"
                                                >
                                                    <X className="h-3.5 w-3.5" />
                                                </button>
                                            )}
                                        </div>

                                        {/* Brand Filter */}
                                        {availableBrands.length > 0 && (
                                            <div className="flex items-center gap-1.5 overflow-x-auto max-w-full pb-1 sm:pb-0">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setSelectedBrand("ALL")
                                                    }
                                                    className={cn(
                                                        "px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-all cursor-pointer whitespace-nowrap",
                                                        selectedBrand === "ALL"
                                                            ? "bg-fg text-bg border-fg"
                                                            : "border-border text-muted hover:text-fg",
                                                    )}
                                                >
                                                    All Brands
                                                </button>
                                                {availableBrands.map((b) => (
                                                    <button
                                                        key={b}
                                                        type="button"
                                                        onClick={() =>
                                                            setSelectedBrand(b)
                                                        }
                                                        className={cn(
                                                            "px-2.5 py-1.5 text-xs font-bold rounded-lg border transition-all cursor-pointer whitespace-nowrap",
                                                            selectedBrand === b
                                                                ? "bg-cyan-600 text-white border-cyan-600"
                                                                : "border-border text-muted hover:text-fg",
                                                        )}
                                                    >
                                                        {b}
                                                    </button>
                                                ))}
                                            </div>
                                        )}

                                        {/* Sort By Dropdown */}
                                        <select
                                            value={sortBy}
                                            onChange={(e) =>
                                                setSortBy(e.target.value as any)
                                            }
                                            className="rounded-xl border border-border bg-bg px-3 py-2 text-xs text-fg focus:border-cyan-500 focus:outline-none cursor-pointer"
                                        >
                                            <option value="featured">
                                                Featured
                                            </option>
                                            <option value="price_asc">
                                                Price: Low to High
                                            </option>
                                            <option value="price_desc">
                                                Price: High to Low
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                {/* Products Grid */}
                                {sortedSlotProducts.length > 0 ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {sortedSlotProducts.map((prod) => {
                                            const isCurrentSelected =
                                                selectedParts[activeSlotId]
                                                    ?.id === prod.id;

                                            return (
                                                <div
                                                    key={prod.id}
                                                    className={cn(
                                                        "group rounded-2xl border p-4 transition-all duration-300 flex flex-col justify-between relative",
                                                        isCurrentSelected
                                                            ? "border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/20 shadow-md"
                                                            : "border-border bg-card/60 hover:border-cyan-500/50 hover:bg-card shadow-xs",
                                                    )}
                                                >
                                                    {/* Top Badges */}
                                                    <div className="flex items-center justify-between mb-3">
                                                        <span className="rounded bg-primary-100 dark:bg-primary-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary-700 dark:text-primary-200">
                                                            {prod.brand}
                                                        </span>
                                                        {prod.wattage !==
                                                            undefined &&
                                                            prod.wattage >
                                                                0 && (
                                                                <span className="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                                                    <Zap className="h-3 w-3" />
                                                                    {
                                                                        prod.wattage
                                                                    }
                                                                    W
                                                                </span>
                                                            )}
                                                    </div>

                                                    {/* Product Image & Title */}
                                                    <div className="flex items-start gap-3 mb-3">
                                                        <div className="h-20 w-20 shrink-0 rounded-xl bg-slate-100 dark:bg-slate-900 border border-border/60 p-2 flex items-center justify-center overflow-hidden">
                                                            <img
                                                                src={prod.image}
                                                                alt={prod.name}
                                                                className="h-full w-full object-contain transition-transform duration-300 group-hover:scale-110"
                                                                loading="lazy"
                                                            />
                                                        </div>
                                                        <div className="min-w-0 flex-1">
                                                            <h4 className="font-semibold text-xs text-fg leading-snug line-clamp-2 group-hover:text-cyan-500 transition-colors">
                                                                {prod.name}
                                                            </h4>

                                                            {/* Specs Chips */}
                                                            {prod.specs && (
                                                                <div className="flex flex-wrap gap-1 mt-2">
                                                                    {Object.entries(
                                                                        prod.specs,
                                                                    )
                                                                        .slice(
                                                                            0,
                                                                            3,
                                                                        )
                                                                        .map(
                                                                            ([
                                                                                key,
                                                                                val,
                                                                            ]) => (
                                                                                <span
                                                                                    key={
                                                                                        key
                                                                                    }
                                                                                    className="text-[10px] text-muted bg-bg px-1.5 py-0.5 rounded border border-border/40"
                                                                                >
                                                                                    {
                                                                                        val
                                                                                    }
                                                                                </span>
                                                                            ),
                                                                        )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>

                                                    {/* Price & Action Button */}
                                                    <div className="flex items-center justify-between pt-3 border-t border-border/50 mt-auto">
                                                        <div>
                                                            <span className="text-sm font-bold text-fg">
                                                                {formatLkr(
                                                                    prod.price,
                                                                )}
                                                            </span>
                                                            {prod.originalPrice &&
                                                                prod.originalPrice >
                                                                    prod.price && (
                                                                    <span className="block text-[10px] text-muted line-through">
                                                                        {formatLkr(
                                                                            prod.originalPrice,
                                                                        )}
                                                                    </span>
                                                                )}
                                                        </div>

                                                        {isCurrentSelected ? (
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    handleRemoveProduct(
                                                                        activeSlotId,
                                                                    )
                                                                }
                                                                className="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 text-white px-3 py-1.5 text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-rose-600 transition-colors cursor-pointer"
                                                            >
                                                                <Check className="h-3.5 w-3.5" />
                                                                <span>
                                                                    Selected
                                                                </span>
                                                            </button>
                                                        ) : (
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    handleSelectProduct(
                                                                        prod,
                                                                    )
                                                                }
                                                                className="inline-flex items-center gap-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider shadow-sm hover:shadow-cyan-500/25 active:scale-95 transition-all cursor-pointer"
                                                            >
                                                                <Plus className="h-3.5 w-3.5" />
                                                                <span>
                                                                    Add to Build
                                                                </span>
                                                            </button>
                                                        )}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="py-12 text-center rounded-2xl border border-dashed border-border bg-bg/50 p-6">
                                        <AlertTriangle className="mx-auto h-8 w-8 text-amber-500 mb-2" />
                                        <h4 className="text-sm font-bold text-fg">
                                            No Components Found
                                        </h4>
                                        <p className="text-xs text-muted mt-1 max-w-sm mx-auto">
                                            {searchQuery ||
                                            selectedBrand !== "ALL" ||
                                            chipsetFilter !== "ALL"
                                                ? "Try clearing your search query or chipset filters to see more parts."
                                                : "No products are currently available in this category."}
                                        </p>
                                        {(searchQuery ||
                                            selectedBrand !== "ALL" ||
                                            chipsetFilter !== "ALL") && (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setSearchQuery("");
                                                    setSelectedBrand("ALL");
                                                    setChipsetFilter("ALL");
                                                }}
                                                className="mt-4 px-3 py-1.5 text-xs font-bold uppercase tracking-wider bg-card hover:bg-muted/10 border border-border rounded-xl transition-colors cursor-pointer"
                                            >
                                                Reset Filters
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* ========================================================== */}
                        {/* RIGHT PANEL: BUILD SUMMARY (col-span-3)                    */}
                        {/* ========================================================== */}
                        <div className="lg:col-span-3 space-y-4">
                            <div className="rounded-2xl border border-border bg-card p-5 shadow-xs">
                                {/* Header */}
                                <div className="mb-4">
                                    <h2 className="font-display text-xl sm:text-2xl font-bold tracking-tight text-fg uppercase">
                                        Build Summary
                                    </h2>
                                    <p className="text-xs text-muted mt-0.5">
                                        Review and finalize your configuration
                                    </p>
                                </div>

                                {/* Preview Card: Empty State OR Selected Components Breakdown */}
                                {selectedCount === 0 ? (
                                    <div className="rounded-2xl border border-dashed border-border/80 bg-bg/50 p-8 text-center my-4">
                                        <div className="h-14 w-14 rounded-2xl bg-muted/10 text-muted mx-auto flex items-center justify-center mb-3">
                                            <Monitor className="h-7 w-7" />
                                        </div>
                                        <h4 className="font-bold text-sm text-fg">
                                            No Components Selected
                                        </h4>
                                        <p className="text-xs text-muted mt-1 leading-relaxed">
                                            Start building by selecting
                                            components from the left panel.
                                        </p>
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-border/80 bg-bg/60 p-3 my-4 max-h-[360px] overflow-y-auto space-y-2 divide-y divide-border/40">
                                        {Object.entries(selectedParts).map(
                                            ([slotKey, part]) => {
                                                const sDef = [
                                                    ...ESSENTIALS_SLOTS,
                                                    ...ACCESSORIES_SLOTS,
                                                ].find(
                                                    (s) => s.id === slotKey,
                                                );

                                                return (
                                                    <div
                                                        key={slotKey}
                                                        className="pt-2 first:pt-0 flex items-center justify-between gap-2"
                                                    >
                                                        <div className="flex items-center gap-2 min-w-0">
                                                            <div className="h-8 w-8 rounded-lg bg-card border border-border/60 shrink-0 p-1 flex items-center justify-center">
                                                                <img
                                                                    src={
                                                                        part.image
                                                                    }
                                                                    alt={
                                                                        part.name
                                                                    }
                                                                    className="h-full w-full object-contain"
                                                                />
                                                            </div>
                                                            <div className="min-w-0">
                                                                <span className="block text-[10px] font-bold text-cyan-600 dark:text-cyan-400 uppercase">
                                                                    {sDef?.shortName ||
                                                                        slotKey}
                                                                </span>
                                                                <p className="text-xs text-fg font-medium truncate">
                                                                    {part.name}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-1.5 shrink-0">
                                                            <span className="text-xs font-bold text-fg">
                                                                {formatLkr(
                                                                    part.price,
                                                                )}
                                                            </span>
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    handleRemoveProduct(
                                                                        slotKey,
                                                                    )
                                                                }
                                                                className="text-muted hover:text-rose-500 p-1"
                                                                title="Remove part"
                                                            >
                                                                <X className="h-3.5 w-3.5" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                );
                                            },
                                        )}
                                    </div>
                                )}

                                {/* Total Price Row */}
                                <div className="border-t border-border pt-4 mt-4 flex items-center justify-between">
                                    <span className="font-display text-sm font-bold uppercase tracking-wider text-muted">
                                        Total
                                    </span>
                                    <span className="font-display text-xl sm:text-2xl font-extrabold text-fg">
                                        {formatLkr(totalCost)}
                                    </span>
                                </div>

                                {/* Action Buttons */}
                                <div className="space-y-2 mt-5">
                                    {/* Primary Add to Cart */}
                                    <button
                                        type="button"
                                        disabled={
                                            selectedCount === 0 ||
                                            isSubmittingCart
                                        }
                                        onClick={handleAddToCartAll}
                                        className={cn(
                                            "w-full flex items-center justify-center gap-2 rounded-xl py-3 px-4 text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer",
                                            selectedCount > 0
                                                ? "bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white shadow-cyan-500/20"
                                                : "bg-muted/20 text-muted border border-border/50 cursor-not-allowed shadow-none",
                                        )}
                                    >
                                        <ShoppingCart className="h-4 w-4" />
                                        <span>Add to Cart</span>
                                    </button>

                                    {/* Save Build */}
                                    <button
                                        type="button"
                                        onClick={handleSaveBuild}
                                        disabled={selectedCount === 0}
                                        className="w-full flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 text-xs font-bold uppercase tracking-wider border border-border bg-card hover:bg-muted/10 text-fg transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <Copy className="h-4 w-4 text-muted" />
                                        <span>Save Build</span>
                                    </button>

                                    {/* Share Build */}
                                    <button
                                        type="button"
                                        onClick={handleShareBuild}
                                        disabled={selectedCount === 0}
                                        className="w-full flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 text-xs font-bold uppercase tracking-wider border border-border bg-card hover:bg-muted/10 text-fg transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <Share2 className="h-4 w-4 text-muted" />
                                        <span>Share Build</span>
                                    </button>

                                    {/* Export Config */}
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setIsExportModalOpen(true)
                                        }
                                        disabled={selectedCount === 0}
                                        className="w-full flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 text-xs font-bold uppercase tracking-wider border border-border bg-card hover:bg-muted/10 text-fg transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <Download className="h-4 w-4 text-muted" />
                                        <span>Export Config</span>
                                    </button>

                                    {/* Clear Build */}
                                    <button
                                        type="button"
                                        onClick={handleClearBuild}
                                        disabled={selectedCount === 0}
                                        className="w-full flex items-center justify-center gap-2 rounded-xl py-2.5 px-4 text-xs font-bold uppercase tracking-wider border border-rose-500/20 text-rose-600 dark:text-rose-400 hover:bg-rose-500/10 transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                        <span>Clear Build</span>
                                    </button>
                                </div>

                                {/* ---------------- Build Tips Box (matches competitor) ---------------- */}
                                <div className="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                                    <div className="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold text-xs uppercase tracking-wider mb-2">
                                        <Lightbulb className="h-4 w-4 shrink-0" />
                                        <span>Build Tips</span>
                                    </div>
                                    <ul className="text-[11px] text-muted space-y-1.5 leading-relaxed">
                                        <li className="flex items-start gap-1.5">
                                            <span className="text-amber-500 font-bold">
                                                •
                                            </span>
                                            <span>
                                                Ensure PSU has 20% headroom
                                                above total wattage
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-1.5">
                                            <span className="text-amber-500 font-bold">
                                                •
                                            </span>
                                            <span>
                                                Check motherboard compatibility
                                                with CPU socket
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-1.5">
                                            <span className="text-amber-500 font-bold">
                                                •
                                            </span>
                                            <span>
                                                Verify RAM compatibility with
                                                motherboard
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-1.5">
                                            <span className="text-amber-500 font-bold">
                                                •
                                            </span>
                                            <span>
                                                Ensure case size fits all
                                                components & GPU
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* ========================================================== */}
            {/* MODAL: PRE-BUILT PC TEMPLATES                              */}
            {/* ========================================================== */}
            {isTemplatesModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="relative w-full max-w-4xl rounded-3xl border border-border bg-card p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
                        <button
                            type="button"
                            onClick={() => setIsTemplatesModalOpen(false)}
                            className="absolute top-5 right-5 h-8 w-8 rounded-full border border-border flex items-center justify-center text-muted hover:text-fg transition-colors"
                        >
                            <X className="h-4 w-4" />
                        </button>

                        <div className="mb-6">
                            <span className="text-xs font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">
                                Instant Configurations
                            </span>
                            <h3 className="font-display text-2xl font-extrabold text-fg">
                                Pre-Built PC Templates
                            </h3>
                            <p className="text-xs text-muted mt-1">
                                Choose from our battle-tested configurations.
                                Load them with one click and customize as you
                                like.
                            </p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {templates.map((tmpl) => (
                                <div
                                    key={tmpl.id}
                                    className="rounded-2xl border border-border/80 bg-bg/60 p-5 flex flex-col justify-between hover:border-cyan-500 transition-all shadow-xs group"
                                >
                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <span
                                                className={cn(
                                                    "px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full border",
                                                    tmpl.badgeColor === "cyan"
                                                        ? "bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/30"
                                                        : tmpl.badgeColor ===
                                                            "emerald"
                                                          ? "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30"
                                                          : tmpl.badgeColor ===
                                                              "purple"
                                                            ? "bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30"
                                                            : "bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30",
                                                )}
                                            >
                                                {tmpl.badge}
                                            </span>
                                            <span className="text-[11px] font-bold text-muted">
                                                ⚡ {tmpl.totalWattage}W
                                            </span>
                                        </div>

                                        <h4 className="font-display text-lg font-bold text-fg group-hover:text-cyan-500 transition-colors">
                                            {tmpl.name}
                                        </h4>
                                        <p className="text-xs text-muted mt-1">
                                            {tmpl.subtitle}
                                        </p>

                                        {/* Parts list preview */}
                                        <div className="mt-4 space-y-1.5 border-t border-border/40 pt-3">
                                            {Object.entries(tmpl.parts).map(
                                                ([slotKey, pId]) => {
                                                    const prod = products.find(
                                                        (p) => p.id === pId,
                                                    );
                                                    const s =
                                                        ESSENTIALS_SLOTS.find(
                                                            (x) =>
                                                                x.id ===
                                                                slotKey,
                                                        );
                                                    if (!prod) return null;
                                                    return (
                                                        <div
                                                            key={slotKey}
                                                            className="flex items-center justify-between text-[11px]"
                                                        >
                                                            <span className="text-muted font-medium">
                                                                {s?.shortName ||
                                                                    slotKey}
                                                                :
                                                            </span>
                                                            <span className="text-fg font-semibold truncate max-w-[200px]">
                                                                {prod.name}
                                                            </span>
                                                        </div>
                                                    );
                                                },
                                            )}
                                        </div>
                                    </div>

                                    <div className="mt-5 pt-4 border-t border-border/50 flex items-center justify-between">
                                        <div>
                                            <span className="text-[10px] uppercase font-bold text-muted block">
                                                Est. Total
                                            </span>
                                            <span className="font-display text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                                                {formatLkr(tmpl.estimatedPrice)}
                                            </span>
                                        </div>

                                        <button
                                            type="button"
                                            onClick={() =>
                                                handleApplyTemplate(tmpl)
                                            }
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider shadow-sm transition-all active:scale-95 cursor-pointer"
                                        >
                                            <Sparkles className="h-3.5 w-3.5" />
                                            <span>Load This Build</span>
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* ========================================================== */}
            {/* MODAL: EXPORT CONFIG                                       */}
            {/* ========================================================== */}
            {isExportModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
                    <div className="relative w-full max-w-2xl rounded-3xl border border-border bg-card p-6 sm:p-8 shadow-2xl">
                        <button
                            type="button"
                            onClick={() => setIsExportModalOpen(false)}
                            className="absolute top-5 right-5 h-8 w-8 rounded-full border border-border flex items-center justify-center text-muted hover:text-fg transition-colors"
                        >
                            <X className="h-4 w-4" />
                        </button>

                        <div className="mb-4">
                            <h3 className="font-display text-xl font-bold text-fg">
                                Export Configuration Sheet
                            </h3>
                            <p className="text-xs text-muted mt-0.5">
                                Print, download, or copy your detailed PC build
                                manifest.
                            </p>
                        </div>

                        <div className="bg-bg rounded-xl border border-border p-4 font-mono text-[11px] text-muted max-h-72 overflow-y-auto whitespace-pre-wrap select-all">
                            {generateConfigText()}
                        </div>

                        <div className="mt-5 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => {
                                    navigator.clipboard.writeText(
                                        generateConfigText(),
                                    );
                                    toast.success(
                                        "Copied config text to clipboard!",
                                    );
                                }}
                                className="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-fg hover:bg-muted/10 transition-colors cursor-pointer"
                            >
                                <Copy className="h-4 w-4" />
                                <span>Copy Text</span>
                            </button>
                            <button
                                type="button"
                                onClick={handleDownloadConfig}
                                className="inline-flex items-center gap-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors shadow-sm cursor-pointer"
                            >
                                <Download className="h-4 w-4" />
                                <span>Download File</span>
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
