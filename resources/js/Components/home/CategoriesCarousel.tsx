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
        viewportWidth < 480
            ? 135
            : viewportWidth < 640
              ? 165
              : viewportWidth < 1024
                ? 205
                : 255;
    const cardWidth =
        viewportWidth < 480
            ? 175
            : viewportWidth < 640
              ? 195
              : viewportWidth < 1024
                ? 220
                : 240;
    const cardHeight =
        viewportWidth < 480
            ? 260
            : viewportWidth < 640
              ? 285
              : viewportWidth < 1024
                ? 320
                : 350;
    const curveFactor = viewportWidth < 640 ? 14 : 20;

    const dragRatio = Math.max(-1, Math.min(1, dragOffset / spacing));

    return (
        <section
            className="py-16 md:py-24 relative overflow-hidden max-w-full w-full select-none bg-gradient-to-b from-[#eef6ff] via-[#f4f9ff] to-[#edf5ff] dark:from-[#070b12] dark:via-[#0b1018] dark:to-[#070b12] text-slate-800 dark:text-slate-200 transition-colors duration-300"
            style={{ contain: "paint" }}
            onMouseEnter={() => setIsPaused(true)}
            onMouseLeave={() => {
                if (!isDraggingRef.current) {
                    setIsPaused(false);
                }
            }}
            tabIndex={0}
            onKeyDown={handleKeyDown}
        >
            {/* ========================================================================= */}
            {/* 1. CREATIVE ABSTRACT GEOMETRIC & FLUID ART BACKGROUND (Enclosed & Contained) */}
            {/* ========================================================================= */}
            <div
                className="pointer-events-none absolute inset-0 z-0 overflow-hidden w-full h-full select-none"
                style={{ contain: "paint" }}
            >
                {/* A. Ambient Atmospheric Glow Blurs */}
                <div className="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] max-w-full h-[480px] bg-gradient-to-tr from-cyan-400/20 via-sky-300/25 to-blue-400/15 dark:from-cyan-500/12 dark:via-blue-600/10 dark:to-cyan-400/10 blur-[130px] rounded-full" />
                <div className="pointer-events-none absolute -top-20 -right-20 w-[460px] max-w-full h-[460px] bg-cyan-400/20 dark:bg-cyan-500/10 blur-[120px] rounded-full" />
                <div className="pointer-events-none absolute -bottom-24 -left-24 w-[480px] max-w-full h-[480px] bg-blue-500/20 dark:bg-blue-600/10 blur-[130px] rounded-full" />

                {/* B. Dynamic SVG Geometric Graphic Layer (Fluid Waves, Circles, Rings, Hatch) */}
                <div className="pointer-events-none absolute inset-0 z-0 overflow-hidden w-full h-full flex items-center justify-center">
                    <svg
                        className="w-full h-full object-cover"
                        viewBox="0 0 1440 700"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        preserveAspectRatio="xMidYMid slice"
                    >
                    <defs>
                        {/* Gradients */}
                        <linearGradient
                            id="blueOrbGrad"
                            x1="0%"
                            y1="0%"
                            x2="100%"
                            y2="100%"
                        >
                            <stop offset="0%" stopColor="#2563eb" />
                            <stop offset="100%" stopColor="#06b6d4" />
                        </linearGradient>

                        <linearGradient
                            id="cyanOrbGrad"
                            x1="0%"
                            y1="0%"
                            x2="100%"
                            y2="100%"
                        >
                            <stop offset="0%" stopColor="#06b6d4" />
                            <stop offset="100%" stopColor="#38bdf8" />
                        </linearGradient>

                        <linearGradient
                            id="fluidWaveGradLight"
                            x1="0%"
                            y1="0%"
                            x2="100%"
                            y2="0%"
                        >
                            <stop
                                offset="0%"
                                stopColor="#38bdf8"
                                stopOpacity="0.12"
                            />
                            <stop
                                offset="50%"
                                stopColor="#06b6d4"
                                stopOpacity="0.15"
                            />
                            <stop
                                offset="100%"
                                stopColor="#2563eb"
                                stopOpacity="0.10"
                            />
                        </linearGradient>

                        {/* Diagonal Stripe Pattern (Inspired by Image 3) */}
                        <pattern
                            id="diagonalStripes"
                            width="12"
                            height="12"
                            patternTransform="rotate(45 0 0)"
                            patternUnits="userSpaceOnUse"
                        >
                            <line
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="12"
                                stroke="#0891b2"
                                strokeWidth="2.5"
                                opacity="0.35"
                            />
                        </pattern>
                    </defs>

                    {/* Fluid Wave 1 (Soft sweeping bottom wave) */}
                    <path
                        d="M -50 320 C 220 220 340 460 680 390 C 1020 320 1180 540 1520 420 L 1520 750 L -50 750 Z"
                        fill="url(#fluidWaveGradLight)"
                        className="dark:opacity-40"
                    />

                    {/* Fluid Wave 2 (Top ambient contour curve) */}
                    <path
                        d="M -50 -40 C 260 140 420 -60 760 80 C 1100 220 1260 40 1520 120 L 1520 -80 L -50 -80 Z"
                        fill="currentColor"
                        opacity="0.04"
                    />

                    {/* Top-Right Vibrant Solid Sphere (Inspired by Image 2) */}
                    <circle
                        cx="1340"
                        cy="110"
                        r="115"
                        fill="url(#blueOrbGrad)"
                        className="opacity-70 dark:opacity-60"
                    />

                    {/* Top-Right Hollow Geometric Ring */}
                    <circle
                        cx="1240"
                        cy="150"
                        r="140"
                        stroke="#0891b2"
                        strokeWidth="3.5"
                        opacity="0.25"
                    />

                    {/* Bottom-Left Solid Vibrant Cyan Sphere */}
                    <circle
                        cx="90"
                        cy="630"
                        r="120"
                        fill="url(#cyanOrbGrad)"
                        className="opacity-60 dark:opacity-50"
                    />

                    {/* Center-Left Hollow Geometric Ring */}
                    <circle
                        cx="160"
                        cy="430"
                        r="110"
                        stroke="#06b6d4"
                        strokeWidth="3"
                        opacity="0.22"
                    />

                    {/* Concentric Modern Rings (Bottom-Right) */}
                    <circle
                        cx="1280"
                        cy="590"
                        r="95"
                        stroke="currentColor"
                        strokeWidth="2.5"
                        opacity="0.18"
                    />
                    <circle
                        cx="1280"
                        cy="590"
                        r="55"
                        stroke="#0891b2"
                        strokeWidth="2"
                        opacity="0.25"
                    />

                    {/* Diagonal Pinstripe Hatched Circle (Inspired by Image 3) */}
                    <circle
                        cx="980"
                        cy="95"
                        r="45"
                        fill="url(#diagonalStripes)"
                        className="opacity-70 dark:opacity-60"
                    />

                    {/* Small Concentrated 4x6 Dot-Matrix Cluster (Upper-Left Accent, Inspired by Image 3) */}
                    <g
                        transform="translate(110, 85)"
                        className="fill-cyan-600 dark:fill-cyan-400 opacity-40 dark:opacity-30"
                    >
                        {[0, 1, 2, 3].map((row) =>
                            [0, 1, 2, 3, 4, 5].map((col) => (
                                <circle
                                    key={`${row}-${col}`}
                                    cx={col * 14}
                                    cy={row * 14}
                                    r="2"
                                />
                            ))
                        )}
                    </g>

                    {/* Modern 45° Diagonal Accent Lines (Inspired by Image 2 & 3) */}
                    <line
                        x1="640"
                        y1="90"
                        x2="750"
                        y2="200"
                        stroke="#0891b2"
                        strokeWidth="2"
                        opacity="0.25"
                    />
                    <line
                        x1="1020"
                        y1="510"
                        x2="1140"
                        y2="630"
                        stroke="#38bdf8"
                        strokeWidth="2"
                        opacity="0.25"
                    />
                    <line
                        x1="280"
                        y1="190"
                        x2="340"
                        y2="250"
                        stroke="#06b6d4"
                        strokeWidth="1.8"
                        opacity="0.22"
                    />

                    {/* Floating Accent Spheres & Beads */}
                    <circle
                        cx="680"
                        cy="370"
                        r="14"
                        fill="#0891b2"
                        className="opacity-80 dark:opacity-90 shadow-lg"
                    />
                    <circle
                        cx="710"
                        cy="130"
                        r="8"
                        fill="#38bdf8"
                        className="opacity-60"
                    />
                    <circle
                        cx="290"
                        cy="160"
                        r="9"
                        fill="#2563eb"
                        className="opacity-50"
                    />
                    <circle
                        cx="1210"
                        cy="350"
                        r="7"
                        fill="#06b6d4"
                        className="opacity-70"
                    />
                </svg>
            </div>
        </div>

            <div className="container relative z-10 mx-auto px-4 md:px-6">
                {/* Section Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 md:mb-12 gap-4">
                    <div className="max-w-2xl">
                        <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                            
                            {subtitle}
                        </span>
                        <h2 className="font-display text-3xl md:text-5xl font-bold text-slate-900 dark:text-white tracking-tight">
                            {title}
                        </h2>
                        {description && (
                            <p className="text-slate-600 dark:text-muted mt-2.5 text-sm md:text-base leading-relaxed">
                                {description}
                            </p>
                        )}
                    </div>

                    
                </div>

                {/* 3D CAROUSEL STAGE (Generous height, no clipping, contain paint, no horizontal scroll) */}
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
                    className="relative w-full max-w-full h-[410px] sm:h-[460px] md:h-[500px] flex items-center justify-center overflow-hidden py-6"
                    style={{
                        touchAction: "pan-y",
                        contain: "paint",
                        isolation: "isolate",
                    }}
                >
                    {/* Cards Render */}
                    <div
                        className="relative w-full max-w-full h-full flex items-center justify-center overflow-hidden"
                        style={{
                            contain: "paint",
                        }}
                    >
                        {categories.map((category, index) => {
                            // Circular diff relative to active center index
                            let diff = index - activeIndex;
                            if (diff > count / 2) diff -= count;
                            if (diff < -count / 2) diff += count;

                            // Apply live drag offset to smooth visual transition
                            const effectiveDiff = diff + dragRatio;
                            const absDiff = Math.abs(effectiveDiff);

                            // Dynamically limit visible cards based on screen width to prevent horizontal overflow
                            const maxVisibleDiff =
                                viewportWidth < 480
                                    ? 1.25
                                    : viewportWidth < 640
                                      ? 1.55
                                      : viewportWidth < 1024
                                        ? 2.2
                                        : 3.2;
                            if (absDiff > maxVisibleDiff) return null;

                            // Curve trajectory:
                            // Center card (diff=0) is at the lowest curve point and enlarged (scale=1.08)
                            // Left and Right sides curve upwards (negative translateY) and scale down
                            // All cards remain completely upright and straight (2D hardware accelerated)
                            const x = effectiveDiff * spacing;
                            const y = -Math.pow(absDiff, 1.55) * curveFactor;
                            const scale = Math.max(
                                0.72,
                                1.08 - absDiff * 0.12
                            );

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
                                    {/* Card Container (Clean seamless design, no harsh outer margins or borders) */}
                                    <div
                                        className={cn(
                                            "relative h-full w-full overflow-hidden transition-all duration-300",
                                            "rounded-t-[999px] rounded-b-[28px]",
                                            isActive
                                                ? "border-2 border-cyan-400 bg-gradient-to-b from-white via-cyan-50/60 to-white dark:from-cyan-500/20 dark:via-[#0d1626] dark:to-[#070c16] shadow-[0_20px_45px_-8px_rgba(6,182,212,0.38)] dark:shadow-[0_15px_40px_-5px_rgba(6,182,212,0.45)]"
                                                : "border border-white/60 dark:border-white/5 bg-gradient-to-b from-white/90 via-white/80 to-slate-50/70 dark:from-[#0d1626]/85 dark:via-[#0b1220]/85 dark:to-[#070c16]/85 hover:border-cyan-400/50 shadow-[0_10px_25px_rgba(15,23,42,0.06)] dark:shadow-md backdrop-blur-md"
                                        )}
                                    >
                                        {/* Ambient Glow Spotlight behind image */}
                                        <div
                                            className={cn(
                                                "pointer-events-none absolute left-1/2 top-[34%] -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl transition-opacity duration-300",
                                                isActive
                                                    ? "h-48 w-48 bg-cyan-400/25 opacity-90"
                                                    : "h-36 w-36 bg-cyan-500/12 opacity-30"
                                            )}
                                        />

                                        {/* Product Image Showcase (mix-blend-multiply eliminates square white image boxes in light mode) */}
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
                                                    "relative max-h-full w-full object-contain pointer-events-none select-none transition-transform duration-500 mix-blend-multiply dark:mix-blend-normal",
                                                    isActive
                                                        ? "scale-105 drop-shadow-[0_12px_20px_rgba(15,23,42,0.15)] dark:drop-shadow-[0_15px_25px_rgba(0,0,0,0.6)]"
                                                        : "scale-95 opacity-90 drop-shadow-[0_8px_12px_rgba(15,23,42,0.08)] dark:drop-shadow-[0_10px_15px_rgba(0,0,0,0.4)]"
                                                )}
                                            />
                                        </div>

                                        {/* Glass Info Bar */}
                                        <div
                                            className={cn(
                                                "absolute inset-x-3 bottom-3 flex items-center justify-between gap-2 rounded-[20px] pl-3.5 pr-2 py-2 transition-colors",
                                                isActive
                                                    ? "bg-white/95 dark:bg-[#09101d]/90 border border-cyan-400/40 shadow-sm backdrop-blur-md"
                                                    : "bg-white/90 dark:bg-[#0d1626]/85 border border-slate-200/60 dark:border-white/5 backdrop-blur-md"
                                            )}
                                        >
                                            <div className="min-w-0">
                                                <h3
                                                    className={cn(
                                                        "font-display font-bold uppercase tracking-wide text-xs sm:text-[13px] leading-tight line-clamp-2 transition-colors",
                                                        isActive
                                                            ? "text-cyan-600 dark:text-cyan-300"
                                                            : "text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400"
                                                    )}
                                                >
                                                    {category.name}
                                                </h3>
                                                <p className="mt-0.5 text-[10px] sm:text-[11px] text-slate-500 dark:text-muted">
                                                    <span
                                                        className={cn(
                                                            "font-semibold",
                                                            countNum > 0
                                                                ? "text-cyan-600 dark:text-cyan-400"
                                                                : "text-slate-400 dark:text-muted"
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
                                                    "flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300",
                                                    isActive
                                                        ? "bg-cyan-500 text-white shadow-[0_0_12px_rgba(6,182,212,0.5)]"
                                                        : "bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20"
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

                {/* SIMPLE BOTTOM NAVIGATION (Clean Left & Right arrow buttons) */}
                <div className="mt-6 flex items-center justify-center gap-4 relative z-30">
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            prevCategory();
                        }}
                        aria-label="Previous Category"
                        className="w-11 h-11 rounded-full border border-slate-300/80 dark:border-border/80 hover:border-cyan-500 bg-white/90 dark:bg-card/80 hover:bg-cyan-50 dark:hover:bg-cyan-500/10 text-slate-700 dark:text-fg hover:text-cyan-600 dark:hover:text-cyan-400 flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer shadow-md shadow-slate-200/50 dark:shadow-none"
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
                        className="w-11 h-11 rounded-full border border-slate-300/80 dark:border-border/80 hover:border-cyan-500 bg-white/90 dark:bg-card/80 hover:bg-cyan-50 dark:hover:bg-cyan-500/10 text-slate-700 dark:text-fg hover:text-cyan-600 dark:hover:text-cyan-400 flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 cursor-pointer shadow-md shadow-slate-200/50 dark:shadow-none"
                    >
                        <ChevronRight className="w-5 h-5" />
                    </button>
                </div>
            </div>
        </section>
    );
}

export default CategoriesCarousel;
