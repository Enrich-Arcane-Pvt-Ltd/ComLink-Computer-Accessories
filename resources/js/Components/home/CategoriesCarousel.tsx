import { cn } from "@/utils/cn";
import { router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowUpRight, ChevronLeft, ChevronRight, Sparkles } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

export interface CategoryItem {
    id?: number;
    name: string;
    slug?: string;
    count?: number;
    image: string;
}

interface CategoriesCarouselProps {
    categories: CategoryItem[];
    title?: string;
    subtitle?: string;
    description?: string;
    autoScrollInterval?: number; // ms, default 3800ms
}

export function CategoriesCarousel({
    categories = [],
    title = "SHOP BY CATEGORY",
    subtitle = "Premium Hardware & Accessories",
    description = "Explore our wide range of laptops, PC components, and accessories built for gaming, work, and everyday performance.",
    autoScrollInterval = 3800,
}: CategoriesCarouselProps) {
    const [activeIndex, setActiveIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const [isDragging, setIsDragging] = useState(false);
    const [dragOffset, setDragOffset] = useState(0);
    const [viewportWidth, setViewportWidth] = useState(1024);

    const isDraggingRef = useRef(false);
    const startXRef = useRef(0);
    const hasMovedRef = useRef(false);
    const containerRef = useRef<HTMLDivElement>(null);

    // Track viewport width for responsive card spacing
    useEffect(() => {
        if (typeof window === "undefined") return;
        const handleResize = () => setViewportWidth(window.innerWidth);
        handleResize();
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    const count = categories.length;

    // Automatic Continuous Scrolling Loop
    useEffect(() => {
        if (isPaused || isDragging || count <= 1) return;

        const timer = setInterval(() => {
            setActiveIndex((prev) => (prev + 1) % count);
        }, autoScrollInterval);

        return () => clearInterval(timer);
    }, [isPaused, isDragging, count, autoScrollInterval]);

    const nextCategory = () => {
        setActiveIndex((prev) => (prev + 1) % count);
    };

    const prevCategory = () => {
        setActiveIndex((prev) => (prev - 1 + count) % count);
    };

    const goToCategory = (idx: number) => {
        setActiveIndex(idx);
    };

    // Keyboard navigation when container is focused
    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === "ArrowLeft") {
            prevCategory();
        } else if (e.key === "ArrowRight") {
            nextCategory();
        }
    };

    // Mouse & Touch Drag Event Handlers
    const handleDragStart = (clientX: number) => {
        isDraggingRef.current = true;
        startXRef.current = clientX;
        hasMovedRef.current = false;
        setIsDragging(true);
        setIsPaused(true);
    };

    const handleDragMove = (clientX: number) => {
        if (!isDraggingRef.current) return;
        const delta = clientX - startXRef.current;
        if (Math.abs(delta) > 5) {
            hasMovedRef.current = true;
        }
        setDragOffset(delta);
    };

    const handleDragEnd = () => {
        if (!isDraggingRef.current) return;
        isDraggingRef.current = false;
        setIsDragging(false);

        const threshold = 50;
        if (dragOffset < -threshold) {
            nextCategory();
        } else if (dragOffset > threshold) {
            prevCategory();
        }
        setDragOffset(0);

        setTimeout(() => {
            hasMovedRef.current = false;
            setIsPaused(false);
        }, 120);
    };

    if (!categories || categories.length === 0) {
        return null;
    }

    // Responsive geometric spacing along the curve
    const spacing =
        viewportWidth < 640 ? 150 : viewportWidth < 1024 ? 205 : 255;
    const cardWidth =
        viewportWidth < 640 ? 195 : viewportWidth < 1024 ? 220 : 240;
    const cardHeight =
        viewportWidth < 640 ? 285 : viewportWidth < 1024 ? 320 : 350;
    const curveFactor = viewportWidth < 640 ? 14 : 20;

    const dragRatio = Math.max(-1, Math.min(1, dragOffset / spacing));

    return (
        <section
            className="py-16 md:py-24 bg-bg relative overflow-hidden select-none"
            onMouseEnter={() => setIsPaused(true)}
            onMouseLeave={() => {
                if (!isDraggingRef.current) {
                    setIsPaused(false);
                }
            }}
            tabIndex={0}
            onKeyDown={handleKeyDown}
        >
            {/* Ambient Cyan Background Flares */}
            <div className="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[850px] h-[360px] bg-cyan-500/10 blur-[140px] rounded-full" />
            <div className="pointer-events-none absolute left-1/4 top-1/3 w-[300px] h-[200px] bg-blue-600/10 blur-[100px] rounded-full" />

            <div className="container relative z-10 mx-auto px-4 md:px-6">
                {/* Section Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 md:mb-12 gap-4">
                    <div className="max-w-2xl">
                        <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                            
                            {subtitle}
                        </span>
                        <h2 className="font-display text-3xl md:text-5xl font-bold text-fg tracking-tight">
                            {title}
                        </h2>
                        {description && (
                            <p className="text-muted mt-2.5 text-sm md:text-base leading-relaxed">
                                {description}
                            </p>
                        )}
                    </div>

                    
                </div>

                {/* 3D CAROUSEL STAGE (Generous height so cards and shadows never clip) */}
                <div
                    ref={containerRef}
                    onMouseDown={(e) => {
                        if (e.button === 0) handleDragStart(e.clientX);
                    }}
                    onMouseMove={(e) => handleDragMove(e.clientX)}
                    onMouseUp={handleDragEnd}
                    onTouchStart={(e) =>
                        handleDragStart(e.touches[0].clientX)
                    }
                    onTouchMove={(e) =>
                        handleDragMove(e.touches[0].clientX)
                    }
                    onTouchEnd={handleDragEnd}
                    className="relative w-full h-[400px] sm:h-[460px] md:h-[500px] flex items-center justify-center overflow-hidden py-6"
                    style={{
                        perspective: "1200px",
                        perspectiveOrigin: "50% 50%",
                        touchAction: "pan-y",
                    }}
                >
                    {/* Subtle edge fade gradients */}
                    <div className="pointer-events-none absolute left-0 top-0 bottom-0 w-8 sm:w-20 bg-gradient-to-r from-bg via-bg/60 to-transparent z-40" />
                    <div className="pointer-events-none absolute right-0 top-0 bottom-0 w-8 sm:w-20 bg-gradient-to-l from-bg via-bg/60 to-transparent z-40" />

                    {/* Cards Render */}
                    <div
                        className="relative w-full h-full flex items-center justify-center"
                        style={{ transformStyle: "preserve-3d" }}
                    >
                        {categories.map((category, index) => {
                            // Circular diff relative to active center index
                            let diff = index - activeIndex;
                            if (diff > count / 2) diff -= count;
                            if (diff < -count / 2) diff += count;

                            // Apply live drag offset to smooth visual transition
                            const effectiveDiff = diff + dragRatio;
                            const absDiff = Math.abs(effectiveDiff);

                            // Don't render cards that are far around the back
                            if (absDiff > 3.4) return null;

                            // Curve trajectory:
                            // Center card (diff=0) is at the lowest curve point and enlarged (scale=1.08)
                            // Left and Right sides curve upwards (negative translateY) and scale down
                            // All cards remain completely upright and straight (rotateY=0, rotateZ=0)
                            const x = effectiveDiff * spacing;
                            const y = -Math.pow(absDiff, 1.55) * curveFactor;
                            const scale = Math.max(
                                0.72,
                                1.08 - absDiff * 0.12
                            );
                            const translateZ = 40 - absDiff * 50;

                            // Opacity falloff for distant side cards
                            let opacity = 1;
                            if (absDiff > 2.5) {
                                opacity = Math.max(
                                    0,
                                    1 - (absDiff - 2.5) * 1.5
                                );
                            } else if (absDiff > 1.2) {
                                opacity = 0.9 - (absDiff - 1.2) * 0.2;
                            }

                            const isActive =
                                Math.abs(effectiveDiff) < 0.45;
                            const zIndex = Math.round(50 - absDiff * 10);

                            const catSlug =
                                category.slug ||
                                category.name
                                    .toLowerCase()
                                    .trim()
                                    .replace(/[^a-z0-9]+/g, "-");
                            const countNum = category.count ?? 0;

                            return (
                                <motion.div
                                    key={
                                        category.slug ||
                                        category.name ||
                                        index
                                    }
                                    initial={false}
                                    animate={{
                                        x,
                                        y,
                                        scale,
                                        rotateY: 0, // Kept completely straight
                                        rotateZ: 0, // Kept completely straight
                                        z: translateZ,
                                        opacity,
                                    }}
                                    transition={{
                                        type: "spring",
                                        stiffness: 280,
                                        damping: 30,
                                        mass: 0.8,
                                    }}
                                    style={{
                                        position: "absolute",
                                        width: cardWidth,
                                        height: cardHeight,
                                        zIndex,
                                        transformStyle: "preserve-3d",
                                        cursor: "pointer",
                                    }}
                                    onClick={(e) => {
                                        if (hasMovedRef.current) return;
                                        if (isActive) {
                                            router.visit(
                                                `/shop?category=${catSlug}`
                                            );
                                        } else {
                                            e.stopPropagation();
                                            goToCategory(index);
                                        }
                                    }}
                                    className="outline-none"
                                >
                                    {/* Card Container */}
                                    <div
                                        className={cn(
                                            "relative h-full w-full overflow-hidden transition-all duration-300",
                                            "rounded-t-[999px] rounded-b-[28px] border",
                                            isActive
                                                ? "border-cyan-400 bg-gradient-to-b from-cyan-500/20 via-[#0d1626] to-[#070c16] shadow-[0_15px_40px_-5px_rgba(6,182,212,0.45)] ring-2 ring-cyan-400/25"
                                                : "border-border/60 bg-gradient-to-b from-cyan-950/20 via-[#0d1524]/90 to-[#070b14]/90 hover:border-cyan-500/50 shadow-md backdrop-blur-md"
                                        )}
                                    >
                                        {/* Ambient Glow Spotlight behind image */}
                                        <div
                                            className={cn(
                                                "pointer-events-none absolute left-1/2 top-[34%] -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl transition-opacity duration-300",
                                                isActive
                                                    ? "h-48 w-48 bg-cyan-400/25 opacity-90"
                                                    : "h-36 w-36 bg-cyan-500/15 opacity-40"
                                            )}
                                        />

                                        {/* Product Image Showcase (Clean, no active pill overhead) */}
                                        <div className="absolute inset-x-0 top-0 bottom-24 flex items-center justify-center px-6 pt-6">
                                            <img
                                                src={category.image}
                                                alt={category.name}
                                                loading="lazy"
                                                draggable={false}
                                                onError={(e) => {
                                                    const target =
                                                        e.currentTarget;
                                                    const currentSrc =
                                                        target.src;
                                                    if (
                                                        /[A-Z]/.test(currentSrc)
                                                    ) {
                                                        target.src =
                                                            currentSrc.toLowerCase();
                                                        return;
                                                    }
                                                    target.src =
                                                        "/images/categories/products.png";
                                                }}
                                                className={cn(
                                                    "relative max-h-full w-full object-contain pointer-events-none select-none transition-transform duration-500",
                                                    isActive
                                                        ? "scale-105 drop-shadow-[0_15px_25px_rgba(0,0,0,0.6)]"
                                                        : "scale-95 opacity-85 drop-shadow-[0_10px_15px_rgba(0,0,0,0.4)]"
                                                )}
                                            />
                                        </div>

                                        {/* Glass Info Bar */}
                                        <div
                                            className={cn(
                                                "absolute inset-x-3 bottom-3 flex items-center justify-between gap-2 rounded-[20px] border pl-3.5 pr-2 py-2 transition-colors",
                                                isActive
                                                    ? "border-cyan-500/50 bg-[#09101d]/90 backdrop-blur-md shadow-[0_4px_20px_rgba(0,0,0,0.5)]"
                                                    : "border-border/60 bg-bg/70 backdrop-blur-md"
                                            )}
                                        >
                                            <div className="min-w-0">
                                                <h3
                                                    className={cn(
                                                        "font-display font-bold uppercase tracking-wide text-xs sm:text-[13px] leading-tight line-clamp-2 transition-colors",
                                                        isActive
                                                            ? "text-cyan-300"
                                                            : "text-fg group-hover:text-cyan-400"
                                                    )}
                                                >
                                                    {category.name}
                                                </h3>
                                                <p className="mt-0.5 text-[10px] sm:text-[11px] text-muted">
                                                    <span
                                                        className={cn(
                                                            "font-semibold",
                                                            countNum > 0
                                                                ? "text-cyan-400"
                                                                : "text-muted"
                                                        )}
                                                    >
                                                        {countNum}
                                                    </span>{" "}
                                                    {countNum === 1
                                                        ? "product"
                                                        : "products"}
                                                </p>
                                            </div>

                                            {/* Action Arrow Icon */}
                                            <span
                                                className={cn(
                                                    "flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border transition-all duration-300",
                                                    isActive
                                                        ? "bg-cyan-500 text-white border-cyan-400 shadow-[0_0_12px_rgba(6,182,212,0.6)]"
                                                        : "bg-cyan-500/10 text-cyan-400 border-cyan-500/30"
                                                )}
                                            >
                                                <ArrowUpRight className="h-4 w-4" />
                                            </span>
                                        </div>
                                    </div>
                                </motion.div>
                            );
                        })}
                    </div>
                </div>

                {/* SIMPLE BOTTOM NAVIGATION (Clean Left & Right arrow buttons, no dots or heavy background) */}
                <div className="mt-6 flex items-center justify-center gap-4 relative z-30">
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            prevCategory();
                        }}
                        aria-label="Previous Category"
                        className="w-11 h-11 rounded-full border border-border/80 hover:border-cyan-500 bg-card/80 hover:bg-cyan-500/10 text-fg hover:text-cyan-400 flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer shadow-sm"
                    >
                        <ChevronLeft className="w-5 h-5" />
                    </button>

                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            nextCategory();
                        }}
                        aria-label="Next Category"
                        className="w-11 h-11 rounded-full border border-border/80 hover:border-cyan-500 bg-card/80 hover:bg-cyan-500/10 text-fg hover:text-cyan-400 flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer shadow-sm"
                    >
                        <ChevronRight className="w-5 h-5" />
                    </button>
                </div>
            </div>
        </section>
    );
}

export default CategoriesCarousel;
