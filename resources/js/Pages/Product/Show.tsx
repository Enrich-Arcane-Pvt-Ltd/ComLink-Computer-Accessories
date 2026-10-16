import { ProductCard, ProductCardData } from "@/Components/product/ProductCard";
import { ImageLightbox } from "@/Components/product/ImageLightbox";
import BrandMarquee from "@/Components/BrandMarquee";
import AppLayout from "@/Layouts/AppLayout";
import { useCart } from "@/contexts/CartContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { PageProps } from "@/types";
import { cn } from "@/utils/cn";
import { formatPrice } from "@/utils/currency";
import { Head, Link, router, usePage } from "@inertiajs/react";
import {
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Copy,
    Heart,
    HelpCircle,
    Maximize2,
    Minus,
    Package,
    Plus,
    RefreshCw,
    RotateCcw,
    Send,
    Share2,
    Shield,
    ShieldAlert,
    ShieldCheck,
    ShoppingCart,
    Star,
    Truck,
    Wrench,
    Zap,
} from "lucide-react";
import React, { useEffect, useMemo, useRef, useState } from "react";
import { toast } from "sonner";

/* -------------------------------------------------------------------------- */
/*  Interfaces                                                                */
/* -------------------------------------------------------------------------- */

export interface ProductSpecItem {
    label: string;
    value: string;
}

export interface ProductSpecGroup {
    group: string;
    items: ProductSpecItem[];
}

export interface ProductWarrantyInfo {
    duration: string;
    type: string;
    covered: string[];
    notCovered: string[];
    claimProcess: string;
    hotline: string;
    email: string;
}

export interface ProductReviewItem {
    id: number | string;
    author: string;
    rating: number;
    date: string;
    verified: boolean;
    title: string;
    comment: string;
}

export interface DetailedProduct {
    id: number | string;
    name: string;
    sku?: string;
    brand?: string;
    category: string;
    categorySlug: string;
    subCategory: string;
    subCategorySlug: string;
    originalPrice: number;
    latestPrice: number;
    discountPercent: number;
    inStock: boolean;
    stockCount: number;
    rating: number;
    reviews: number;
    condition?: string;
    image: string;
    images: string[];
    description: string;
    specifications?: ProductSpecGroup[];
    warranty?: ProductWarrantyInfo;
    reviewsList?: ProductReviewItem[];
}

interface ProductShowProps {
    product: DetailedProduct;
    relatedProducts: ProductCardData[];
    categories?: any[];
}

/* -------------------------------------------------------------------------- */
/*  Helper Formatters                                                         */
/* -------------------------------------------------------------------------- */

/* -------------------------------------------------------------------------- */
/*  Product Show Component                                                    */
/* -------------------------------------------------------------------------- */

export default function Show(props: ProductShowProps) {
    return (
        <AppLayout>
            <ProductViewContent {...props} />
        </AppLayout>
    );
}

