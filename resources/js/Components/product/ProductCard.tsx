import { useCart } from "@/contexts/CartContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { PageProps } from "@/types";
import { cn } from "@/utils/cn";
import { formatPrice } from "@/utils/currency";
import { Link, router, usePage } from "@inertiajs/react";
import { toast } from "sonner";
import { Heart, ShoppingCart, Star, Eye, Check, Bell } from "lucide-react";
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
    latestPrice?: number;
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
        name: 'ASUS ROG Swift 27" 240Hz Fast IPS Esports Gaming Monitor',
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
        latestPrice:
            product.latestPrice !== undefined
                ? Number(product.latestPrice)
                : product.sale_price !== undefined
                  ? Number(product.sale_price)
                  : undefined,
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
    const latestPrice =
        normalized.latestPrice !== undefined && normalized.latestPrice > 0
            ? normalized.latestPrice
            : getLatestPrice(
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
            rating: normalized.rating ?? 5,
            reviews: normalized.reviewCount ?? 0,
            colors: [],
            sizes: [],
            description: "",
            brand: normalized.brand || "ComLink",
        });
    };

    const showSubCategory =
        Boolean(normalized.subCategory) &&
        normalized.subCategory?.toLowerCase().trim() !==
            normalized.category?.toLowerCase().trim() &&
        !["laptops", "monitors & displays", "software"].includes(
            (normalized.category || "").toLowerCase().trim(),
        );

    return (
        <article className="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-border/80 bg-card transition-all duration-300 hover:-translate-y-1 hover:border-cyan-500 hover:shadow-xl hover:shadow-cyan-500/20">
            {/* ---------------- 1. Colour stage + U-shaped panel ---------------- */}
            <div
                className={cn(
                    "relative aspect-[1/1] w-full overflow-hidden",
                    normalized.inStock
                        ? "bg-gradient-to-br from-cyan-400 via-cyan-600 to-blue-700"
                        : "bg-gradient-to-br from-slate-500 via-slate-600 to-slate-800",
                )}
            >
                {/* U-shaped light panel the product sits on */}
                <div className="pointer-events-none absolute inset-x-[10%] top-0 bottom-7 rounded-b-[999px] bg-gradient-to-b from-white/95 to-white/80 shadow-[inset_0_-12px_30px_rgba(8,145,178,0.18)]" />

                {/* Top-Left: Status & Discount Badges */}
                <div className="pointer-events-none absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5">
                    {/* Stock status pill */}
                    <span
                        className={cn(
                            "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md",
                            normalized.inStock
                                ? "border-emerald-500/30 bg-white/90 text-emerald-700"
                                : "border-rose-500/30 bg-white/90 text-rose-700",
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

                    {/* Discount badge */}
                    {hasDiscount && (
                        <span className="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-rose-500 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                            -{normalized.discountPercent}% OFF
                        </span>
                    )}
                </div>

                {/* Top-Right: Quick Action Icons */}
                <div className="absolute right-3 top-3 z-10 flex flex-col gap-1.5">
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
                            "flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border shadow-sm backdrop-blur-md transition-all duration-200 hover:scale-110 active:scale-95",
                            isWishlisted
                                ? "border-rose-500/40 bg-white text-rose-500"
                                : "border-white/60 bg-white/90 text-slate-500 hover:border-rose-500/30 hover:text-rose-500",
                        )}
                    >
                        <Heart
                            className={cn(
                                "h-4 w-4 transition-transform",
                                isWishlisted &&
                                    "scale-110 fill-rose-500 text-rose-500",
                            )}
                        />
                    </button>

                    {/* Quick View Button (hover reveal) */}
                    <Link
                        href={`/product/${normalized.id}`}
                        aria-label="Quick View"
                        className="flex h-8 w-8 translate-y-1 items-center justify-center rounded-full border border-white/60 bg-white/90 text-slate-500 opacity-0 shadow-sm backdrop-blur-md transition-all duration-200 hover:scale-110 hover:text-cyan-600 active:scale-95 group-hover:translate-y-0 group-hover:opacity-100"
                    >
                        <Eye className="h-4 w-4" />
                    </Link>
                </div>

                {/* Product Image (tilted, straightens on hover) */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="absolute inset-x-[14%] top-[10%] bottom-[12%] flex items-center justify-center"
                >
                    <img
                        src={normalized.image}
                        alt={normalized.name}
                        loading="lazy"
                        className={cn(
                            // multiply blends white photo backgrounds into the light panel
                            "h-full w-full -rotate-6 object-contain mix-blend-multiply transition-transform duration-500 ease-out group-hover:rotate-0 group-hover:scale-110",
                            !normalized.inStock && "opacity-50 grayscale",
                        )}
                    />
                </Link>
            </div>

            {/* ---------------- 2. Price pill (overlaps the stage) ---------------- */}
            <div className="relative z-10 -mt-5 flex items-start justify-between gap-2 pr-4">
                <div
                    className={cn(
                        "inline-flex items-center rounded-r-full py-2 pl-4 pr-6 font-display text-lg font-black tracking-tight text-white shadow-lg",
                        normalized.inStock
                            ? "bg-gradient-to-r from-cyan-600 to-cyan-400 shadow-cyan-500/30 dark:text-slate-950"
                            : "bg-gradient-to-r from-slate-600 to-slate-500 shadow-slate-500/20",
                    )}
                >
                    {formatPrice(latestPrice)}
                </div>

                {hasDiscount && (
                    <div className="flex flex-col items-end gap-1 pt-6">
                        <span className="text-xs font-medium text-muted line-through">
                            {formatPrice(normalized.originalPrice)}
                        </span>
                        <span className="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                            Save {formatPrice(savings)}
                        </span>
                    </div>
                )}
            </div>

            {/* ---------------- 3. Details & Metadata ---------------- */}
            <div
                className={cn(
                    "flex flex-1 flex-col px-4 pb-4",
                    hasDiscount ? "pt-1" : "pt-3",
                )}
            >
                {/* Category & Subcategory Labels + Rating Row */}
                <div className="mb-2 flex items-center justify-between gap-2">
                    <div className="flex flex-wrap items-center gap-1.5">
                        <span className="rounded bg-primary-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-primary-700 dark:bg-primary-800 dark:text-primary-100">
                            {normalized.category}
                        </span>
                        {showSubCategory && (
                            <span className="rounded bg-accent-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-accent-800 dark:bg-accent-950 dark:text-accent-300">
                                {normalized.subCategory}
                            </span>
                        )}
                    </div>

                    {/* Rating badge */}
                    <div className="flex shrink-0 items-center gap-1 text-[11px] font-semibold text-amber-500">
                        <Star className="h-3 w-3 fill-amber-400 text-amber-400" />
                        <span className="font-bold text-fg">
                            {normalized.rating?.toFixed(1)}
                        </span>
                        <span className="text-[10px] text-muted">
                            ({normalized.reviewCount})
                        </span>
                    </div>
                </div>

                {/* Product Title */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="mb-4 line-clamp-2 min-h-[2.6rem] text-sm font-semibold leading-snug text-fg transition-colors group-hover:text-cyan-600 dark:group-hover:text-cyan-400"
                    title={normalized.name}
                >
                    {normalized.name}
                </Link>

                {/* ---------------- Add to Cart Button ---------------- */}
                <button
                    type="button"
                    onClick={handleAddToCart}
                    disabled={!normalized.inStock}
                    className={cn(
                        "group/btn relative mt-auto flex w-full items-center justify-center gap-2 overflow-hidden rounded-full px-4 py-2.5 text-xs font-bold uppercase tracking-wider shadow-md transition-all duration-300",
                        normalized.inStock
                            ? isAdded
                                ? "cursor-default bg-emerald-600 text-white shadow-emerald-600/25"
                                : "cursor-pointer bg-gradient-to-r from-cyan-600 to-cyan-500 text-white shadow-cyan-500/20 hover:from-cyan-500 hover:to-cyan-400 hover:shadow-cyan-500/35 active:scale-[0.98] dark:from-cyan-500 dark:to-cyan-400 dark:text-slate-950 dark:hover:from-cyan-400 dark:hover:to-cyan-300"
                            : "cursor-not-allowed border border-border/60 bg-muted/30 text-muted shadow-none",
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