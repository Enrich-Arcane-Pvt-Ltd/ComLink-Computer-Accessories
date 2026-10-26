import { Link, router, usePage } from "@inertiajs/react";
import { formatPrice } from "@/utils/currency";
import { useTheme } from "@/contexts/ThemeContext";
import { AnimatePresence, motion } from "framer-motion";
import {
    ArrowRight,
    Cpu,
    Loader2,
    Package,
    Search,
    TrendingUp,
    X,
} from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

export interface SearchProduct {
    id: string;
    name: string;
    slug: string;
    product_code?: string;
    price: number;
    originalPrice?: number | null;
    category: string;
    subCategory?: string | null;
    brand?: string | null;
    condition?: string;
    images: string[];
    inStock: boolean;
    quantity: number;
}

interface SearchModalProps {
    isOpen: boolean;
    onClose: () => void;
}

const QUICK_CATEGORIES = [
    { label: "All Items", slug: "all" },
    { label: "Laptops", slug: "laptops" },
    { label: "PC Components", slug: "pc-components" },
    { label: "Graphic Cards", slug: "graphic-cards" },
    { label: "Processors", slug: "processors" },
    { label: "Peripherals", slug: "peripherals" },
    { label: "Keyboards", slug: "keyboards" },
    { label: "Mouse", slug: "mouse" },
    { label: "Monitors", slug: "monitors-displays" },
    { label: "Gaming", slug: "gaming" },
    { label: "Storage & SSD", slug: "ssd" },
];

const POPULAR_SEARCHES = [
    "Gaming Laptop",
    "RTX 4060",
    "Mechanical Keyboard",
    "Wireless Mouse",
    "NVMe SSD",
    "Core i7",
    "DDR5 RAM",
    "Gaming Monitor",
];