function ProductViewContent({
    product,
    relatedProducts = [],
    categories = [],
}: ProductShowProps) {
    const { auth } = usePage<PageProps>().props;
    const { addToCart } = useCart();
    const { toggleWishlist, isInWishlist } = useWishlist();

    // Image gallery states
    const imagesList = useMemo(() => {
        if (Array.isArray(product.images) && product.images.length >= 4) {
            return product.images.slice(0, 4);
        }
        if (Array.isArray(product.images) && product.images.length > 0) {
            const list = [...product.images];
            while (list.length < 4) {
                list.push(product.image || list[0]);
            }
            return list;
        }
        const fallback = product.image || "/images/products/sample-keyboard.jpg";
        return [fallback, fallback, fallback, fallback];
    }, [product.images, product.image]);

    // First small box image is default in main image box
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);

    // Quantity state
    const [quantity, setQuantity] = useState(1);
    const [isAddedToCart, setIsAddedToCart] = useState(false);

    // Active detail tab
    const [activeTab, setActiveTab] = useState<"spec" | "warranty" | "reviews">("spec");
    const reviewsTabRef = useRef<HTMLDivElement>(null);

    // Wishlist status
    const isWishlisted = isInWishlist(String(product.id));

    // Carousel state for same subcategory products
    const [carouselIndex, setCarouselIndex] = useState(0);
    const [visibleCards, setVisibleCards] = useState(4);

    // New review form states
    const [newReviewAuthor, setNewReviewAuthor] = useState("");
    const [newReviewRating, setNewReviewRating] = useState(5);
    const [newReviewTitle, setNewReviewTitle] = useState("");
    const [newReviewComment, setNewReviewComment] = useState("");
    const [localReviews, setLocalReviews] = useState<ProductReviewItem[]>(
        product.reviewsList || [],
    );

    // Calculate savings
    const hasDiscount = product.discountPercent > 0;
    const savings = Math.max(0, product.originalPrice - product.latestPrice);

    // Sync active image if product changes
    useEffect(() => {
        setActiveImageIndex(0);
        setQuantity(1);
    }, [product.id]);

    // Responsive visible cards count for carousel
    useEffect(() => {
        const handleResize = () => {
            if (window.innerWidth >= 1280) {
                setVisibleCards(4);
            } else if (window.innerWidth >= 1024) {
                setVisibleCards(3);
            } else if (window.innerWidth >= 640) {
                setVisibleCards(2);
            } else {
                setVisibleCards(1);
            }
        };

        handleResize();
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    const maxCarouselIndex = Math.max(0, relatedProducts.length - visibleCards);

    const handleNextCarousel = () => {
        setCarouselIndex((prev) => (prev >= maxCarouselIndex ? 0 : prev + 1));
    };

    const handlePrevCarousel = () => {
        setCarouselIndex((prev) => (prev <= 0 ? maxCarouselIndex : prev - 1));
    };

    // Quantity handlers
    const handleIncrement = () => {
        if (!product.inStock) return;
        if (product.stockCount && quantity >= product.stockCount) {
            toast.warning(`Only ${product.stockCount} units available in stock.`);
            return;
        }
        setQuantity((q) => q + 1);
    };

    const handleDecrement = () => {
        if (!product.inStock) return;
        setQuantity((q) => (q > 1 ? q - 1 : 1));
    };

    // Add to cart action
    const handleAddToCart = () => {
        if (!auth?.user) {
            toast.error("Please login or register to add items to your cart.");
            router.visit("/login");
            return;
        }

        if (!product.inStock) {
            toast.error("This item is currently out of stock.");
            return;
        }

        addToCart({
            productId: String(product.id),
            name: product.name,
            price: product.latestPrice,
            image: imagesList[0],
            quantity: quantity,
        });

        setIsAddedToCart(true);
        setTimeout(() => setIsAddedToCart(false), 2000);
    };

    // Wishlist action
    const handleToggleWishlist = () => {
        if (!auth?.user) {
            toast.error("Please login or register to manage your wishlist.");
            router.visit("/login");
            return;
        }

        toggleWishlist({
            id: String(product.id),
            name: product.name,
            price: product.latestPrice,
            originalPrice: product.originalPrice,
            images: imagesList,
            category: product.category,
            rating: product.rating,
            reviews: product.reviews,
            colors: [],
            sizes: [],
            description: product.description,
            brand: product.brand || "ComLink",
        });
    };

    // Scroll to reviews
    const scrollToReviews = (e: React.MouseEvent) => {
        e.preventDefault();
        setActiveTab("reviews");
        setTimeout(() => {
            reviewsTabRef.current?.scrollIntoView({ behavior: "smooth" });
        }, 100);
    };

    // Submit review
    const handleSubmitReview = (e: React.FormEvent) => {
        e.preventDefault();
        if (!newReviewAuthor.trim() || !newReviewComment.trim()) {
            toast.error("Please fill in your name and comment.");
            return;
        }

        const newReview: ProductReviewItem = {
            id: Date.now(),
            author: newReviewAuthor.trim(),
            rating: newReviewRating,
            date: "Just now",
            verified: true,
            title: newReviewTitle.trim() || "Verified Customer Review",
            comment: newReviewComment.trim(),
        };

        setLocalReviews([newReview, ...localReviews]);
        setNewReviewAuthor("");
        setNewReviewTitle("");
        setNewReviewComment("");
        setNewReviewRating(5);
        toast.success("Thank you! Your review has been submitted.");
    };

    // Share link
    const handleShare = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            toast.success("Product link copied to clipboard!");
        }
    };

    return (
        <>
            <Head title={`${product.name} - ComLink Computer Shop`} />

            {/* Lightbox Modal */}
            {isLightboxOpen && (
                <ImageLightbox
                    images={imagesList}
                    currentIndex={activeImageIndex}
                    onClose={() => setIsLightboxOpen(false)}
                    onNavigate={(idx) => setActiveImageIndex(idx)}
                />
            )}

            <div className="bg-bg min-h-screen">
                {/* ---------------- Breadcrumb Navigation ---------------- */}
                <div className="border-b border-border/40 bg-card/40 backdrop-blur-sm">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
                        <nav className="flex flex-wrap items-center gap-2 text-xs text-muted">
                            <Link href="/" className="hover:text-cyan-500 transition-colors">
                                Home
                            </Link>
                            <span>/</span>
                            <Link href="/shop" className="hover:text-cyan-500 transition-colors">
                                Shop
                            </Link>
                            <span>/</span>
                            <Link
                                href={`/category/${product.categorySlug}`}
                                className="hover:text-cyan-500 transition-colors"
                            >
                                {product.category}
                            </Link>
                            {product.subCategory &&
                                product.subCategory.toLowerCase().trim() !==
                                    product.category.toLowerCase().trim() &&
                                !["laptops", "monitors & displays", "software"].includes(
                                    product.category.toLowerCase().trim(),
                                ) && (
                                    <>
                                        <span>/</span>
                                        <Link
                                            href={`/category/${product.categorySlug}/${product.subCategorySlug || ""}`}
                                            className="text-cyan-600 dark:text-cyan-400 font-medium hover:underline"
                                        >
                                            {product.subCategory}
                                        </Link>
                                    </>
                                )}
                            <span>/</span>
                            <span className="text-fg truncate max-w-[200px] sm:max-w-xs font-medium">
                                {product.name}
                            </span>
                        </nav>
                    </div>
                </div>

                {/* ---------------- Main Product Showcase Stage ---------------- */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 lg:pt-12">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                        {/* ========================================================== */}
                        {/* LEFT SIDE: PRODUCT IMAGE SECTION                           */}
                        {/* ========================================================== */}
                        <div className="lg:col-span-6 xl:col-span-6">
                            {/*
                              Layout Requirement:
                              - main square of product image default main image
                              - left side of the main image box there should be 4 small image boxes
                              - in default that first small image box image defaultly should shows in main image box
                              - main box top and bottom margin should be align with that 4 small image boxes section top and bottom alignment
                            */}
                            <div className="flex items-stretch gap-3 sm:gap-4 select-none">
                                {/* 4 Small Image Boxes Column (Left side of main image) */}
                                <div className="w-20 sm:w-24 shrink-0 flex flex-col justify-between gap-2.5 sm:gap-3">
                                    {imagesList.map((imgUrl, idx) => {
                                        const isActive = activeImageIndex === idx;
                                        return (
                                            <button
                                                key={`thumb-${idx}`}
                                                type="button"
                                                onClick={() => setActiveImageIndex(idx)}
                                                onMouseEnter={() => setActiveImageIndex(idx)}
                                                aria-label={`Select product image ${idx + 1}`}
                                                className={cn(
                                                    "group relative flex-1 min-h-0 aspect-square w-full rounded-xl overflow-hidden border-2 bg-gradient-to-b from-slate-100/80 to-slate-200/50 dark:from-slate-900/60 dark:to-slate-950/80 p-1.5 transition-all duration-200 cursor-pointer flex items-center justify-center",
                                                    isActive
                                                        ? "border-cyan-500 ring-2 ring-cyan-500/30 dark:ring-cyan-400/30 shadow-md scale-[1.02]"
                                                        : "border-border/70 hover:border-cyan-500/50 hover:bg-card/80 opacity-80 hover:opacity-100",
                                                )}
                                            >
                                                <img
                                                    src={imgUrl}
                                                    alt={`${product.name} view ${idx + 1}`}
                                                    className="h-full w-full object-contain transition-transform duration-300 group-hover:scale-108"
                                                    loading="lazy"
                                                />
                                                {isActive && (
                                                    <span className="absolute bottom-1 right-1 flex h-2 w-2 rounded-full bg-cyan-500 ring-2 ring-white dark:ring-slate-950" />
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>

                                {/* Main Square Product Image Box */}
                                <div className="group relative flex-1 min-w-0 aspect-square rounded-2xl overflow-hidden border border-border/80 bg-gradient-to-b from-slate-100/90 via-slate-100/60 to-slate-200/50 dark:from-slate-900/80 dark:via-slate-900/60 dark:to-slate-950/90 p-6 sm:p-8 flex items-center justify-center shadow-sm hover:border-cyan-500/40 transition-colors">
                                    {/* Ambient subtle spotlight glow */}
                                    <div className="pointer-events-none absolute inset-0 bg-radial from-cyan-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

                                    {/* Top-Left: Status & Discount Badges */}
                                    <div className="absolute top-3.5 left-3.5 z-10 flex flex-col items-start gap-1.5 pointer-events-none">
                                        <span
                                            className={cn(
                                                "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wider backdrop-blur-md shadow-xs border",
                                                product.inStock
                                                    ? "bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30"
                                                    : "bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30",
                                            )}
                                        >
                                            <span className="relative flex h-2 w-2">
                                                {product.inStock && (
                                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                                )}
                                                <span
                                                    className={cn(
                                                        "relative inline-flex h-2 w-2 rounded-full",
                                                        product.inStock ? "bg-emerald-500" : "bg-rose-500",
                                                    )}
                                                />
                                            </span>
                                            {product.inStock ? "In Stock" : "Out of Stock"}
                                        </span>

                                        {hasDiscount && (
                                            <span className="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-rose-500 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-white shadow-xs">
                                                -{product.discountPercent}% OFF
                                            </span>
                                        )}
                                    </div>

                                    {/* Top-Right: Quick Actions (Lightbox zoom & share) */}
                                    <div className="absolute top-3.5 right-3.5 z-10 flex items-center gap-2">
                                        <button
                                            type="button"
                                            onClick={handleShare}
                                            aria-label="Share product"
                                            className="h-8 w-8 rounded-full bg-card/80 backdrop-blur-md border border-border/80 flex items-center justify-center text-muted hover:text-cyan-500 hover:border-cyan-500/40 hover:scale-105 active:scale-95 transition-all cursor-pointer shadow-xs"
                                            title="Copy link"
                                        >
                                            <Share2 className="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setIsLightboxOpen(true)}
                                            aria-label="Enlarge image"
                                            className="h-8 w-8 rounded-full bg-card/80 backdrop-blur-md border border-border/80 flex items-center justify-center text-muted hover:text-cyan-500 hover:border-cyan-500/40 hover:scale-105 active:scale-95 transition-all cursor-pointer shadow-xs"
                                            title="Zoom image"
                                        >
                                            <Maximize2 className="h-4 w-4" />
                                        </button>
                                    </div>

                                    {/* Main Product Image */}
                                    <img
                                        key={activeImageIndex}
                                        src={imagesList[activeImageIndex]}
                                        alt={product.name}
                                        className={cn(
                                            "max-h-[360px] sm:max-h-[420px] w-auto max-w-full object-contain drop-shadow-md transition-all duration-500 ease-out group-hover:scale-105 cursor-zoom-in",
                                            !product.inStock && "opacity-60 grayscale",
                                        )}
                                        onClick={() => setIsLightboxOpen(true)}
                                    />

                                    {/* Prev / Next Arrows */}
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setActiveImageIndex((prev) =>
                                                prev <= 0 ? imagesList.length - 1 : prev - 1,
                                            );
                                        }}
                                        aria-label="Previous image"
                                        className="absolute left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-card/80 backdrop-blur-md border border-border/80 flex items-center justify-center text-fg opacity-0 group-hover:opacity-100 hover:bg-cyan-500 hover:text-white transition-all shadow-md cursor-pointer"
                                    >
                                        <ChevronLeft className="h-5 w-5" />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setActiveImageIndex((prev) =>
                                                prev >= imagesList.length - 1 ? 0 : prev + 1,
                                            );
                                        }}
                                        aria-label="Next image"
                                        className="absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-card/80 backdrop-blur-md border border-border/80 flex items-center justify-center text-fg opacity-0 group-hover:opacity-100 hover:bg-cyan-500 hover:text-white transition-all shadow-md cursor-pointer"
                                    >
                                        <ChevronRight className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* ========================================================== */}
                        {/* RIGHT SIDE: PRODUCT CONTENT SECTION                        */}
                        {/* ========================================================== */}
                        <div className="lg:col-span-6 xl:col-span-6 flex flex-col">
                            {/* 1. In Stock / Out of Stock Status */}
                            <div className="flex items-center gap-3 mb-2.5">
                                {product.inStock ? (
                                    <div className="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">
                                        <span className="relative flex h-2 w-2">
                                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                            <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
                                        </span>
                                        <span>
                                            In Stock ({product.stockCount} units available)
                                        </span>
                                    </div>
                                ) : (
                                    <div className="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30">
                                        <span className="h-2 w-2 rounded-full bg-rose-500" />
                                        <span>Out of Stock (Currently Unavailable)</span>
                                    </div>
                                )}

                                {product.condition && (
                                    <span className="rounded-full bg-muted/20 px-2.5 py-0.5 text-[11px] font-semibold text-muted border border-border/50">
                                        {product.condition}
                                    </span>
                                )}

                                {/* {product.sku && (
                                    <span className="text-[11px] font-mono text-muted ml-auto">
                                        SKU: {product.sku}
                                    </span>
                                )} */}
                            </div>

                            {/* 2. Product Name */}
                            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-fg font-display leading-tight mb-2.5">
                                {product.name}
                            </h1>

                            {/* 3. [Category] - [Sub Category] */}
                            <div className="flex items-center flex-wrap gap-2 mb-3 text-xs font-semibold">
                                <Link
                                    href={`/category/${product.categorySlug}`}
                                    className="inline-flex items-center gap-1 px-2.5 py-1 text-cyan-700 dark:text-cyan-400 transition-colors"
                                >
                                    {product.category}
                                </Link>
                                {product.subCategory &&
                                    product.subCategory.toLowerCase().trim() !==
                                        product.category.toLowerCase().trim() &&
                                    !["laptops", "monitors & displays", "software"].includes(
                                        product.category.toLowerCase().trim(),
                                    ) && (
                                        <>
                                            <span className="text-muted font-bold">-</span>
                                            <Link
                                                href={`/category/${product.categorySlug}/${product.subCategorySlug || ""}`}
                                                className="inline-flex items-center gap-1 px-2.5 py-1 text-purple-700 dark:text-purple-400 transition-colors"
                                            >
                                                {product.subCategory}
                                            </Link>
                                        </>
                                    )}
                                {product.brand && (
                                    <span className="text-muted ml-1">
                                        by <strong className="text-fg">{product.brand}</strong>
                                    </span>
                                )}
                            </div>

                            {/* 4. Star Rating ({review-count} reviews) */}
                            <div className="flex items-center gap-2 mb-5 pb-4 border-b border-border/40">
                                <div className="flex items-center gap-1">
                                    {[1, 2, 3, 4, 5].map((s) => (
                                        <Star
                                            key={`star-${s}`}
                                            className={cn(
                                                "h-4 w-4",
                                                s <= Math.round(product.rating)
                                                    ? "fill-amber-400 text-amber-400"
                                                    : "text-muted/40",
                                            )}
                                        />
                                    ))}
                                </div>
                                <span className="text-sm font-bold text-fg">
                                    {Number(product.rating).toFixed(1)}
                                </span>
                                <span className="text-muted text-xs">/ 5.0</span>
                                <span className="text-muted text-xs">•</span>
                                <button
                                    type="button"
                                    onClick={scrollToReviews}
                                    className="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline cursor-pointer"
                                >
                                    ({product.reviews} customer reviews)
                                </button>
                            </div>

                            {/* 5. Pricing Row: Latest Price, Original Price, Discount percentage */}
                            <div className="flex items-baseline flex-wrap gap-3 mb-5">
                                <span className="text-3xl sm:text-4xl font-black text-fg font-display tracking-tight">
                                    {formatPrice(product.latestPrice)}
                                </span>

                                {hasDiscount && (
                                    <>
                                        <span className="text-lg text-muted line-through font-medium">
                                            {formatPrice(product.originalPrice)}
                                        </span>
                                        <span className="rounded-full bg-gradient-to-r from-amber-500 to-rose-500 px-3 py-1 text-xs font-black uppercase tracking-wider text-white shadow-xs">
                                            {product.discountPercent}% OFF
                                        </span>
                                        <span className="rounded-md bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            Save {formatPrice(savings)}
                                        </span>
                                    </>
                                )}
                            </div>

                            {/* 6. Product Description */}
                            <div className="mb-6">
                                <h3 className="text-xs font-bold uppercase tracking-wider text-muted mb-2">
                                    Overview
                                </h3>
                                <p className="text-sm leading-relaxed text-muted dark:text-slate-300">
                                    {product.description}
                                </p>
                            </div>

                            {/* 7. Quantity value handling section */}
                            {/* Requirement: In status == out of stock, quantity handling and Add to cart Option should restricted */}
                            <div className="mb-6 p-4 rounded-xl border border-border/60 bg-card/50">
                                <div className="flex items-center justify-between mb-3">
                                    <span className="text-xs font-bold uppercase tracking-wider text-fg">
                                        Quantity Selection
                                    </span>
                                    {product.inStock ? (
                                        <span className="text-xs text-muted">
                                            Maximum available: <strong>{product.stockCount}</strong>
                                        </span>
                                    ) : (
                                        <span className="text-xs font-semibold text-rose-500">
                                            Quantity restricted (Out of stock)
                                        </span>
                                    )}
                                </div>

                                <div className="flex items-center gap-3">
                                    <div
                                        className={cn(
                                            "flex items-center rounded-xl border bg-card overflow-hidden shadow-xs transition-colors",
                                            product.inStock
                                                ? "border-border hover:border-cyan-500/40"
                                                : "border-border/40 opacity-40 cursor-not-allowed pointer-events-none",
                                        )}
                                    >
                                        <button
                                            type="button"
                                            onClick={handleDecrement}
                                            disabled={!product.inStock || quantity <= 1}
                                            aria-label="Decrease quantity"
                                            className="h-10 w-10 flex items-center justify-center text-fg hover:bg-muted/20 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
                                        >
                                            <Minus className="h-4 w-4" />
                                        </button>
                                        <span className="h-10 w-14 flex items-center justify-center font-bold font-mono text-sm border-x border-border/40 select-none">
                                            {product.inStock ? quantity : 0}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={handleIncrement}
                                            disabled={
                                                !product.inStock ||
                                                Boolean(product.stockCount && quantity >= product.stockCount)
                                            }
                                            aria-label="Increase quantity"
                                            className="h-10 w-10 flex items-center justify-center text-fg hover:bg-muted/20 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
                                        >
                                            <Plus className="h-4 w-4" />
                                        </button>
                                    </div>

                                    {product.inStock && (
                                        <span className="text-xs text-muted">
                                            Subtotal:{" "}
                                            <strong className="text-fg font-mono">
                                                {formatPrice(product.latestPrice * quantity)}
                                            </strong>
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* 8. Action Buttons: Add To Cart & Add To Wishlist */}
                            <div className="flex flex-col sm:flex-row items-stretch gap-3 mb-8">
                                {/* Add to Cart Button */}
                                <button
                                    type="button"
                                    onClick={handleAddToCart}
                                    disabled={!product.inStock}
                                    className={cn(
                                        "group/btn relative flex-1 flex items-center justify-center gap-2.5 rounded-xl py-3.5 px-6 font-bold text-sm uppercase tracking-wider transition-all duration-300 shadow-md",
                                        product.inStock
                                            ? isAddedToCart
                                                ? "bg-emerald-600 text-white shadow-emerald-600/30 cursor-default"
                                                : "bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white dark:from-cyan-500 dark:to-cyan-400 dark:hover:from-cyan-400 dark:hover:to-cyan-300 dark:text-slate-950 shadow-cyan-500/25 hover:shadow-cyan-500/40 active:scale-[0.98] cursor-pointer"
                                            : "bg-muted/30 text-muted border border-border/60 cursor-not-allowed shadow-none opacity-60",
                                    )}
                                >
                                    {product.inStock ? (
                                        isAddedToCart ? (
                                            <>
                                                <Check className="h-5 w-5 animate-scale-in" />
                                                <span>Added to Cart!</span>
                                            </>
                                        ) : (
                                            <>
                                                <ShoppingCart className="h-5 w-5 transition-transform group-hover/btn:-translate-y-0.5" />
                                                <span>Add To Cart</span>
                                            </>
                                        )
                                    ) : (
                                        <>
                                            <ShieldAlert className="h-5 w-5" />
                                            <span>Out of Stock (Restricted)</span>
                                        </>
                                    )}
                                </button>

                                {/* Add to Wishlist Button */}
                                <button
                                    type="button"
                                    onClick={handleToggleWishlist}
                                    aria-label="Add to wishlist"
                                    className={cn(
                                        "sm:w-auto flex items-center justify-center gap-2.5 rounded-xl py-3.5 px-5 font-bold text-sm tracking-wider border transition-all duration-200 active:scale-[0.98] cursor-pointer",
                                        isWishlisted
                                            ? "bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/40 shadow-xs"
                                            : "bg-card hover:bg-muted/20 text-fg border-border hover:border-cyan-500/40 shadow-xs",
                                    )}
                                >
                                    <Heart
                                        className={cn(
                                            "h-5 w-5 transition-transform",
                                            isWishlisted && "fill-rose-500 text-rose-500 scale-110",
                                        )}
                                    />
                                    <span>{isWishlisted ? "In Wishlist" : "Add To Wishlist"}</span>
                                </button>
                            </div>

                            {/* Trust badges */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 border-t border-border/40 text-xs">
                                <div className="flex items-center gap-2 text-muted">
                                    <ShieldCheck className="h-4 w-4 text-cyan-500 shrink-0" />
                                    <span>100% Genuine</span>
                                </div>
                                <div className="flex items-center gap-2 text-muted">
                                    <Truck className="h-4 w-4 text-cyan-500 shrink-0" />
                                    <span>Islandwide Delivery</span>
                                </div>
                                <div className="flex items-center gap-2 text-muted">
                                    <RotateCcw className="h-4 w-4 text-cyan-500 shrink-0" />
                                    <span>7 Days Exchange</span>
                                </div>
                                <div className="flex items-center gap-2 text-muted">
                                    <Wrench className="h-4 w-4 text-cyan-500 shrink-0" />
                                    <span>Official Warranty</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ========================================================== */}
                {/* NEXT SECTION: MOVING WITH TABS SHOWING DETAILS SECTION     */}
                {/* Tabs: Specification , Warranty Details , Reviews           */}
                {/* ========================================================== */}
                <div ref={reviewsTabRef} className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 sm:mt-20">
                    <div className="rounded-2xl border border-border/80 bg-card overflow-hidden shadow-xs">
                        {/* Tab Headers */}
                        <div className="flex border-b border-border/80 bg-muted/20 overflow-x-auto scrollbar-none">
                            <button
                                type="button"
                                onClick={() => setActiveTab("spec")}
                                className={cn(
                                    "relative px-6 py-4 text-sm font-bold tracking-wide transition-all whitespace-nowrap cursor-pointer",
                                    activeTab === "spec"
                                        ? "text-cyan-600 dark:text-cyan-400 bg-card"
                                        : "text-muted hover:text-fg hover:bg-card/50",
                                )}
                            >
                                <span>Specification</span>
                                {activeTab === "spec" && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-cyan-500" />
                                )}
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab("warranty")}
                                className={cn(
                                    "relative px-6 py-4 text-sm font-bold tracking-wide transition-all whitespace-nowrap cursor-pointer",
                                    activeTab === "warranty"
                                        ? "text-cyan-600 dark:text-cyan-400 bg-card"
                                        : "text-muted hover:text-fg hover:bg-card/50",
                                )}
                            >
                                <span>Warranty Details</span>
                                {activeTab === "warranty" && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-cyan-500" />
                                )}
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab("reviews")}
                                className={cn(
                                    "relative px-6 py-4 text-sm font-bold tracking-wide transition-all whitespace-nowrap cursor-pointer flex items-center gap-2",
                                    activeTab === "reviews"
                                        ? "text-cyan-600 dark:text-cyan-400 bg-card"
                                        : "text-muted hover:text-fg hover:bg-card/50",
                                )}
                            >
                                <span>Reviews</span>
                                <span className="rounded-full bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 px-2 py-0.5 text-xs font-bold">
                                    {localReviews.length}
                                </span>
                                {activeTab === "reviews" && (
                                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-cyan-500" />
                                )}
                            </button>
                        </div>

                        {/* Tab Content Panel */}
                        <div className="p-6 sm:p-8 lg:p-10">
                            {/* 1. Specification Tab */}
                            {activeTab === "spec" && (
                                <div className="space-y-8 animate-fade-in">
                                    <div>
                                        <h3 className="text-lg font-bold text-fg mb-1">
                                            Technical Specifications
                                        </h3>
                                        <p className="text-xs text-muted mb-6">
                                            Verified hardware specifications and performance metrics for{" "}
                                            {product.name}.
                                        </p>
                                    </div>

                                    {product.specifications && product.specifications.length > 0 ? (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            {product.specifications.map((group, gIdx) => (
                                                <div
                                                    key={`spec-group-${gIdx}`}
                                                    className="rounded-xl border border-border/70 overflow-hidden bg-bg/50"
                                                >
                                                    <div className="bg-muted/30 px-4 py-2.5 border-b border-border/70">
                                                        <h4 className="text-xs font-bold uppercase tracking-wider text-fg">
                                                            {group.group}
                                                        </h4>
                                                    </div>
                                                    <div className="divide-y divide-border/40">
                                                        {group.items.map((item, iIdx) => (
                                                            <div
                                                                key={`spec-item-${iIdx}`}
                                                                className="grid grid-cols-1 sm:grid-cols-3 px-4 py-3 text-xs gap-1"
                                                            >
                                                                <span className="font-semibold text-muted">
                                                                    {item.label}
                                                                </span>
                                                                <span className="sm:col-span-2 text-fg font-medium">
                                                                    {item.value}
                                                                </span>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="rounded-xl border border-border/70 p-6 bg-bg/50">
                                            <p className="text-sm text-muted">
                                                Standard hardware specifications apply. Contact our technical team
                                                for detailed datasheet or pinout inquiries.
                                            </p>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* 2. Warranty Details Tab */}
                            {activeTab === "warranty" && (
                                <div className="space-y-8 animate-fade-in">
                                    {/* Warranty Banner */}
                                    <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-r from-cyan-500/10 via-card to-purple-500/10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div className="flex items-center gap-4">
                                            <div className="h-14 w-14 rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 flex items-center justify-center shrink-0">
                                                <ShieldCheck className="h-8 w-8" />
                                            </div>
                                            <div>
                                                <span className="text-xs font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 block mb-1">
                                                    Official Guarantee
                                                </span>
                                                <h3 className="text-xl sm:text-2xl font-black text-fg font-display">
                                                    {product.warranty?.duration || "2 Years Official Warranty"}
                                                </h3>
                                                <p className="text-xs text-muted mt-0.5">
                                                    {product.warranty?.type ||
                                                        "Manufacturer Replacement & Hardware Warranty"}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex flex-col sm:flex-row gap-2.5">
                                            <a
                                                href={`tel:${product.warranty?.hotline || "+94112345678"}`}
                                                className="inline-flex items-center justify-center gap-2 rounded-xl bg-card border border-border hover:border-cyan-500/40 px-4 py-2.5 text-xs font-bold text-fg transition-colors"
                                            >
                                                <span>Hotline: {product.warranty?.hotline || "+94 11 234 5678"}</span>
                                            </a>
                                        </div>
                                    </div>

                                    {/* What is covered vs Not Covered */}
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {/* Covered */}
                                        <div className="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-6">
                                            <div className="flex items-center gap-2 mb-4">
                                                <Check className="h-5 w-5 text-emerald-500" />
                                                <h4 className="text-sm font-bold text-fg uppercase tracking-wide">
                                                    What is Covered
                                                </h4>
                                            </div>
                                            <ul className="space-y-2.5 text-xs text-muted">
                                                {(
                                                    product.warranty?.covered || [
                                                        "Factory manufacturing defects and hardware malfunctions",
                                                        "Internal component failure during normal operation",
                                                        "Controller chip hardware faults and PCB soldering faults",
                                                        "Original in-box cables and included power adapters",
                                                    ]
                                                ).map((item, idx) => (
                                                    <li key={`cov-${idx}`} className="flex items-start gap-2">
                                                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500 mt-1 shrink-0" />
                                                        <span>{item}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>

                                        {/* Not Covered */}
                                        <div className="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6">
                                            <div className="flex items-center gap-2 mb-4">
                                                <ShieldAlert className="h-5 w-5 text-rose-500" />
                                                <h4 className="text-sm font-bold text-fg uppercase tracking-wide">
                                                    What is Not Covered
                                                </h4>
                                            </div>
                                            <ul className="space-y-2.5 text-xs text-muted">
                                                {(
                                                    product.warranty?.notCovered || [
                                                        "Physical damage, broken pins, cracks, or burnt electrical tracks",
                                                        "Liquid spillage, corrosion, humidity exposure, or rust",
                                                        "Unauthorized disassembly, firmware modification, or third-party repair",
                                                        "Mutilated serial number barcode stickers",
                                                    ]
                                                ).map((item, idx) => (
                                                    <li key={`notcov-${idx}`} className="flex items-start gap-2">
                                                        <span className="h-1.5 w-1.5 rounded-full bg-rose-500 mt-1 shrink-0" />
                                                        <span>{item}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    </div>

                                    {/* Claim Process */}
                                    <div className="rounded-xl border border-border/70 bg-bg/50 p-6">
                                        <h4 className="text-sm font-bold text-fg uppercase tracking-wide mb-3 flex items-center gap-2">
                                            <HelpCircle className="h-4 w-4 text-cyan-500" />
                                            <span>Warranty Claim Procedure</span>
                                        </h4>
                                        <p className="text-xs leading-relaxed text-muted mb-4">
                                            {product.warranty?.claimProcess ||
                                                "Visit any ComLink Flagship Store or send via insured courier with your original purchase receipt/invoice. Warranty claims are verified and processed within 3 to 5 business days."}
                                        </p>
                                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                            <div className="p-3 rounded-lg bg-card border border-border/60">
                                                <strong className="block text-fg font-bold mb-1">
                                                    Step 1: Check Invoice
                                                </strong>
                                                <span className="text-muted">
                                                    Ensure invoice number and serial match the original box.
                                                </span>
                                            </div>
                                            <div className="p-3 rounded-lg bg-card border border-border/60">
                                                <strong className="block text-fg font-bold mb-1">
                                                    Step 2: Drop-Off / Send
                                                </strong>
                                                <span className="text-muted">
                                                    Bring to our repair center or ship with safe packing.
                                                </span>
                                            </div>
                                            <div className="p-3 rounded-lg bg-card border border-border/60">
                                                <strong className="block text-fg font-bold mb-1">
                                                    Step 3: Quick Replacement
                                                </strong>
                                                <span className="text-muted">
                                                    Inspection and replacement unit issued within 3-5 days.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* 3. Reviews Tab */}
                            {activeTab === "reviews" && (
                                <div className="space-y-8 animate-fade-in">
                                    {/* Rating Summary Card */}
                                    <div className="grid grid-cols-1 md:grid-cols-12 gap-6 p-6 rounded-2xl border border-border/70 bg-bg/60 items-center">
                                        <div className="md:col-span-4 flex flex-col items-center justify-center text-center p-4 border-b md:border-b-0 md:border-r border-border/60">
                                            <span className="text-5xl font-black font-display text-fg tracking-tight">
                                                {Number(product.rating).toFixed(1)}
                                            </span>
                                            <div className="flex items-center gap-1 my-2">
                                                {[1, 2, 3, 4, 5].map((s) => (
                                                    <Star
                                                        key={`sum-star-${s}`}
                                                        className={cn(
                                                            "h-5 w-5",
                                                            s <= Math.round(product.rating)
                                                                ? "fill-amber-400 text-amber-400"
                                                                : "text-muted/30",
                                                        )}
                                                    />
                                                ))}
                                            </div>
                                            <span className="text-xs text-muted font-medium">
                                                Based on {localReviews.length} verified ratings
                                            </span>
                                        </div>

                                        <div className="md:col-span-8 space-y-2">
                                            {[
                                                { stars: 5, pct: 85 },
                                                { stars: 4, pct: 12 },
                                                { stars: 3, pct: 3 },
                                                { stars: 2, pct: 0 },
                                                { stars: 1, pct: 0 },
                                            ].map((row) => (
                                                <div key={`pct-${row.stars}`} className="flex items-center gap-3 text-xs">
                                                    <span className="w-12 font-semibold text-fg">
                                                        {row.stars} Stars
                                                    </span>
                                                    <div className="flex-1 h-2 rounded-full bg-muted/20 overflow-hidden">
                                                        <div
                                                            className="h-full bg-amber-400 rounded-full"
                                                            style={{ width: `${row.pct}%` }}
                                                        />
                                                    </div>
                                                    <span className="w-8 text-right text-muted font-mono">
                                                        {row.pct}%
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Reviews list */}
                                    <div className="space-y-4">
                                        <h4 className="text-sm font-bold text-fg uppercase tracking-wide">
                                            Customer Feedback ({localReviews.length})
                                        </h4>

                                        <div className="space-y-3">
                                            {localReviews.map((rev) => (
                                                <div
                                                    key={rev.id}
                                                    className="p-5 rounded-xl border border-border/70 bg-card hover:border-cyan-500/30 transition-colors"
                                                >
                                                    <div className="flex items-start justify-between gap-4 mb-2">
                                                        <div>
                                                            <div className="flex items-center gap-2">
                                                                <span className="font-bold text-fg text-sm">
                                                                    {rev.author}
                                                                </span>
                                                                {/* {rev.verified && (
                                                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 px-2 py-0.2 text-[10px] font-bold">
                                                                        <Check className="h-3 w-3" /> Verified Buyer
                                                                    </span>
                                                                )} */}
                                                            </div>
                                                            <div className="flex items-center gap-1 mt-1">
                                                                {[1, 2, 3, 4, 5].map((s) => (
                                                                    <Star
                                                                        key={`rev-star-${s}`}
                                                                        className={cn(
                                                                            "h-3.5 w-3.5",
                                                                            s <= rev.rating
                                                                                ? "fill-amber-400 text-amber-400"
                                                                                : "text-muted/30",
                                                                        )}
                                                                    />
                                                                ))}
                                                            </div>
                                                        </div>
                                                        <span className="text-xs text-muted">{rev.date}</span>
                                                    </div>

                                                    {rev.title && (
                                                        <h5 className="font-semibold text-fg text-sm mb-1">
                                                            {rev.title}
                                                        </h5>
                                                    )}
                                                    <p className="text-xs leading-relaxed text-muted">
                                                        {rev.comment}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Write a Review Section */}
                                    <div className="rounded-2xl border border-border/80 bg-bg/50 p-6 sm:p-8 mt-8">
                                        <h4 className="text-base font-bold text-fg mb-1">
                                            Write a Review
                                        </h4>
                                        <p className="text-xs text-muted mb-6">
                                            Share your experience with other PC builders and gamers.
                                        </p>

                                        <form onSubmit={handleSubmitReview} className="space-y-4">
                                            <div>
                                                <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                                    Your Rating
                                                </label>
                                                <div className="flex items-center gap-2">
                                                    {[1, 2, 3, 4, 5].map((val) => (
                                                        <button
                                                            key={`write-star-${val}`}
                                                            type="button"
                                                            onClick={() => setNewReviewRating(val)}
                                                            className="p-1 hover:scale-110 active:scale-95 transition-transform cursor-pointer"
                                                        >
                                                            <Star
                                                                className={cn(
                                                                    "h-6 w-6",
                                                                    val <= newReviewRating
                                                                        ? "fill-amber-400 text-amber-400"
                                                                        : "text-muted/40",
                                                                )}
                                                            />
                                                        </button>
                                                    ))}
                                                    <span className="text-xs font-bold text-fg ml-2 font-mono">
                                                        {newReviewRating} of 5 Stars
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                                        Your Name
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={newReviewAuthor}
                                                        onChange={(e) => setNewReviewAuthor(e.target.value)}
                                                        placeholder="e.g. Kasun Silva"
                                                        className="w-full rounded-xl border border-border bg-card px-4 py-2.5 text-xs text-fg focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-colors"
                                                        required
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                                        Review Headline
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={newReviewTitle}
                                                        onChange={(e) => setNewReviewTitle(e.target.value)}
                                                        placeholder="e.g. Unbelievable performance and cooling!"
                                                        className="w-full rounded-xl border border-border bg-card px-4 py-2.5 text-xs text-fg focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-colors"
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <label className="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">
                                                    Detailed Review
                                                </label>
                                                <textarea
                                                    rows={4}
                                                    value={newReviewComment}
                                                    onChange={(e) => setNewReviewComment(e.target.value)}
                                                    placeholder="Tell others what you liked, temperatures, packaging, and setup..."
                                                    className="w-full rounded-xl border border-border bg-card px-4 py-2.5 text-xs text-fg focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-colors"
                                                    required
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                className="inline-flex items-center gap-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider px-6 py-3 shadow-md hover:shadow-cyan-500/25 active:scale-95 transition-all cursor-pointer"
                                            >
                                                <Send className="h-4 w-4" />
                                                <span>Submit Review</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* ========================================================== */}
                {/* NEXT SECTION: SAME SUBCATEGORY PRODUCTS CAROUSEL           */}
                {/* "in there i need to show same subcategory products         */}
                {/* at this moment show some product cards with carousel       */}
                {/* later we will build logics"                                */}
                {/* ========================================================== */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20 sm:mt-24">
                    <div className="flex items-end justify-between mb-8 pb-4 border-b border-border/40">
                        <div>
                            {/* <span className="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 mb-2">
                                Same Subcategory
                            </span> */}
                            <h2 className="font-display text-2xl sm:text-3xl font-extrabold text-fg tracking-tight">
                                MORE IN {((product.subCategory && !["laptops", "monitors & displays", "software"].includes(product.category.toLowerCase().trim())) ? product.subCategory : product.category).toUpperCase()}
                            </h2>
                            <p className="text-xs text-muted mt-1">
                                Explore similar products and accessories from the {(product.subCategory && !["laptops", "monitors & displays", "software"].includes(product.category.toLowerCase().trim())) ? product.subCategory : product.category} lineup.
                            </p>
                        </div>

                        {/* Carousel Navigation Buttons */}
                        {relatedProducts.length > visibleCards && (
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={handlePrevCarousel}
                                    aria-label="Previous products"
                                    className="h-10 w-10 rounded-full border border-border bg-card hover:border-cyan-500 hover:bg-cyan-500/10 text-fg flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95"
                                >
                                    <ChevronLeft className="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    onClick={handleNextCarousel}
                                    aria-label="Next products"
                                    className="h-10 w-10 rounded-full border border-border bg-card hover:border-cyan-500 hover:bg-cyan-500/10 text-fg flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95"
                                >
                                    <ChevronRight className="h-5 w-5" />
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Carousel Container */}
                    {relatedProducts.length > 0 ? (
                        <div className="relative overflow-hidden">
                            <div
                                className="flex transition-transform duration-500 ease-out"
                                style={{
                                    transform: `translateX(-${(carouselIndex * 100) / visibleCards}%)`,
                                }}
                            >
                                {relatedProducts.map((relProduct, rIdx) => (
                                    <div
                                        key={`rel-prod-${relProduct.id || rIdx}`}
                                        className="shrink-0 px-2.5"
                                        style={{ width: `${100 / visibleCards}%` }}
                                    >
                                        <ProductCard product={relProduct} />
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="p-8 text-center rounded-2xl border border-border/70 bg-card/60">
                            <p className="text-sm text-muted">
                                No additional products found in this subcategory at this moment.
                            </p>
                        </div>
                    )}
                </div>

                {/* ========================================================== */}
                {/* BRAND MARQUEE SECTION                                      */}
                {/* ========================================================== */}
                <BrandMarquee className="mt-20 sm:mt-24 border-t border-border/40" />
            </div>
        </>
    );
}
