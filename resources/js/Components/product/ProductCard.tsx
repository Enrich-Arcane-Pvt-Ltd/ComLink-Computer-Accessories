import { useCart } from "@/contexts/CartContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { PageProps } from "@/types";
import { cn } from "@/utils/cn";
import { formatPrice } from "@/utils/currency";
import { Link, router, usePage } from "@inertiajs/react";
import { toast } from "sonner";
import {
    Heart,
    ShoppingCart,
    Star,
    Eye,
    Check,
    Bell,
} from "lucide-react";
import React, { useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Types + rich sample data                                                  */
/* -------------------------------------------------------------------------- */

export interface ProductCardData {
    id: number | string;
    name: string;
    image: string;
    category: string;
    subCategory?: string;
    brand?: string;
    rating?: number;
    reviewCount?: number;
    reviews?: number;
    originalPrice: number;
    discountPercent: number; // 0 = no discount
    inStock: boolean;
    condition?: "Brand New" | "Used" | string;
}

export const sampleProduct: ProductCardData = {
    id: 1,
    name: "Redragon K552 Kumara RGB Mechanical Gaming Keyboard",
    image: "/images/products/sample-keyboard.jpg",
    category: "Peripherals",
    subCategory: "Keyboards",
    brand: "Redragon",
    rating: 4.8,
    reviewCount: 34,
    originalPrice: 89.99,
    discountPercent: 15,
    inStock: true,
    condition: "Brand New",
};

export const sampleOutOfStockProduct: ProductCardData = {
    id: 2,
    name: "NVIDIA GeForce RTX 4060 8GB Graphics Card",
    image: "/images/products/sample-keyboard.jpg",
    category: "PC Components",
    subCategory: "Graphic Cards",
    brand: "NVIDIA",
    rating: 4.9,
    reviewCount: 57,
    originalPrice: 329.0,
    discountPercent: 0,
    inStock: false,
    condition: "Brand New",
};

export const sample10Products: ProductCardData[] = [
    {
        id: 1,
        name: "Redragon K552 Kumara RGB Mechanical Gaming Keyboard",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Keyboards",
        brand: "Redragon",
        rating: 4.8,
        reviewCount: 42,
        originalPrice: 89.99,
        discountPercent: 15,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 2,
        name: "NVIDIA GeForce RTX 4060 8GB GDDR6 Graphics Card",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Graphic Cards",
        brand: "NVIDIA",
        rating: 4.9,
        reviewCount: 19,
        originalPrice: 329.0,
        discountPercent: 0,
        inStock: false,
        condition: "Brand New",
    },
    {
        id: 3,
        name: "Logitech G502 HERO High Performance Gaming Mouse",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Mouse",
        brand: "Logitech",
        rating: 4.9,
        reviewCount: 128,
        originalPrice: 49.99,
        discountPercent: 20,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 4,
        name: "AMD Ryzen 7 7800X3D 8-Core 16-Thread Desktop Processor",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Processors",
        brand: "AMD",
        rating: 5.0,
        reviewCount: 88,
        originalPrice: 449.0,
        discountPercent: 10,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 5,
        name: "Samsung 990 PRO 2TB PCIe 4.0 M.2 NVMe Internal SSD",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "SSD",
        brand: "Samsung",
        rating: 4.9,
        reviewCount: 64,
        originalPrice: 179.99,
        discountPercent: 12,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 6,
        name: "ASUS ROG Swift 27\" 240Hz Fast IPS Esports Gaming Monitor",
        image: "/images/products/sample-keyboard.jpg",
        category: "Monitors & Displays",
        subCategory: "Gaming Monitors",
        brand: "ASUS ROG",
        rating: 4.7,
        reviewCount: 31,
        originalPrice: 599.99,
        discountPercent: 15,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 7,
        name: "Corsair RM850x 850W 80 PLUS Gold Fully Modular ATX Power Supply",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Power Supply",
        brand: "Corsair",
        rating: 4.8,
        reviewCount: 52,
        originalPrice: 139.99,
        discountPercent: 0,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 8,
        name: "SteelSeries Arctis Nova 7 Wireless Multi-Platform Gaming Headset",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Headsets",
        brand: "SteelSeries",
        rating: 4.8,
        reviewCount: 46,
        originalPrice: 179.99,
        discountPercent: 10,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 9,
        name: "NZXT Kraken Elite 360 RGB AIO Liquid CPU Cooler with LCD Display",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Coolers",
        brand: "NZXT",
        rating: 4.9,
        reviewCount: 29,
        originalPrice: 279.99,
        discountPercent: 8,
        inStock: true,
        condition: "Brand New",
    },
    {
        id: 10,
        name: "MSI MPG B650 Carbon WiFi AM5 DDR5 ATX Motherboard",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Motherboards",
        brand: "MSI",
        rating: 4.6,
        reviewCount: 23,
        originalPrice: 299.99,
        discountPercent: 0,
        inStock: false,
        condition: "Brand New",
    },
];

/* -------------------------------------------------------------------------- */
/*  Helpers                                                                   */
/* -------------------------------------------------------------------------- */

const getLatestPrice = (original: number, discountPercent: number) =>
    original - (original * discountPercent) / 100;

/* -------------------------------------------------------------------------- */
/*  ProductCard Component                                                     */
/* -------------------------------------------------------------------------- */

export interface ProductCardProps {
    product?: ProductCardData | any;
}

export function ProductCard({ product = sampleProduct }: ProductCardProps) {
    const { auth } = usePage<PageProps>().props;
    const { addToCart } = useCart();
    const { toggleWishlist, isInWishlist } = useWishlist();
    const [isAdded, setIsAdded] = useState(false);

    // Normalization to support both ProductCardData and legacy product props
    const normalized: ProductCardData = {
        id: product.id ?? 1,
        name: product.name ?? "Sample Product",
        image:
            product.image ??
            product.images?.[0] ??
            "/images/products/sample-keyboard.jpg",
        category: product.category ?? "PC Hardware",
        subCategory: product.subCategory || undefined,
        brand: product.brand ?? (product.name?.split(" ")[0] || "ComLink"),
        rating: Number(product.rating ?? 4.8),
        reviewCount: Number(product.reviewCount ?? product.reviews ?? 24),
        originalPrice: Number(product.originalPrice ?? product.price ?? 99.99),
        discountPercent:
            product.discountPercent !== undefined
                ? Number(product.discountPercent)
                : product.isSale && product.originalPrice && product.price
                  ? Math.round(
                        ((product.originalPrice - product.price) /
                            product.originalPrice) *
                            100,
                    )
                  : 0,
        inStock:
            product.inStock !== undefined ? Boolean(product.inStock) : true,
        condition: product.condition ?? "Brand New",
    };

    const isWishlisted = isInWishlist(String(normalized.id));
    const hasDiscount = normalized.discountPercent > 0;
    const latestPrice = getLatestPrice(
        normalized.originalPrice,
        normalized.discountPercent,
    );
    const savings = normalized.originalPrice - latestPrice;

    const handleAddToCart = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (!auth?.user) {
            toast.error("Please login or register to add items to your cart.");
            router.visit("/login");
            return;
        }

        if (!normalized.inStock) return;

        addToCart({
            productId: String(normalized.id),
            name: normalized.name,
            price: latestPrice,
            image: normalized.image,
            quantity: 1,
        });

        setIsAdded(true);
        setTimeout(() => setIsAdded(false), 1800);
    };

    const handleToggleWishlist = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (!auth?.user) {
            toast.error("Please login or register to manage your wishlist.");
            router.visit("/login");
            return;
        }

        toggleWishlist({
            id: String(normalized.id),
            name: normalized.name,
            price: latestPrice,
            originalPrice: normalized.originalPrice,
            images: [normalized.image],
            category: normalized.category,
            rating: normalized.rating,
            reviews: normalized.reviewCount,
            colors: [],
            sizes: [],
            description: "",
            brand: normalized.brand || "ComLink",
        });
    };

    return (
        <article className="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-border/80 bg-card/95 backdrop-blur-sm transition-all duration-300 hover:border-cyan-500 hover:shadow-lg hover:shadow-cyan-500/15">
            {/* Top ambient corner glow on hover */}
            <div className="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-cyan-500/10 blur-2xl transition-all duration-500 group-hover:scale-125 group-hover:bg-cyan-500/15" />

            {/* ---------------- 1. Image Showcase Stage ---------------- */}
            <div className="relative mx-3 mt-3 aspect-square overflow-hidden rounded-xl bg-gradient-to-b from-slate-100/90 to-slate-200/50 dark:from-slate-900/60 dark:to-slate-950/80 p-5 flex items-center justify-center border border-border/40 group-hover:border-cyan-500/25 transition-colors duration-300">
                {/* Radial spotlight effect behind product on hover */}
                <div className="pointer-events-none absolute inset-0 bg-radial from-cyan-500/15 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

                {/* Top-Left: Status & Discount Badges */}
                <div className="absolute left-2.5 top-2.5 z-10 flex flex-col items-start gap-1.5 pointer-events-none">
                    {/* Stock status pill */}
                    <span
                        className={cn(
                            "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider backdrop-blur-md shadow-xs border",
                            normalized.inStock
                                ? "bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30"
                                : "bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30",
                        )}
                    >
                        <span className="relative flex h-1.5 w-1.5">
                            {normalized.inStock && (
                                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                            )}
                            <span
                                className={cn(
                                    "relative inline-flex h-1.5 w-1.5 rounded-full",
                                    normalized.inStock
                                        ? "bg-emerald-500"
                                        : "bg-rose-500",
                                )}
                            />
                        </span>
                        {normalized.inStock ? "In Stock" : "Out of Stock"}
                    </span>

                    {/* Discount badge without AI icon */}
                    {hasDiscount && (
                        <span className="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-rose-500 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-xs">
                            -{normalized.discountPercent}% OFF
                        </span>
                    )}
                </div>

                {/* Top-Right: Quick Action Icons */}
                <div className="absolute right-2.5 top-2.5 z-10 flex flex-col gap-1.5">
                    {/* Wishlist button */}
                    <button
                        type="button"
                        onClick={handleToggleWishlist}
                        aria-label={
                            isWishlisted
                                ? "Remove from wishlist"
                                : "Add to wishlist"
                        }
                        aria-pressed={isWishlisted}
                        className={cn(
                            "flex h-8 w-8 items-center justify-center rounded-full bg-card/90 backdrop-blur-md border border-border/80 shadow-xs transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer",
                            isWishlisted
                                ? "text-rose-500 border-rose-500/40 bg-rose-500/10"
                                : "text-muted hover:text-rose-500 hover:border-rose-500/30",
                        )}
                    >
                        <Heart
                            className={cn(
                                "h-4 w-4 transition-transform",
                                isWishlisted && "fill-rose-500 text-rose-500 scale-110",
                            )}
                        />
                    </button>

                    {/* Quick View Button (hover reveal) */}
                    <Link
                        href={`/product/${normalized.id}`}
                        aria-label="Quick View"
                        className="flex h-8 w-8 items-center justify-center rounded-full bg-card/90 backdrop-blur-md border border-border/80 text-muted shadow-xs transition-all duration-200 hover:text-cyan-500 hover:border-cyan-500/40 hover:scale-110 active:scale-95 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0"
                    >
                        <Eye className="h-4 w-4" />
                    </Link>
                </div>

                {/* Product Image Link */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="flex h-full w-full items-center justify-center"
                >
                    <img
                        src={normalized.image}
                        alt={normalized.name}
                        loading="lazy"
                        className={cn(
                            "max-h-[190px] w-auto max-w-full object-contain drop-shadow-sm transition-transform duration-500 ease-out group-hover:scale-108",
                            !normalized.inStock && "opacity-50 grayscale",
                        )}
                    />
                </Link>
            </div>

            {/* ---------------- 2. Details & Metadata ---------------- */}
            <div className="flex flex-1 flex-col p-4 pt-3">
                {/* Category & Subcategory Labels + Rating Row */}
                <div className="mb-2 flex items-center justify-between gap-2">
                    <div className="flex flex-wrap items-center gap-1.5">
                        <span className="rounded bg-primary-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-primary-700 dark:bg-primary-800 dark:text-primary-100">
                            {normalized.category}
                        </span>
                        {Boolean(normalized.subCategory) &&
                            normalized.subCategory?.toLowerCase().trim() !== normalized.category?.toLowerCase().trim() &&
                            !["laptops", "monitors & displays", "software"].includes(
                                (normalized.category || "").toLowerCase().trim(),
                            ) && (
                                <span className="rounded bg-accent-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-accent-800 dark:bg-accent-950 dark:text-accent-300">
                                    {normalized.subCategory}
                                </span>
                            )}
                    </div>

                    {/* Rating badge */}
                    <div className="flex items-center gap-1 text-[11px] font-semibold text-amber-500 shrink-0">
                        <Star className="h-3 w-3 fill-amber-400 text-amber-400" />
                        <span className="text-fg font-bold">
                            {normalized.rating?.toFixed(1)}
                        </span>
                        <span className="text-muted text-[10px]">
                            ({normalized.reviewCount})
                        </span>
                    </div>
                </div>

                {/* Product Title */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="line-clamp-2 min-h-[2.6rem] text-sm font-semibold leading-snug text-fg group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors"
                    title={normalized.name}
                >
                    {normalized.name}
                </Link>

                {/* Price & Savings Row */}
                <div className="mt-auto pt-3 mb-3.5 flex items-baseline justify-between gap-2 border-t border-border/40">
                    <div className="flex items-baseline gap-2">
                        <span className="text-xl font-black tracking-tight text-fg font-display">
                            {formatPrice(latestPrice)}
                        </span>
                        {hasDiscount && (
                            <span className="text-xs text-muted line-through font-medium">
                                {formatPrice(normalized.originalPrice)}
                            </span>
                        )}
                    </div>

                    {hasDiscount && (
                        <span className="rounded-md bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Save {formatPrice(savings)}
                        </span>
                    )}
                </div>

                {/* ---------------- 3. Add to Cart Button ---------------- */}
                <button
                    type="button"
                    onClick={handleAddToCart}
                    disabled={!normalized.inStock}
                    className={cn(
                        "group/btn relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl py-2.5 px-4 text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-md",
                        normalized.inStock
                            ? isAdded
                                ? "bg-emerald-600 text-white shadow-emerald-600/25 cursor-default"
                                : "bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white dark:from-cyan-500 dark:to-cyan-400 dark:hover:from-cyan-400 dark:hover:to-cyan-300 dark:text-slate-950 shadow-cyan-500/20 hover:shadow-cyan-500/35 active:scale-[0.98] cursor-pointer"
                            : "bg-muted/30 text-muted border border-border/60 cursor-not-allowed shadow-none",
                    )}
                >
                    {normalized.inStock ? (
                        isAdded ? (
                            <>
                                <Check className="h-4 w-4 animate-scale-in" />
                                <span>Added to Cart!</span>
                            </>
                        ) : (
                            <>
                                <ShoppingCart className="h-4 w-4 transition-transform duration-200 group-hover/btn:-translate-y-0.5 group-hover/btn:translate-x-0.5" />
                                <span>Add to Cart</span>
                            </>
                        )
                    ) : (
                        <>
                            <Bell className="h-3.5 w-3.5 opacity-60" />
                            <span>Out of Stock</span>
                        </>
                    )}
                </button>
            </div>
        </article>
    );
}

export default ProductCard;

/* -------------------------------------------------------------------------- */
/*  Optional preview grid: drop <ProductCardPreview /> into any page to test  */
/* -------------------------------------------------------------------------- */

export function ProductCardPreview() {
    return (
        <div className="container mx-auto grid grid-cols-1 gap-6 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
            <ProductCard product={sampleProduct} />
            <ProductCard product={sampleOutOfStockProduct} />
        </div>
    );
}
