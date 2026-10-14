import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import { Heart, ShoppingCart } from "lucide-react";
import React, { useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Types + hard-coded sample data (replace with admin dashboard data later)  */
/* -------------------------------------------------------------------------- */

export interface ProductCardData {
    id: number | string;
    name: string;
    image: string;
    category: string;
    subCategory?: string;
    originalPrice: number;
    discountPercent: number; // 0 = no discount
    inStock: boolean;
}

export const sampleProduct: ProductCardData = {
    id: 1,
    name: "Redragon K552 Kumara RGB Mechanical Gaming Keyboard",
    image: "/images/products/sample-keyboard.jpg",
    category: "Peripherals",
    subCategory: "Keyboards",
    originalPrice: 89.99,
    discountPercent: 15,
    inStock: true,
};

export const sampleOutOfStockProduct: ProductCardData = {
    id: 2,
    name: "NVIDIA GeForce RTX 4060 8GB Graphics Card",
    image: "/images/products/sample-keyboard.jpg",
    category: "PC Components",
    subCategory: "Graphic Cards",
    originalPrice: 329.0,
    discountPercent: 0,
    inStock: false,
};

export const sample10Products: ProductCardData[] = [
    {
        id: 1,
        name: "Redragon K552 Kumara RGB Mechanical Gaming Keyboard",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Keyboards",
        originalPrice: 89.99,
        discountPercent: 15,
        inStock: true,
    },
    {
        id: 2,
        name: "NVIDIA GeForce RTX 4060 8GB GDDR6 Graphics Card",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Graphic Cards",
        originalPrice: 329.0,
        discountPercent: 0,
        inStock: false,
    },
    {
        id: 3,
        name: "Logitech G502 HERO High Performance Gaming Mouse",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Mouse",
        originalPrice: 49.99,
        discountPercent: 20,
        inStock: true,
    },
    {
        id: 4,
        name: "AMD Ryzen 7 7800X3D 8-Core 16-Thread Desktop Processor",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Processors",
        originalPrice: 449.0,
        discountPercent: 10,
        inStock: true,
    },
    {
        id: 5,
        name: "Samsung 990 PRO 2TB PCIe 4.0 M.2 NVMe Internal SSD",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "SSD",
        originalPrice: 179.99,
        discountPercent: 12,
        inStock: true,
    },
    {
        id: 6,
        name: "ASUS ROG Swift 27\" 240Hz Fast IPS Esports Gaming Monitor",
        image: "/images/products/sample-keyboard.jpg",
        category: "Monitors & Displays",
        subCategory: "Gaming Monitors",
        originalPrice: 599.99,
        discountPercent: 15,
        inStock: true,
    },
    {
        id: 7,
        name: "Corsair RM850x 850W 80 PLUS Gold Fully Modular ATX Power Supply",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Power Supply",
        originalPrice: 139.99,
        discountPercent: 0,
        inStock: true,
    },
    {
        id: 8,
        name: "SteelSeries Arctis Nova 7 Wireless Multi-Platform Gaming Headset",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Headsets",
        originalPrice: 179.99,
        discountPercent: 10,
        inStock: true,
    },
    {
        id: 9,
        name: "NZXT Kraken Elite 360 RGB AIO Liquid CPU Cooler with LCD Display",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Coolers",
        originalPrice: 279.99,
        discountPercent: 8,
        inStock: true,
    },
    {
        id: 10,
        name: "MSI MPG B650 Carbon WiFi AM5 DDR5 ATX Motherboard",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Motherboards",
        originalPrice: 299.99,
        discountPercent: 0,
        inStock: false,
    },
];

/* -------------------------------------------------------------------------- */
/*  Helpers                                                                   */
/* -------------------------------------------------------------------------- */

const formatPrice = (value: number) => `$${value.toFixed(2)}`;

const getLatestPrice = (original: number, discountPercent: number) =>
    original - (original * discountPercent) / 100;

/* -------------------------------------------------------------------------- */
/*  ProductCard                                                               */
/* -------------------------------------------------------------------------- */

export interface ProductCardProps {
    product?: ProductCardData | any;
}

export function ProductCard({
    product = sampleProduct,
}: ProductCardProps) {
    const [isWishlisted, setIsWishlisted] = useState(false);

    // Normalization to support both ProductCardData and legacy product props
    const normalized: ProductCardData = {
        id: product.id ?? 1,
        name: product.name ?? "Sample Product",
        image:
            product.image ??
            product.images?.[0] ??
            "/images/products/sample-keyboard.jpg",
        category: product.category ?? "Hardware",
        subCategory:
            product.subCategory ?? product.category ?? "Accessories",
        originalPrice: Number(
            product.originalPrice ?? product.price ?? 99.99,
        ),
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
            product.inStock !== undefined
                ? Boolean(product.inStock)
                : true,
    };

    const hasDiscount = normalized.discountPercent > 0;
    const latestPrice = getLatestPrice(
        normalized.originalPrice,
        normalized.discountPercent,
    );

    // Hard-coded for now. Later: connect to CartContext / WishlistContext.
    const handleAddToCart = () => {
        console.log("Add to cart:", normalized.id);
    };

    const handleToggleWishlist = () => {
        setIsWishlisted((prev) => !prev);
    };

    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-lg border border-border bg-card transition-shadow duration-300 hover:shadow-lg">
            {/* ---------------- Image area ---------------- */}
            <div className="relative bg-white dark:bg-card-base">
                {/* Top-left: stock label + discount */}
                <div className="absolute left-3 top-3 z-10 flex flex-col items-start gap-2">
                    <span
                        className={cn(
                            "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide",
                            normalized.inStock
                                ? "bg-green-500/15 text-green-700 dark:text-green-400"
                                : "bg-red-500/15 text-red-700 dark:text-red-400",
                        )}
                    >
                        <span
                            className={cn(
                                "h-1.5 w-1.5 rounded-full",
                                normalized.inStock ? "bg-green-600" : "bg-red-600",
                            )}
                        />
                        {normalized.inStock ? "Available" : "Out of Stock"}
                    </span>

                    {hasDiscount && (
                        <span className="rounded-md bg-sale px-2 py-1 text-xs font-bold text-sale-fg">
                            -{normalized.discountPercent}%
                        </span>
                    )}
                </div>

                {/* Top-right: wishlist */}
                <button
                    type="button"
                    onClick={handleToggleWishlist}
                    aria-label={
                        isWishlisted
                            ? "Remove from wishlist"
                            : "Add to wishlist"
                    }
                    aria-pressed={isWishlisted}
                    className="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 dark:bg-card/90 text-slate-700 dark:text-slate-200 shadow-sm transition-colors hover:text-sale cursor-pointer"
                >
                    <Heart
                        className={cn(
                            "h-5 w-5 transition-colors",
                            isWishlisted && "fill-sale text-sale",
                        )}
                    />
                </button>

                {/* Product image */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="block aspect-square overflow-hidden p-6"
                >
                    <img
                        src={normalized.image}
                        alt={normalized.name}
                        loading="lazy"
                        className={cn(
                            "h-full w-full object-contain transition-transform duration-500 group-hover:scale-105",
                            !normalized.inStock && "opacity-60 grayscale",
                        )}
                    />
                </Link>
            </div>

            {/* ---------------- Details ---------------- */}
            <div className="flex flex-1 flex-col p-4">
                {/* Category / subcategory labels */}
                <div className="mb-2 flex flex-wrap items-center gap-2">
                    <span className="rounded bg-primary-100 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-primary-700 dark:bg-primary-800 dark:text-primary-100">
                        {normalized.category}
                    </span>
                    <span className="rounded bg-accent-100 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-accent-800 dark:bg-accent-950 dark:text-accent-300">
                        {normalized.subCategory}
                    </span>
                </div>

                {/* Product name */}
                <Link
                    href={`/product/${normalized.id}`}
                    className="mb-3 line-clamp-2 min-h-[2.75rem] text-sm font-semibold leading-snug text-fg transition-colors hover:text-accent"
                >
                    {normalized.name}
                </Link>

                {/* Prices */}
                <div className="mt-auto mb-4 flex items-baseline gap-2">
                    <span className="text-xl font-bold text-fg">
                        {formatPrice(latestPrice)}
                    </span>
                    {hasDiscount && (
                        <span className="text-xs text-muted line-through">
                            {formatPrice(normalized.originalPrice)}
                        </span>
                    )}
                </div>

                {/* Add to cart */}
                <button
                    type="button"
                    onClick={handleAddToCart}
                    disabled={!normalized.inStock}
                    className={cn(
                        "flex w-full items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold uppercase tracking-wider transition-opacity",
                        normalized.inStock
                            ? "bg-accent text-accent-fg hover:opacity-90 cursor-pointer"
                            : "bg-border text-muted cursor-not-allowed",
                    )}
                >
                    <ShoppingCart className="h-4 w-4" />
                    {normalized.inStock ? "Add to Cart" : "Out of Stock"}
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
