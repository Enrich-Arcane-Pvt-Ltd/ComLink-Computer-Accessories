import { ProductCard, ProductCardData } from "@/Components/product/ProductCard";
import AppLayout from "@/Layouts/AppLayout";
import { formatPrice, CURRENCY_SYMBOL } from "@/utils/currency";
import { Head, Link, router } from "@inertiajs/react";
import {
    ChevronDown,
    ChevronRight,
    Filter,
    Grid3X3,
    Layers,
    LayoutGrid,
    RotateCcw,
    Search,
    ShieldCheck,
    SlidersHorizontal,
    Truck,
    Wrench,
    X,
} from "lucide-react";
import React, { useMemo, useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Types                                                                     */
/* -------------------------------------------------------------------------- */

export interface SubCategoryData {
    id: number | string;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    count?: number;
}

export interface CategoryData {
    id: number | string;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    count?: number;
    subcategories: SubCategoryData[];
}

export interface FilterFacet {
    id: string;
    label: string;
    options: string[];
}

export interface CategoryFilterSchema {
    hasCondition: boolean; // false for thermal-paste & software
    facets: FilterFacet[];
}

export interface BrandItem {
    id: number;
    name: string;
    slug: string;
}

interface ShopProps {
    categories: CategoryData[];
    activeCategory?: CategoryData | null;
    activeSubCategory?: SubCategoryData | null;
    products: ProductCardData[];
    dynamicFacets?: FilterFacet[];
    brands?: BrandItem[];
    pageMaxPrice?: number;
    filters: Record<string, any>;
}

/* -------------------------------------------------------------------------- */
/*  Price Range Bar Component                                                 */
/* -------------------------------------------------------------------------- */

function PriceBarSlider({
    minPrice,
    maxPrice,
    maxLimit = 100000,
    onMinChange,
    onMaxChange,
    onApply,
    onApplyPreset,
    onReset,
}: {
    minPrice: string | number;
    maxPrice: string | number;
    maxLimit?: number;
    onMinChange: (val: string) => void;
    onMaxChange: (val: string) => void;
    onApply: () => void;
    onApplyPreset?: (min: string, max: string) => void;
    onReset: () => void;
}) {
    const minLimit = 0;
    const effectiveMax = Math.max(10, Math.ceil(maxLimit));
    const step = Math.max(1, Math.round(effectiveMax / 100));

    const parsedMin = minPrice === "" ? minLimit : Math.max(minLimit, Math.min(effectiveMax, Number(minPrice)));
    const parsedMax = maxPrice === "" ? effectiveMax : Math.max(minLimit, Math.min(effectiveMax, Number(maxPrice)));

    const leftPercent = (parsedMin / effectiveMax) * 100;
    const rightPercent = (parsedMax / effectiveMax) * 100;
    const fillWidth = Math.max(0, rightPercent - leftPercent);

    return (
        <div className="space-y-4 pt-1">
            {/* Visual Bar Track with Dual Thumbs */}
            <div className="relative w-full h-7 flex items-center select-none">
                {/* Background Track */}
                <div className="absolute w-full h-2 bg-muted/20 dark:bg-muted/30 rounded-full" />

                {/* Active Colored Fill Bar */}
                <div
                    className="absolute h-2 bg-accent rounded-full transition-all pointer-events-none"
                    style={{
                        left: `${leftPercent}%`,
                        width: `${fillWidth}%`,
                    }}
                />

                {/* Min Slider Input (invisible thumb overlay with pointer-events on thumb) */}
                <input
                    type="range"
                    min={minLimit}
                    max={effectiveMax}
                    step={step}
                    value={parsedMin}
                    onChange={(e) => {
                        const val = Math.min(Number(e.target.value), parsedMax - step);
                        onMinChange(val <= 0 ? "" : val.toString());
                    }}
                    onMouseUp={onApply}
                    onTouchEnd={onApply}
                    className="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-moz-range-thumb]:pointer-events-auto z-30"
                />

                {/* Max Slider Input (invisible thumb overlay with pointer-events on thumb) */}
                <input
                    type="range"
                    min={minLimit}
                    max={effectiveMax}
                    step={step}
                    value={parsedMax}
                    onChange={(e) => {
                        const val = Math.max(Number(e.target.value), parsedMin + step);
                        onMaxChange(val >= effectiveMax ? "" : val.toString());
                    }}
                    onMouseUp={onApply}
                    onTouchEnd={onApply}
                    className="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-moz-range-thumb]:pointer-events-auto z-30"
                />

                {/* Styled Visual Thumb: Min */}
                <div
                    className="absolute w-4 h-4 rounded-full bg-accent border-2 border-card shadow-md -translate-x-1/2 pointer-events-none z-20 transition-all"
                    style={{ left: `${leftPercent}%` }}
                />

                {/* Styled Visual Thumb: Max */}
                <div
                    className="absolute w-4 h-4 rounded-full bg-accent border-2 border-card shadow-md -translate-x-1/2 pointer-events-none z-20 transition-all"
                    style={{ left: `${rightPercent}%` }}
                />
            </div>

            {/* Price values text along bar */}
            <div className="flex items-center justify-between text-[11px] text-muted font-medium px-0.5">
                <span>{formatPrice(0, 0)}</span>
                <span className="text-accent font-semibold">
                    {formatPrice(parsedMin, 0)} - {formatPrice(parsedMax, 0)}
                </span>
                <span>{formatPrice(effectiveMax, 0)}</span>
            </div>

            {/* Input boxes */}
            <div className="grid grid-cols-2 gap-2">
                <div>
                    <span className="text-[10px] text-muted block mb-1 font-medium">Min Price ({CURRENCY_SYMBOL.trim()})</span>
                    <input
                        type="number"
                        placeholder="0"
                        min={0}
                        max={effectiveMax}
                        value={minPrice}
                        onChange={(e) => onMinChange(e.target.value)}
                        className="w-full bg-bg border border-border rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                    />
                </div>
                <div>
                    <span className="text-[10px] text-muted block mb-1 font-medium">Max Price ({CURRENCY_SYMBOL.trim()})</span>
                    <input
                        type="number"
                        placeholder={effectiveMax.toString()}
                        min={0}
                        max={effectiveMax}
                        value={maxPrice}
                        onChange={(e) => onMaxChange(e.target.value)}
                        className="w-full bg-bg border border-border rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                    />
                </div>
            </div>

            {/* Quick Price Ranges */}
            {effectiveMax > 100 && (
                <div className="flex flex-wrap gap-1.5 pt-0.5">
                    {(() => {
                        const roundNice = (num: number) => {
                            if (num >= 50000) return Math.round(num / 5000) * 5000;
                            if (num >= 10000) return Math.round(num / 1000) * 1000;
                            if (num >= 1000) return Math.round(num / 500) * 500;
                            return Math.round(num / 50) * 50;
                        };
                        const q1 = roundNice(effectiveMax * 0.25);
                        const q2 = roundNice(effectiveMax * 0.5);
                        const q3 = roundNice(effectiveMax * 0.75);

                        return [
                            { label: `< ${formatPrice(q1, 0)}`, min: "0", max: String(q1) },
                            { label: `${formatPrice(q1, 0)} - ${formatPrice(q2, 0)}`, min: String(q1), max: String(q2) },
                            { label: `${formatPrice(q2, 0)} - ${formatPrice(q3, 0)}`, min: String(q2), max: String(q3) },
                            { label: `${formatPrice(q3, 0)}+`, min: String(q3), max: String(effectiveMax) },
                        ].map((preset) => (
                            <button
                                key={preset.label}
                                type="button"
                                onClick={() => {
                                    onMinChange(preset.min);
                                    onMaxChange(preset.max);
                                    if (onApplyPreset) {
                                        onApplyPreset(preset.min, preset.max);
                                    } else {
                                        onApply();
                                    }
                                }}
                                className="text-[10px] px-2 py-1 bg-bg border border-border/80 rounded-md text-slate-700 dark:text-muted hover:text-cyan-600 dark:hover:text-fg hover:border-accent font-medium transition-colors"
                            >
                                {preset.label}
                            </button>
                        ));
                    })()}
                </div>
            )}

            <button
                type="button"
                onClick={onApply}
                className="w-full py-1.5 bg-card hover:bg-accent hover:text-white dark:hover:text-slate-950 border border-border hover:border-accent rounded-lg text-xs font-semibold transition-all mt-1 shadow-sm"
            >
                Apply Price Filter
            </button>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Shop Component                                                       */
/* -------------------------------------------------------------------------- */

export default function ShopIndex({
    categories = [],
    activeCategory = null,
    activeSubCategory = null,
    products = [],
    dynamicFacets = [],
    brands = [],
    pageMaxPrice = 0,
    filters = {},
}: ShopProps) {
    // Current maximum product price for this page scope (defaults cleanly if no products)
    const computedMaxPrice = useMemo(() => {
        if (pageMaxPrice && pageMaxPrice > 0) {
            return Math.ceil(pageMaxPrice);
        }
        if (products && products.length > 0) {
            const highest = Math.max(...products.map((p) => p.latestPrice || p.originalPrice || 0));
            if (highest > 0) return Math.ceil(highest);
        }
        return 100000;
    }, [pageMaxPrice, products]);

    // Current active filter schema based on dynamic specifications
    const activeSchema = useMemo<CategoryFilterSchema>(() => {
        const hasCondition = activeCategory?.slug !== "thermal-paste" && activeCategory?.slug !== "software";
        return {
            hasCondition,
            facets: dynamicFacets || [],
        };
    }, [activeCategory, dynamicFacets]);

    // Local filter state
    const [condition, setCondition] = useState<string>(filters.condition || "all");
    const [searchTerm, setSearchTerm] = useState(filters.q || "");
    const [minPrice, setMinPrice] = useState(filters.min_price || "");
    const [maxPrice, setMaxPrice] = useState(filters.max_price || "");
    const [inStockOnly, setInStockOnly] = useState(Boolean(filters.in_stock));
    const [outOfStockOnly, setOutOfStockOnly] = useState(Boolean(filters.out_of_stock));
    const [brandSearch, setBrandSearch] = useState("");
    const [brandExpanded, setBrandExpanded] = useState(true);
    const [facetSearches, setFacetSearches] = useState<Record<string, string>>({});
    const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
    const [columns, setColumns] = useState<3 | 4>(3);

    // Track which facet accordions are expanded
    const [expandedFacets, setExpandedFacets] = useState<Record<string, boolean>>(() => {
        const init: Record<string, boolean> = { categories: true };
        activeSchema.facets.forEach((f) => {
            init[f.id] = true;
        });
        return init;
    });

    React.useEffect(() => {
        setExpandedFacets((prev) => {
            const updated: Record<string, boolean> = { ...prev, categories: true };
            activeSchema.facets.forEach((f) => {
                if (updated[f.id] === undefined) {
                    updated[f.id] = true;
                }
            });
            return updated;
        });
    }, [activeSchema]);

    const toggleFacet = (id: string) => {
        setExpandedFacets((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    // Track which category dropdowns are open in the sidebar
    const [expandedCategories, setExpandedCategories] = useState<Record<string, boolean>>(() => {
        const initial: Record<string, boolean> = {};
        if (activeCategory?.slug) {
            initial[activeCategory.slug] = true;
        }
        return initial;
    });

    const toggleAccordion = (slug: string) => {
        setExpandedCategories((prev) => ({
            ...prev,
            [slug]: !prev[slug],
        }));
    };

    // Helper to check if a facet option is selected
    const isOptionSelected = (facetId: string, optionValue: string): boolean => {
        const current = filters[facetId];
        if (!current) return false;
        if (Array.isArray(current)) {
            return current.includes(optionValue);
        }
        return current.toString().split(",").includes(optionValue);
    };

    // Helper to check if a brand is selected
    const isBrandSelected = (brandName: string, brandSlug: string): boolean => {
        const current = filters.brand;
        if (!current) return false;
        const list = Array.isArray(current) ? current : current.toString().split(",");
        return list.includes(brandName) || list.includes(brandSlug);
    };

    // Apply URL filter updates via Inertia
    const applyFilters = (newParams: Record<string, any>) => {
        const query: Record<string, any> = {
            category: activeCategory?.slug || undefined,
            subcategory: activeSubCategory?.slug || undefined,
            condition: condition !== "all" ? condition : undefined,
            q: searchTerm || undefined,
            sort: filters.sort || undefined,
            min_price: minPrice || undefined,
            max_price: maxPrice || undefined,
            in_stock: inStockOnly ? "1" : undefined,
            out_of_stock: outOfStockOnly ? "1" : undefined,
            ...filters,
            ...newParams,
        };

        // Clean empty values
        Object.keys(query).forEach((key) => {
            if (query[key] === undefined || query[key] === "" || query[key] === null) {
                delete query[key];
            }
        });

        router.get("/shop", query, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Toggle a brand checkbox
    const handleToggleBrand = (brandName: string) => {
        const current = filters.brand;
        let values: string[] = [];
        if (Array.isArray(current)) {
            values = [...current];
        } else if (typeof current === "string" && current.length > 0) {
            values = current.split(",");
        }

        if (values.includes(brandName)) {
            values = values.filter((v) => v !== brandName);
        } else {
            values.push(brandName);
        }

        applyFilters({
            brand: values.length > 0 ? values.join(",") : undefined,
        });
    };

    // Toggle a checkbox inside any facet
    const handleToggleFacetOption = (facetId: string, optionValue: string) => {
        const current = filters[facetId];
        let values: string[] = [];
        if (Array.isArray(current)) {
            values = [...current];
        } else if (typeof current === "string" && current.length > 0) {
            values = current.split(",");
        }

        if (values.includes(optionValue)) {
            values = values.filter((v) => v !== optionValue);
        } else {
            values.push(optionValue);
        }

        applyFilters({
            [facetId]: values.length > 0 ? values.join(",") : undefined,
        });
    };

    const handleConditionChange = (newCondition: string) => {
        setCondition(newCondition);
        applyFilters({
            condition: newCondition !== "all" ? newCondition : undefined,
        });
    };

    const handleSortChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        applyFilters({ sort: e.target.value });
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters({ q: searchTerm });
    };

    const clearFilters = () => {
        setCondition("all");
        setSearchTerm("");
        setMinPrice("");
        setMaxPrice("");
        setInStockOnly(false);
        setOutOfStockOnly(false);
        router.get(
            "/shop",
            {
                category: activeCategory?.slug || undefined,
                subcategory: activeSubCategory?.slug || undefined,
            },
            { preserveState: false }
        );
    };

    // Collect all applied filter tags for top pill bar
    const appliedPills = useMemo(() => {
        const pills: { key: string; label: string; onRemove: () => void }[] = [];

        if (condition && condition !== "all") {
            pills.push({
                key: `condition-${condition}`,
                label: `Condition: ${condition === "brandnew" ? "Brandnew" : "Used"}`,
                onRemove: () => handleConditionChange("all"),
            });
        }

        if (inStockOnly) {
            pills.push({
                key: "in_stock",
                label: "In Stock Only",
                onRemove: () => {
                    setInStockOnly(false);
                    applyFilters({ in_stock: undefined });
                },
            });
        }

        if (outOfStockOnly) {
            pills.push({
                key: "out_of_stock",
                label: "Out of Stock",
                onRemove: () => {
                    setOutOfStockOnly(false);
                    applyFilters({ out_of_stock: undefined });
                },
            });
        }

        if (minPrice || maxPrice) {
            pills.push({
                key: "price_range",
                label: `Price: ${formatPrice(Number(minPrice) || 0, 0)} - ${maxPrice ? formatPrice(Number(maxPrice), 0) : formatPrice(computedMaxPrice, 0)}`,
                onRemove: () => {
                    setMinPrice("");
                    setMaxPrice("");
                    applyFilters({ min_price: undefined, max_price: undefined });
                },
            });
        }

        if (searchTerm) {
            pills.push({
                key: "search_term",
                label: `Search: "${searchTerm}"`,
                onRemove: () => {
                    setSearchTerm("");
                    applyFilters({ q: undefined });
                },
            });
        }

        if (filters.brand) {
            const rawBrands = Array.isArray(filters.brand) ? filters.brand : filters.brand.toString().split(",");
            rawBrands.forEach((b: string) => {
                const trimmed = b.trim();
                if (trimmed) {
                    pills.push({
                        key: `brand-${trimmed}`,
                        label: `Brand: ${trimmed}`,
                        onRemove: () => handleToggleBrand(trimmed),
                    });
                }
            });
        }

        activeSchema.facets.forEach((facet) => {
            const raw = filters[facet.id];
            if (raw) {
                const values = raw.toString().split(",");
                values.forEach((val: string) => {
                    pills.push({
                        key: `${facet.id}-${val}`,
                        label: `${facet.label}: ${val}`,
                        onRemove: () => handleToggleFacetOption(facet.id, val),
                    });
                });
            }
        });

        return pills;
    }, [condition, inStockOnly, outOfStockOnly, minPrice, maxPrice, searchTerm, filters, activeSchema, computedMaxPrice]);

    // Page title
    const pageTitle = useMemo(() => {
        if (activeSubCategory && activeCategory) {
            return `${activeSubCategory.name} - ${activeCategory.name} | WikiStore`;
        }
        if (activeCategory) {
            return `${activeCategory.name} | WikiStore`;
        }
        return "All Categories & Products | WikiStore";
    }, [activeCategory, activeSubCategory]);

    // Active subcategories list
    const currentSubcategories = activeCategory?.subcategories || [];

    /* ---------------------------------------------------------------------- */
    /*  Reusable Filter Controls Component                                    */
    /* ---------------------------------------------------------------------- */
    const renderFilterControls = (isMobile = false) => (
        <div className="space-y-5">
            {/* 1. Condition Selector (All / Brandnew / Used) - Only if enabled for this category */}
            {activeSchema.hasCondition && (
                <div className="bg-card rounded-2xl border border-border p-4 shadow-sm">
                    <label className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 block mb-3">
                        Condition
                    </label>
                    <div className="grid grid-cols-3 gap-1.5 p-1 bg-bg/80 rounded-xl border border-border/80">
                        <button
                            type="button"
                            onClick={() => handleConditionChange("all")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "all"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10"
                            }`}
                        >
                            All
                        </button>
                        <button
                            type="button"
                            onClick={() => handleConditionChange("brandnew")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "brandnew"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10"
                            }`}
                        >
                            Brandnew
                        </button>
                        <button
                            type="button"
                            onClick={() => handleConditionChange("used")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "used"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10"
                            }`}
                        >
                            Used
                        </button>
                    </div>
                </div>
            )}

            {/* 2. Price Range Bar & Visual Slider */}
            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm space-y-3">
                <div className="flex items-center justify-between">
                    <label className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        Price Range
                    </label>
                    {(minPrice || maxPrice) && (
                        <button
                            type="button"
                            onClick={() => {
                                setMinPrice("");
                                setMaxPrice("");
                                applyFilters({ min_price: undefined, max_price: undefined });
                            }}
                            className="text-[10px] text-accent hover:underline font-medium"
                        >
                            Reset
                        </button>
                    )}
                </div>

                <PriceBarSlider
                    minPrice={minPrice}
                    maxPrice={maxPrice}
                    maxLimit={computedMaxPrice}
                    onMinChange={setMinPrice}
                    onMaxChange={setMaxPrice}
                    onApply={() => applyFilters({ min_price: minPrice, max_price: maxPrice })}
                    onApplyPreset={(min, max) => applyFilters({ min_price: min, max_price: max })}
                    onReset={() => {
                        setMinPrice("");
                        setMaxPrice("");
                        applyFilters({ min_price: undefined, max_price: undefined });
                    }}
                />
            </div>

            {/* 3. Availability Checkboxes */}
            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm space-y-2.5">
                <label className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 block">
                    Availability
                </label>
                <div className="space-y-2">
                    <label className="flex items-center gap-2.5 cursor-pointer text-xs text-fg select-none group">
                        <input
                            type="checkbox"
                            checked={inStockOnly}
                            onChange={(e) => {
                                const val = e.target.checked;
                                setInStockOnly(val);
                                applyFilters({ in_stock: val ? "1" : undefined });
                            }}
                            className="rounded border-border text-accent focus:ring-accent w-4 h-4 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                        />
                        <span className="flex items-center gap-1.5 text-fg group-hover:text-accent transition-colors">
                            <span className="w-2 h-2 rounded-full bg-emerald-500 inline-block" />
                            In Stock
                        </span>
                    </label>

                    <label className="flex items-center gap-2.5 cursor-pointer text-xs text-fg select-none group">
                        <input
                            type="checkbox"
                            checked={outOfStockOnly}
                            onChange={(e) => {
                                const val = e.target.checked;
                                setOutOfStockOnly(val);
                                applyFilters({ out_of_stock: val ? "1" : undefined });
                            }}
                            className="rounded border-border text-accent focus:ring-accent w-4 h-4 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                        />
                        <span className="flex items-center gap-1.5 text-fg group-hover:text-accent transition-colors">
                            <span className="w-2 h-2 rounded-full bg-rose-500 inline-block" />
                            Out of Stock
                        </span>
                    </label>
                </div>
            </div>

            {/* 4. Brand Filter - Loaded dynamically from real brands added by admin in database */}
            {brands.length > 0 && (() => {
                const search = brandSearch.toLowerCase();
                const filteredBrands = brands.filter((b) =>
                    b.name.toLowerCase().includes(search)
                );
                const selectedBrandCount = brands.filter((b) =>
                    isBrandSelected(b.name, b.slug)
                ).length;

                return (
                    <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setBrandExpanded((prev) => !prev)}
                            className="w-full flex items-center justify-between p-4 text-left transition-colors hover:bg-muted/10 group"
                        >
                            <div className="flex items-center gap-2">
                                <span className="font-display font-bold text-xs uppercase tracking-wider text-fg group-hover:text-accent transition-colors">
                                    Brand
                                </span>
                                {selectedBrandCount > 0 && (
                                    <span className="text-[10px] px-1.5 py-0.2 bg-accent text-white dark:text-slate-950 font-bold rounded-full">
                                        {selectedBrandCount}
                                    </span>
                                )}
                            </div>
                            <ChevronDown
                                className={`w-4 h-4 text-muted group-hover:text-fg transition-transform duration-200 ${
                                    brandExpanded ? "rotate-180" : ""
                                }`}
                            />
                        </button>

                        {brandExpanded && (
                            <div className="px-4 pb-4 pt-1 space-y-2 border-t border-border/60">
                                {brands.length > 6 && (
                                    <div className="relative mb-2">
                                        <Search className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-muted" />
                                        <input
                                            type="text"
                                            placeholder="Search brands..."
                                            value={brandSearch}
                                            onChange={(e) => setBrandSearch(e.target.value)}
                                            className="w-full bg-bg border border-border rounded-lg pl-7 pr-2 py-1 text-[11px] text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                                        />
                                    </div>
                                )}

                                <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1 scrollbar-thin">
                                    {filteredBrands.length > 0 ? (
                                        filteredBrands.map((b) => {
                                            const checked = isBrandSelected(b.name, b.slug);
                                            return (
                                                <label
                                                    key={b.id}
                                                    className="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 select-none py-0.5 group"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={checked}
                                                        onChange={() => handleToggleBrand(b.name)}
                                                        className="rounded border-border text-accent focus:ring-accent w-3.5 h-3.5 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                                                    />
                                                    <span
                                                        className={`truncate transition-colors ${
                                                            checked
                                                                ? "text-cyan-600 dark:text-cyan-400 font-semibold"
                                                                : "group-hover:text-cyan-600 dark:group-hover:text-cyan-400"
                                                        }`}
                                                    >
                                                        {b.name}
                                                    </span>
                                                </label>
                                            );
                                        })
                                    ) : (
                                        <p className="text-[11px] text-muted py-1 italic">
                                            No brands match search
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                );
            })()}

            {/* 5. Main Specification Titles with their added values for selections */}
            {activeSchema.facets.map((facet) => {
                const isExpanded = Boolean(expandedFacets[facet.id]);
                const searchVal = facetSearches[facet.id] || "";
                const filteredOptions = facet.options.filter((opt) =>
                    opt.toLowerCase().includes(searchVal.toLowerCase())
                );
                const selectedCount = facet.options.filter((opt) =>
                    isOptionSelected(facet.id, opt)
                ).length;

                return (
                    <div
                        key={facet.id}
                        className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden"
                    >
                        {/* Facet Header */}
                        <button
                            type="button"
                            onClick={() => toggleFacet(facet.id)}
                            className="w-full flex items-center justify-between p-4 text-left transition-colors hover:bg-muted/10 group"
                        >
                            <div className="flex items-center gap-2">
                                <span className="font-display font-bold text-xs uppercase tracking-wider text-fg group-hover:text-accent transition-colors">
                                    {facet.label}
                                </span>
                                {selectedCount > 0 && (
                                    <span className="text-[10px] px-1.5 py-0.2 bg-accent text-white dark:text-slate-950 font-bold rounded-full">
                                        {selectedCount}
                                    </span>
                                )}
                            </div>
                            <ChevronDown
                                className={`w-4 h-4 text-muted group-hover:text-fg transition-transform duration-200 ${
                                    isExpanded ? "rotate-180" : ""
                                }`}
                            />
                        </button>

                        {/* Facet Body */}
                        {isExpanded && (
                            <div className="px-4 pb-4 pt-1 space-y-2 border-t border-border/60">
                                {facet.options.length > 6 && (
                                    <div className="relative mb-2">
                                        <Search className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-muted" />
                                        <input
                                            type="text"
                                            placeholder={`Filter ${facet.label.toLowerCase()}...`}
                                            value={searchVal}
                                            onChange={(e) =>
                                                setFacetSearches((prev) => ({
                                                    ...prev,
                                                    [facet.id]: e.target.value,
                                                }))
                                            }
                                            className="w-full bg-bg border border-border rounded-lg pl-7 pr-2 py-1 text-[11px] text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                                        />
                                    </div>
                                )}

                                <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1 scrollbar-thin">
                                    {filteredOptions.length > 0 ? (
                                        filteredOptions.map((opt) => {
                                            const checked = isOptionSelected(facet.id, opt);
                                            return (
                                                <label
                                                    key={opt}
                                                    className="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 select-none py-0.5 group"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={checked}
                                                        onChange={() =>
                                                            handleToggleFacetOption(facet.id, opt)
                                                        }
                                                        className="rounded border-border text-accent focus:ring-accent w-3.5 h-3.5 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                                                    />
                                                    <span
                                                        className={`truncate transition-colors ${
                                                            checked
                                                                ? "text-cyan-600 dark:text-cyan-400 font-semibold"
                                                                : "group-hover:text-cyan-600 dark:group-hover:text-cyan-400"
                                                        }`}
                                                    >
                                                        {opt}
                                                    </span>
                                                </label>
                                            );
                                        })
                                    ) : (
                                        <p className="text-[11px] text-muted py-1 italic">
                                            No options match search
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                );
            })}

            {/* Clear All Filters Button */}
            {appliedPills.length > 0 && (
                <button
                    type="button"
                    onClick={clearFilters}
                    className="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-semibold text-rose-500 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all"
                >
                    <RotateCcw className="w-3.5 h-3.5" />
                    <span>Clear All Filters</span>
                </button>
            )}
        </div>
    );

    return (
        <AppLayout>
            <Head title={pageTitle}>
                <meta
                    name="description"
                    content={
                        activeCategory?.description ||
                        "Explore laptops, PC components, peripherals, displays, gaming gear, and computer accessories."
                    }
                />
            </Head>

            <div className="min-h-screen bg-bg text-fg">
                {/* ------------------------------------------------------------------ */}
                {/*  Header / Category Banner                                          */}
                {/* ------------------------------------------------------------------ */}
                <div className="relative border-b border-border bg-gradient-to-b from-card/80 to-bg py-8 md:py-10">
                    <div className="container mx-auto px-4 md:px-6">
                        {/* Breadcrumbs */}
                        <nav className="flex items-center gap-2 text-xs md:text-sm text-muted mb-4 flex-wrap">
                            {!activeCategory ? (
                                <span className="text-accent font-semibold">
                                    Shop
                                </span>
                            ) : (
                                <>
                                    <Link
                                        href="/shop"
                                        className="hover:text-accent transition-colors text-muted"
                                    >
                                        Shop
                                    </Link>
                                    <ChevronRight className="w-3.5 h-3.5 opacity-50" />
                                    <Link
                                        href={`/shop?category=${activeCategory.slug}`}
                                        className={`hover:text-accent transition-colors ${
                                            !activeSubCategory ? "text-accent font-semibold" : "text-muted"
                                        }`}
                                    >
                                        {activeCategory.name}
                                    </Link>
                                    {activeSubCategory && (
                                        <>
                                            <ChevronRight className="w-3.5 h-3.5 opacity-50" />
                                            <span className="text-accent font-semibold">
                                                {activeSubCategory.name}
                                            </span>
                                        </>
                                    )}
                                </>
                            )}
                        </nav>

                        {/* Banner Content (Cleaned: AI icon section and top right badge row removed) */}
                        <div className="max-w-3xl">
                            <h1 className="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display tracking-tight text-fg">
                                {activeSubCategory
                                    ? activeSubCategory.name
                                    : activeCategory
                                    ? activeCategory.name
                                    : "ALL CATEGORIES & PRODUCTS"}
                            </h1>
                            <p className="text-sm md:text-base text-muted mt-2 leading-relaxed">
                                {activeSubCategory?.description ||
                                    activeCategory?.description ||
                                    "Browse verified selection of gaming hardware, workstation components, ultrabooks, and everyday peripherals with official warranty."}
                            </p>
                        </div>

                        {/* Horizontal Subcategory Bar (if category has subcategories) */}
                        {currentSubcategories.length > 0 && (
                            <div className="mt-6 pt-5 border-t border-border/60">
                                <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mr-2 shrink-0">
                                        Subcategories:
                                    </span>
                                    <Link
                                        href={`/shop?category=${activeCategory?.slug}`}
                                        className={`px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 border ${
                                            !activeSubCategory
                                                ? "bg-accent text-white dark:text-slate-950 border-accent shadow-sm shadow-cyan-500/20"
                                                : "bg-white dark:bg-card/90 border-slate-300 dark:border-border/90 text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:border-cyan-500 hover:bg-cyan-50/50 dark:hover:bg-cyan-950/20 shadow-xs"
                                        }`}
                                    >
                                        All {activeCategory?.name}
                                    </Link>
                                    {currentSubcategories.map((sub) => {
                                        const isSelected = activeSubCategory?.slug === sub.slug;
                                        return (
                                            <Link
                                                key={sub.slug}
                                                href={`/shop?category=${activeCategory?.slug}&subcategory=${sub.slug}`}
                                                className={`px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 border flex items-center gap-1.5 ${
                                                    isSelected
                                                        ? "bg-accent text-white dark:text-slate-950 border-accent shadow-sm shadow-cyan-500/20"
                                                        : "bg-white dark:bg-card/90 border-slate-300 dark:border-border/90 text-slate-700 dark:text-slate-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:border-cyan-500 hover:bg-cyan-50/50 dark:hover:bg-cyan-950/20 shadow-xs"
                                                }`}
                                            >
                                                <span>{sub.name}</span>
                                                {typeof sub.count === "number" && sub.count > 0 && (
                                                    <span
                                                        className={`text-[10px] px-1.5 py-0.5 rounded-full font-mono font-medium ${
                                                            isSelected
                                                                ? "bg-black/20 text-white dark:text-slate-950"
                                                                : "bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60"
                                                        }`}
                                                    >
                                                        {sub.count}
                                                    </span>
                                                )}
                                            </Link>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* ------------------------------------------------------------------ */}
                {/*  Main Container: Sidebar + Product Grid                            */}
                {/* ------------------------------------------------------------------ */}
                <div className="container mx-auto px-4 md:px-6 py-8">
                    <div className="flex flex-col lg:flex-row gap-8 items-start">
                        {/* -------------------------------------------------------------- */}
                        {/*  Desktop Filter Sidebar                                        */}
                        {/* -------------------------------------------------------------- */}
                        <aside className="hidden lg:block w-72 shrink-0 sticky top-24 space-y-6">
                            {/* Categories Tree Navigation */}
                            <div className="bg-card rounded-2xl border border-border/80 p-5 shadow-sm">
                                <div className="flex items-center justify-between pb-3 mb-4 border-b border-border/80">
                                    <h3 className="font-display font-extrabold text-xs tracking-wider uppercase text-fg flex items-center gap-2">
                                        <Layers className="w-3.5 h-3.5 text-cyan-500" />
                                        Categories
                                    </h3>
                                    <Link
                                        href="/shop"
                                        className="text-xs text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 hover:underline font-bold transition-colors"
                                    >
                                        View All
                                    </Link>
                                </div>

                                <div className="space-y-1">
                                    {categories.map((cat) => {
                                        const isCatActive = activeCategory?.slug === cat.slug;
                                        const hasSubs = cat.subcategories && cat.subcategories.length > 0;
                                        const isExpanded = Boolean(expandedCategories[cat.slug]);

                                        return (
                                            <div key={cat.slug} className="rounded-xl overflow-hidden">
                                                <div
                                                    className={`group/cat flex items-center justify-between px-3 py-2 text-xs rounded-xl transition-all cursor-pointer ${
                                                        isCatActive && !activeSubCategory
                                                            ? "bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 font-bold border-l-2 border-cyan-500 dark:border-cyan-400 shadow-sm shadow-cyan-500/10"
                                                            : isCatActive && activeSubCategory
                                                            ? "bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 font-semibold border-l-2 border-cyan-500/60 dark:border-cyan-400/60"
                                                            : "text-slate-800 dark:text-slate-100 font-medium hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400"
                                                    }`}
                                                >
                                                    <Link
                                                        href={`/shop?category=${cat.slug}`}
                                                        className="flex items-center gap-2.5 flex-1 min-w-0"
                                                    >
                                                        {cat.image && (
                                                            <div className="w-5 h-5 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 shrink-0 flex items-center justify-center p-0.5 group-hover/cat:border-cyan-500/50 transition-colors">
                                                                <img
                                                                    src={cat.image}
                                                                    alt={cat.name}
                                                                    className="w-full h-full object-contain"
                                                                />
                                                            </div>
                                                        )}
                                                        <span className="truncate tracking-wide">{cat.name}</span>
                                                    </Link>

                                                    {hasSubs && (
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                toggleAccordion(cat.slug);
                                                            }}
                                                            className="p-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10 transition-colors cursor-pointer"
                                                            aria-label={`Toggle ${cat.name} subcategories`}
                                                        >
                                                            <ChevronDown
                                                                className={`w-3.5 h-3.5 transition-transform duration-200 ${
                                                                    isExpanded ? "rotate-180 text-cyan-600 dark:text-cyan-400" : ""
                                                                }`}
                                                            />
                                                        </button>
                                                    )}
                                                </div>

                                                {/* Subcategories list */}
                                                {hasSubs && isExpanded && (
                                                    <div className="ml-6 pl-2.5 my-1.5 border-l-2 border-slate-200 dark:border-slate-800 space-y-1 py-1">
                                                        {cat.subcategories.map((sub) => {
                                                            const isSubActive =
                                                                activeCategory?.slug === cat.slug &&
                                                                activeSubCategory?.slug === sub.slug;

                                                            return (
                                                                <Link
                                                                    key={sub.slug}
                                                                    href={`/shop?category=${cat.slug}&subcategory=${sub.slug}`}
                                                                    className={`group/sub flex items-center justify-between px-2.5 py-1.5 text-xs rounded-md transition-all ${
                                                                        isSubActive
                                                                            ? "text-cyan-600 dark:text-cyan-400 font-bold bg-cyan-500/15 border-l-2 border-cyan-500 pl-3 shadow-xs"
                                                                            : "text-slate-700 dark:text-slate-200 font-medium hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10 hover:pl-3"
                                                                    }`}
                                                                >
                                                                    <span className="truncate">{sub.name}</span>
                                                                    {typeof sub.count === "number" && sub.count > 0 && (
                                                                        <span
                                                                            className={`text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded-full transition-colors ${
                                                                                isSubActive
                                                                                    ? "bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/40"
                                                                                    : "bg-slate-100 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 group-hover/sub:border-cyan-500/40 group-hover/sub:text-cyan-600 dark:group-hover/sub:text-cyan-300"
                                                                            }`}
                                                                        >
                                                                            {sub.count}
                                                                        </span>
                                                                    )}
                                                                </Link>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Dynamic Category Specific Filters */}
                            {renderFilterControls(false)}
                        </aside>

                        {/* -------------------------------------------------------------- */}
                        {/*  Right Column: Toolbar & Product Grid                          */}
                        {/* -------------------------------------------------------------- */}
                        <main className="flex-1 w-full space-y-6">
                            {/* Toolbar */}
                            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                                {/* Search Form */}
                                <form onSubmit={handleSearchSubmit} className="relative flex-1 max-w-md">
                                    <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                                    <input
                                        type="text"
                                        placeholder={
                                            activeSubCategory
                                                ? `Search in ${activeSubCategory.name}...`
                                                : activeCategory
                                                ? `Search in ${activeCategory.name}...`
                                                : "Search across all hardware..."
                                        }
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="w-full bg-bg border border-border rounded-xl pl-9 pr-8 py-2 text-xs md:text-sm text-fg placeholder:text-muted focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all"
                                    />
                                    {searchTerm && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setSearchTerm("");
                                                applyFilters({ q: "" });
                                            }}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-fg"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    )}
                                </form>

                                {/* Controls: Filter Toggle (Mobile), Sort, Layout Switcher */}
                                <div className="flex items-center gap-3 justify-between md:justify-end">
                                    {/* Mobile Filter Button */}
                                    <button
                                        type="button"
                                        onClick={() => setMobileFilterOpen(true)}
                                        className="lg:hidden flex items-center gap-2 px-3 py-2 bg-card border border-border rounded-xl text-xs font-semibold text-fg hover:bg-muted/10"
                                    >
                                        <Filter className="w-3.5 h-3.5 text-accent" />
                                        <span>Filters</span>
                                        {appliedPills.length > 0 && (
                                            <span className="w-4 h-4 rounded-full bg-accent text-white dark:text-slate-950 text-[10px] flex items-center justify-center font-bold">
                                                {appliedPills.length}
                                            </span>
                                        )}
                                    </button>

                                    {/* Sort Dropdown */}
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs text-muted hidden sm:inline">Sort:</span>
                                        <select
                                            value={filters.sort || "featured"}
                                            onChange={handleSortChange}
                                            className="bg-bg border border-border rounded-xl px-3 py-2 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent font-medium"
                                        >
                                            <option value="featured">Featured / Best Matches</option>
                                            <option value="newest">Newest Arrivals</option>
                                            <option value="price_asc">Price: Low to High</option>
                                            <option value="price_desc">Price: High to Low</option>
                                        </select>
                                    </div>

                                    {/* Grid Layout Switcher (Desktop) */}
                                    <div className="hidden sm:flex items-center border border-border rounded-xl overflow-hidden bg-bg">
                                        <button
                                            type="button"
                                            onClick={() => setColumns(3)}
                                            className={`p-2 transition-colors ${
                                                columns === 3 ? "bg-accent text-white dark:text-slate-950 font-bold" : "text-muted hover:text-fg hover:bg-muted/10"
                                            }`}
                                            title="3 Columns Grid"
                                        >
                                            <LayoutGrid className="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setColumns(4)}
                                            className={`p-2 transition-colors ${
                                                columns === 4 ? "bg-accent text-white dark:text-slate-950 font-bold" : "text-muted hover:text-fg hover:bg-muted/10"
                                            }`}
                                            title="4 Columns Grid"
                                        >
                                            <Grid3X3 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Applied Filter Pills Bar */}
                            {appliedPills.length > 0 && (
                                <div className="flex items-center gap-2 flex-wrap text-xs bg-card p-3 rounded-2xl border border-border">
                                    <span className="text-muted font-medium mr-1">Active Filters:</span>
                                    {appliedPills.map((pill) => (
                                        <span
                                            key={pill.key}
                                            className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-bg border border-border text-fg text-xs font-medium"
                                        >
                                            <span>{pill.label}</span>
                                            <button
                                                type="button"
                                                onClick={pill.onRemove}
                                                className="text-muted hover:text-rose-500 transition-colors"
                                                title="Remove filter"
                                            >
                                                <X className="w-3 h-3" />
                                            </button>
                                        </span>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={clearFilters}
                                        className="text-xs text-accent hover:underline ml-auto font-medium"
                                    >
                                        Clear all
                                    </button>
                                </div>
                            )}

                            {/* Results Count */}
                            <div className="flex items-center justify-between text-xs text-muted px-1">
                                <span>
                                    Showing <strong className="text-fg">{products.length}</strong> {products.length === 1 ? "product" : "products"}
                                    {activeCategory && ` in ${activeCategory.name}`}
                                    {activeSubCategory && ` > ${activeSubCategory.name}`}
                                </span>
                            </div>

                            {/* Product Cards Grid */}
                            {products.length > 0 ? (
                                <div
                                    className={`grid grid-cols-1 sm:grid-cols-2 ${
                                        columns === 4 ? "xl:grid-cols-4" : "xl:grid-cols-3"
                                    } gap-5`}
                                >
                                    {products.map((product) => (
                                        <ProductCard key={product.id} product={product} />
                                    ))}
                                </div>
                            ) : (
                                /* Empty State */
                                <div className="text-center py-20 px-4 rounded-3xl bg-card border border-border my-8">
                                    <div className="w-16 h-16 rounded-full bg-accent/10 text-accent mx-auto flex items-center justify-center mb-4">
                                        <SlidersHorizontal className="w-8 h-8" />
                                    </div>
                                    <h3 className="font-display font-bold text-xl text-fg mb-2">
                                        No products match your filter criteria
                                    </h3>
                                    <p className="text-sm text-muted max-w-md mx-auto mb-6">
                                        Try adjusting your price range, clearing some filters, or explore our full hardware collection.
                                    </p>
                                    <div className="flex items-center justify-center gap-3">
                                        <button
                                            type="button"
                                            onClick={clearFilters}
                                            className="px-5 py-2.5 bg-accent text-white dark:text-slate-950 font-bold rounded-xl text-xs transition-transform active:scale-95 shadow-md"
                                        >
                                            Reset All Filters
                                        </button>
                                        <Link
                                            href="/shop"
                                            className="px-5 py-2.5 bg-bg border border-border text-fg font-semibold rounded-xl text-xs hover:bg-muted/10 transition-colors"
                                        >
                                            View All Products
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </main>
                    </div>
                </div>

                {/* ------------------------------------------------------------------ */}
                {/*  Mobile Filter Drawer                                              */}
                {/* ------------------------------------------------------------------ */}
                {mobileFilterOpen && (
                    <div className="fixed inset-0 z-50 lg:hidden flex">
                        {/* Backdrop */}
                        <div
                            className="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity"
                            onClick={() => setMobileFilterOpen(false)}
                        />

                        {/* Drawer content */}
                        <div className="relative ml-auto w-full max-w-xs bg-bg h-full p-6 overflow-y-auto shadow-2xl flex flex-col justify-between border-l border-border z-10">
                            <div>
                                <div className="flex items-center justify-between pb-4 border-b border-border mb-6">
                                    <div className="flex items-center gap-2">
                                        <Filter className="w-4 h-4 text-accent" />
                                        <h2 className="font-display font-bold text-base text-fg">Filter Products</h2>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setMobileFilterOpen(false)}
                                        className="p-1 rounded-lg text-muted hover:text-fg"
                                    >
                                        <X className="w-5 h-5" />
                                    </button>
                                </div>

                                {/* Categories Navigation in Mobile Drawer */}
                                <div className="mb-6 pb-6 border-b border-border">
                                    <div className="flex items-center justify-between pb-3 mb-3 border-b border-border/80">
                                        <h3 className="font-display font-extrabold text-xs tracking-wider uppercase text-fg flex items-center gap-2">
                                            <Layers className="w-3.5 h-3.5 text-cyan-500" />
                                            Categories
                                        </h3>
                                        <Link
                                            href="/shop"
                                            onClick={() => setMobileFilterOpen(false)}
                                            className="text-xs text-cyan-600 dark:text-cyan-400 hover:underline font-bold"
                                        >
                                            View All
                                        </Link>
                                    </div>
                                    <div className="space-y-1 max-h-64 overflow-y-auto pr-1 scrollbar-thin">
                                        {categories.map((cat) => {
                                            const isCatActive = activeCategory?.slug === cat.slug;
                                            const hasSubs = cat.subcategories && cat.subcategories.length > 0;
                                            const isExpanded = Boolean(expandedCategories[cat.slug]);

                                            return (
                                                <div key={`m-${cat.slug}`} className="rounded-xl overflow-hidden">
                                                    <div
                                                        className={`group/cat flex items-center justify-between px-3 py-2 text-xs rounded-xl transition-all cursor-pointer ${
                                                            isCatActive && !activeSubCategory
                                                                ? "bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 font-bold border-l-2 border-cyan-500 dark:border-cyan-400 shadow-sm shadow-cyan-500/10"
                                                                : isCatActive && activeSubCategory
                                                                ? "bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 font-semibold border-l-2 border-cyan-500/60 dark:border-cyan-400/60"
                                                                : "text-slate-800 dark:text-slate-100 font-medium hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400"
                                                        }`}
                                                    >
                                                        <Link
                                                            href={`/shop?category=${cat.slug}`}
                                                            onClick={() => setMobileFilterOpen(false)}
                                                            className="flex items-center gap-2.5 flex-1 min-w-0"
                                                        >
                                                            {cat.image && (
                                                                <div className="w-5 h-5 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 shrink-0 flex items-center justify-center p-0.5">
                                                                    <img
                                                                        src={cat.image}
                                                                        alt={cat.name}
                                                                        className="w-full h-full object-contain"
                                                                    />
                                                                </div>
                                                            )}
                                                            <span className="truncate tracking-wide">{cat.name}</span>
                                                        </Link>

                                                        {hasSubs && (
                                                            <button
                                                                type="button"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    toggleAccordion(cat.slug);
                                                                }}
                                                                className="p-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10 transition-colors"
                                                            >
                                                                <ChevronDown
                                                                    className={`w-3.5 h-3.5 transition-transform duration-200 ${
                                                                        isExpanded ? "rotate-180 text-cyan-600 dark:text-cyan-400" : ""
                                                                    }`}
                                                                />
                                                            </button>
                                                        )}
                                                    </div>

                                                    {hasSubs && isExpanded && (
                                                        <div className="ml-6 pl-2.5 my-1.5 border-l-2 border-slate-200 dark:border-slate-800 space-y-1 py-1">
                                                            {cat.subcategories.map((sub) => {
                                                                const isSubActive =
                                                                    activeCategory?.slug === cat.slug &&
                                                                    activeSubCategory?.slug === sub.slug;

                                                                return (
                                                                    <Link
                                                                        key={`m-${sub.slug}`}
                                                                        href={`/shop?category=${cat.slug}&subcategory=${sub.slug}`}
                                                                        onClick={() => setMobileFilterOpen(false)}
                                                                        className={`group/sub flex items-center justify-between px-2.5 py-1.5 text-xs rounded-md transition-all ${
                                                                            isSubActive
                                                                                ? "text-cyan-600 dark:text-cyan-400 font-bold bg-cyan-500/15 border-l-2 border-cyan-500 pl-3 shadow-xs"
                                                                                : "text-slate-700 dark:text-slate-200 font-medium hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-500/10 hover:pl-3"
                                                                        }`}
                                                                    >
                                                                        <span className="truncate">{sub.name}</span>
                                                                        {typeof sub.count === "number" && sub.count > 0 && (
                                                                            <span
                                                                                className={`text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded-full ${
                                                                                    isSubActive
                                                                                        ? "bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/40"
                                                                                        : "bg-slate-100 dark:bg-slate-800/90 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60"
                                                                                }`}
                                                                            >
                                                                                {sub.count}
                                                                            </span>
                                                                        )}
                                                                    </Link>
                                                                );
                                                            })}
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                {renderFilterControls(true)}
                            </div>

                            <div className="pt-6 border-t border-border mt-6 space-y-2">
                                <button
                                    type="button"
                                    onClick={() => setMobileFilterOpen(false)}
                                    className="w-full py-2.5 bg-accent text-white dark:text-slate-950 font-bold rounded-xl text-xs shadow-md"
                                >
                                    Show {products.length} Products
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        clearFilters();
                                        setMobileFilterOpen(false);
                                    }}
                                    className="w-full py-2 bg-card border border-border text-muted hover:text-fg rounded-xl text-xs"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* ------------------------------------------------------------------ */}
                {/*  Trust & Value Proposition Bar                                     */}
                {/* ------------------------------------------------------------------ */}
                <section className="border-t border-border bg-card py-12 mt-12">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <ShieldCheck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">100% Genuine Brands</h4>
                                    <p className="text-xs text-muted mt-0.5">Direct manufacturer warranty</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <Truck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Islandwide Delivery</h4>
                                    <p className="text-xs text-muted mt-0.5">Fast & secure shipping</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <Wrench className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Custom PC Assembly</h4>
                                    <p className="text-xs text-muted mt-0.5">Free testing & stress checks</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <RotateCcw className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">7-Day Easy Returns</h4>
                                    <p className="text-xs text-muted mt-0.5">Hassle-free replacement policy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