export function SearchModal({ isOpen, onClose }: SearchModalProps) {
    const { theme } = useTheme();
    const { navCategories } = usePage().props as any;

    const [query, setQuery] = useState("");
    const [selectedCategory, setSelectedCategory] = useState("all");
    const [results, setResults] = useState<SearchProduct[]>([]);
    const [totalCount, setTotalCount] = useState<number>(0);
    const [isLoading, setIsLoading] = useState(false);
    const [hasLoadedInitial, setHasLoadedInitial] = useState(false);

    const inputRef = useRef<HTMLInputElement>(null);
    const abortControllerRef = useRef<AbortController | null>(null);

    // Merge categories if admin/dynamic categories exist
    const categoryOptions = React.useMemo(() => {
        if (Array.isArray(navCategories) && navCategories.length > 0) {
            const mapped = navCategories.map((c: any) => ({
                label: c.name,
                slug: c.slug,
            }));
            return [{ label: "All Items", slug: "all" }, ...mapped];
        }
        return QUICK_CATEGORIES;
    }, [navCategories]);

    // Focus input and lock body scroll on open
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
            setTimeout(() => {
                inputRef.current?.focus();
            }, 50);
        } else {
            document.body.style.overflow = "unset";
            setQuery("");
            setSelectedCategory("all");
        }
        return () => {
            document.body.style.overflow = "unset";
        };
    }, [isOpen]);

    // Handle Escape key
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === "Escape" && isOpen) {
                onClose();
            }
        };
        window.addEventListener("keydown", handleKeyDown);
        return () => window.removeEventListener("keydown", handleKeyDown);
    }, [isOpen, onClose]);

    // Perform live debounced search
    useEffect(() => {
        if (!isOpen) return;

        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        const controller = new AbortController();
        abortControllerRef.current = controller;

        const timer = setTimeout(async () => {
            setIsLoading(true);
            try {
                const params = new URLSearchParams();
                if (query.trim()) {
                    params.set("q", query.trim());
                }
                if (selectedCategory && selectedCategory !== "all") {
                    params.set("category", selectedCategory);
                }
                params.set("limit", "8");

                const response = await fetch(
                    `/api/search-products?${params.toString()}`,
                    {
                        signal: controller.signal,
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    setResults(data.products || []);
                    setTotalCount(data.total || 0);
                    setHasLoadedInitial(true);
                }
            } catch (err: any) {
                if (err.name !== "AbortError") {
                    console.error("Search API error:", err);
                }
            } finally {
                setIsLoading(false);
            }
        }, 220);

        return () => {
            clearTimeout(timer);
            controller.abort();
        };
    }, [query, selectedCategory, isOpen]);

    // Navigate to shop catalog with query
    const handleSearchSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        onClose();
        const params = new URLSearchParams();
        if (query.trim()) {
            params.set("q", query.trim());
        }
        if (selectedCategory && selectedCategory !== "all") {
            params.set("category", selectedCategory);
        }
        router.visit(`/shop?${params.toString()}`);
    };

    const handlePopularClick = (term: string) => {
        setQuery(term);
        inputRef.current?.focus();
    };

    return (
        <AnimatePresence>
            {isOpen && (
                <>
                    {/* Backdrop */}
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.2 }}
                        className="fixed inset-0 bg-black/75 backdrop-blur-md z-50"
                        onClick={onClose}
                    />

                    {/* Search Drawer Modal */}
                    <motion.div
                        initial={{ opacity: 0, y: -25 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -25 }}
                        transition={{ duration: 0.22, ease: "easeOut" }}
                        className="fixed top-0 left-0 right-0 z-50 bg-bg border-b border-border shadow-2xl max-h-[92vh] flex flex-col"
                    >
                        <div className="container mx-auto px-4 md:px-6 py-4 flex flex-col max-h-[92vh]">
                            {/* Header Bar */}
                            <div className="flex items-center justify-between pb-3 border-b border-border">
                                <div className="flex items-center gap-3">
                                    <Link
                                        href="/"
                                        onClick={onClose}
                                        className="flex items-center gap-2 group"
                                    >
                                        <img
                                            src="/images/Comlink_logo.png"
                                            alt="ComLink"
                                            className="h-10 md:h-12 w-auto object-contain transition-transform group-hover:scale-105"
                                        />
                                    </Link>
                                </div>

                                <div className="flex items-center gap-3">
                                    <span className="hidden md:inline-flex items-center gap-1 text-xs text-muted uppercase tracking-wider font-mono">
                                        Press{" "}
                                        <kbd className="px-1.5 py-0.5 rounded bg-card border border-border text-[10px] text-fg font-semibold">
                                            ESC
                                        </kbd>{" "}
                                        to close
                                    </span>
                                    <button
                                        onClick={onClose}
                                        className="p-1.5 rounded-md text-muted hover:text-fg hover:bg-card border border-transparent hover:border-border transition-colors cursor-pointer"
                                        aria-label="Close search"
                                    >
                                        <X className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            {/* Main Search Input Form */}
                            <form
                                onSubmit={handleSearchSubmit}
                                className="mt-4 relative"
                            >
                                <div className="relative flex items-center">
                                    <Search className="absolute left-4 h-5 w-5 text-accent pointer-events-none transition-transform" />
                                    <input
                                        ref={inputRef}
                                        type="text"
                                        value={query}
                                        onChange={(e) =>
                                            setQuery(e.target.value)
                                        }
                                        placeholder="Search laptops, graphics cards, keyboards, processors, RAM, SSDs..."
                                        className="w-full h-14 pl-12 pr-28 rounded-lg bg-card border border-border text-fg text-base md:text-lg placeholder:text-muted/70 focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition-all shadow-inner"
                                    />

                                    <div className="absolute right-3 flex items-center gap-2">
                                        {isLoading && (
                                            <Loader2 className="h-5 w-5 text-accent animate-spin" />
                                        )}
                                        {query && (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setQuery("");
                                                    inputRef.current?.focus();
                                                }}
                                                className="p-1 text-muted hover:text-fg rounded-md hover:bg-muted/10 transition-colors"
                                                aria-label="Clear input"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                        )}
                                        <button
                                            type="submit"
                                            className="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-accent text-accent-fg text-xs font-bold uppercase tracking-wider hover:opacity-90 transition-opacity"
                                        >
                                            Search
                                            <ArrowRight className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {/* Category Filter Chips */}
                            <div className="mt-3 flex items-center gap-2 overflow-x-auto pb-1.5 pt-1 no-scrollbar text-xs">
                                <span className="text-muted font-medium whitespace-nowrap pl-0.5">
                                    Category:
                                </span>
                                {categoryOptions.map((cat) => {
                                    const active =
                                        selectedCategory === cat.slug;
                                    return (
                                        <button
                                            key={cat.slug}
                                            type="button"
                                            onClick={() =>
                                                setSelectedCategory(cat.slug)
                                            }
                                            className={`px-3 py-1 rounded-full whitespace-nowrap font-medium transition-all cursor-pointer ${
                                                active
                                                    ? "bg-accent text-accent-fg shadow-sm font-semibold"
                                                    : "bg-card hover:bg-card/80 text-muted hover:text-fg border border-border"
                                            }`}
                                        >
                                            {cat.label}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Popular Searches when no query is typed */}
                            {!query.trim() && (
                                <div className="mt-3 flex items-center gap-2 overflow-x-auto pb-1 text-xs text-muted">
                                    <span className="flex items-center gap-1 font-medium text-fg whitespace-nowrap">
                                        <TrendingUp className="h-3.5 w-3.5 text-accent" />
                                        Popular:
                                    </span>
                                    <div className="flex items-center gap-1.5 flex-wrap">
                                        {POPULAR_SEARCHES.map((term) => (
                                            <button
                                                key={term}
                                                type="button"
                                                onClick={() =>
                                                    handlePopularClick(term)
                                                }
                                                className="px-2.5 py-0.5 rounded-md bg-card/60 hover:bg-card border border-border/70 text-fg/80 hover:text-accent hover:border-accent text-xs transition-colors cursor-pointer"
                                            >
                                                {term}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Results Area */}
                            <div className="flex-1 overflow-y-auto mt-4 pr-1 min-h-[160px] max-h-[56vh]">
                                {isLoading && !hasLoadedInitial ? (
                                    /* Initial Skeleton */
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 py-2">
                                        {[1, 2, 3, 4, 5, 6, 7, 8].map((n) => (
                                            <div
                                                key={n}
                                                className="flex gap-3 p-3 rounded-lg bg-card/40 border border-border/50 animate-pulse"
                                            >
                                                <div className="w-16 h-16 rounded-md bg-muted/20 flex-shrink-0" />
                                                <div className="flex-1 space-y-2">
                                                    <div className="h-3 w-1/3 bg-muted/20 rounded" />
                                                    <div className="h-4 w-4/5 bg-muted/20 rounded" />
                                                    <div className="h-3 w-1/2 bg-muted/20 rounded" />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : results.length > 0 ? (
                                    <div>
                                        <div className="pb-2 mb-2 text-xs text-muted">
                                            <span>
                                                {query.trim()
                                                    ? `Found ${totalCount} results for "${query}"`
                                                    : "Featured Computer Products & Hardware"}
                                            </span>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pb-2">
                                            {results.map((product) => (
                                                <Link
                                                    key={product.id}
                                                    href={`/product/${product.slug || product.id}`}
                                                    onClick={onClose}
                                                    className="group relative flex gap-3.5 p-3 rounded-lg bg-card/50 hover:bg-card border border-border/80 hover:border-accent transition-all duration-200 hover:shadow-md cursor-pointer"
                                                >
                                                    {/* Thumbnail */}
                                                    <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-md bg-bg border border-border/60 overflow-hidden flex-shrink-0 flex items-center justify-center p-1 relative">
                                                        {product.images?.[0] ? (
                                                            <img
                                                                src={
                                                                    product
                                                                        .images[0]
                                                                }
                                                                alt={
                                                                    product.name
                                                                }
                                                                className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                                                onError={(
                                                                    e,
                                                                ) => {
                                                                    (
                                                                        e.target as HTMLImageElement
                                                                    ).src =
                                                                        "/images/placeholder.png";
                                                                }}
                                                            />
                                                        ) : (
                                                            <Cpu className="h-8 w-8 text-muted" />
                                                        )}
                                                    </div>

                                                    {/* Information */}
                                                    <div className="flex-1 min-w-0 flex flex-col justify-between">
                                                        <div>
                                                            <div className="flex items-center gap-1.5 flex-wrap">
                                                                {product.brand && (
                                                                    <span className="text-[10px] font-bold text-accent uppercase tracking-wider">
                                                                        {
                                                                            product.brand
                                                                        }
                                                                    </span>
                                                                )}
                                                                <span className="text-[10px] text-muted truncate">
                                                                    {product.subCategory ||
                                                                        product.category}
                                                                </span>
                                                            </div>
                                                            <h4 className="text-xs sm:text-sm font-semibold text-fg line-clamp-2 mt-0.5 group-hover:text-accent transition-colors">
                                                                {product.name}
                                                            </h4>
                                                        </div>

                                                        <div className="mt-1 flex items-baseline justify-between gap-1">
                                                            <div className="flex items-baseline gap-1.5">
                                                                <span className="text-xs sm:text-sm font-bold text-accent">
                                                                    {formatPrice(
                                                                        product.price,
                                                                    )}
                                                                </span>
                                                                {product.originalPrice && (
                                                                    <span className="text-[11px] text-muted line-through">
                                                                        {formatPrice(
                                                                            product.originalPrice,
                                                                        )}
                                                                    </span>
                                                                )}
                                                            </div>

                                                            <span
                                                                className={`text-[10px] font-medium flex items-center gap-1 ${
                                                                    product.inStock
                                                                        ? "text-emerald-500"
                                                                        : "text-muted"
                                                                }`}
                                                            >
                                                                <span
                                                                    className={`w-1.5 h-1.5 rounded-full ${
                                                                        product.inStock
                                                                            ? "bg-emerald-500"
                                                                            : "bg-muted"
                                                                    }`}
                                                                />
                                                                {product.inStock
                                                                    ? "In Stock"
                                                                    : "Out of Stock"}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                ) : (
                                    /* Empty State */
                                    <div className="text-center py-12 px-4 flex flex-col items-center justify-center">
                                        <div className="w-14 h-14 rounded-full bg-card border border-border flex items-center justify-center mb-3 text-muted">
                                            <Search className="h-6 w-6" />
                                        </div>
                                        <h3 className="text-base font-bold text-fg">
                                            No accessories or components found
                                        </h3>
                                        <p className="text-sm text-muted mt-1 max-w-md">
                                            We couldn't find any products
                                            matching &ldquo;
                                            <span className="text-fg font-medium">
                                                {query}
                                            </span>
                                            &rdquo;. Try searching for general
                                            terms like &ldquo;mouse&rdquo;,
                                            &ldquo;keyboard&rdquo;, or browse
                                            our catalog.
                                        </p>
                                        <div className="mt-5 flex gap-3">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setQuery("");
                                                    setSelectedCategory("all");
                                                }}
                                                className="px-4 py-2 rounded-md bg-card border border-border text-xs font-semibold text-fg hover:border-accent transition-colors"
                                            >
                                                Clear Search
                                            </button>
                                            <Link
                                                href="/shop"
                                                onClick={onClose}
                                                className="px-4 py-2 rounded-md bg-accent text-accent-fg text-xs font-bold uppercase tracking-wider hover:opacity-90 transition-opacity inline-flex items-center gap-1.5"
                                            >
                                                Browse All Products
                                                <ArrowRight className="h-3.5 w-3.5" />
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Footer Banner */}
                            {results.length > 0 && (
                                <div className="mt-3 pt-3 border-t border-border flex items-center justify-end">
                                    <button
                                        type="button"
                                        onClick={() => handleSearchSubmit()}
                                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-accent text-accent-fg font-bold uppercase tracking-wider text-xs hover:opacity-95 transition-opacity cursor-pointer"
                                    >
                                        View all in Shop Catalog
                                        <ArrowRight className="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            )}
                        </div>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
}
