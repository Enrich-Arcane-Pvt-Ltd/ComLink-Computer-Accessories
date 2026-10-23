import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import { ChevronLeft, ChevronRight } from "lucide-react";
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
    autoScrollInterval?: number; // ms, default 3500ms
}

export function CategoriesCarousel({
    categories = [],
    title = "SHOP BY CATEGORY",
    subtitle = "Premium Hardware & Accessories",
    description = "Explore our wide range of laptops, PC components, and accessories built for gaming, work, and everyday performance.",
    autoScrollInterval = 3500,
}: CategoriesCarouselProps) {
    const scrollRef = useRef<HTMLDivElement>(null);
    const [canScrollLeft, setCanScrollLeft] = useState(false);
    const [canScrollRight, setCanScrollRight] = useState(false);

    // Mouse drag state
    const isMouseDown = useRef(false);
    const startX = useRef(0);
    const scrollStartX = useRef(0);
    const hasDragged = useRef(false);
    const [isDragging, setIsDragging] = useState(false);

    // Auto-scroll pause state (when user hovers or drags)
    const [isPaused, setIsPaused] = useState(false);

    // Track scrollability to dynamically enable/disable arrows
    const checkScroll = () => {
        const el = scrollRef.current;
        if (!el) return;
        const { scrollLeft, scrollWidth, clientWidth } = el;
        setCanScrollLeft(scrollLeft > 4);
        setCanScrollRight(scrollLeft + clientWidth < scrollWidth - 4);
    };

    useEffect(() => {
        checkScroll();
        const el = scrollRef.current;
        if (!el) return;

        el.addEventListener("scroll", checkScroll, { passive: true });
        window.addEventListener("resize", checkScroll);

        const timer = setTimeout(checkScroll, 300);

        return () => {
            el.removeEventListener("scroll", checkScroll);
            window.removeEventListener("resize", checkScroll);
            clearTimeout(timer);
        };
    }, [categories]);

    // Automatic Scrolling Loop
    useEffect(() => {
        const el = scrollRef.current;
        if (!el || isPaused || isDragging || categories.length === 0) return;

        const interval = setInterval(() => {
            const container = scrollRef.current;
            if (!container) return;

            // Only auto-scroll if content overflows
            if (container.scrollWidth <= container.clientWidth) return;

            const isAtEnd =
                container.scrollLeft + container.clientWidth >=
                container.scrollWidth - 10;

            if (isAtEnd) {
                // Loop smoothly back to the beginning
                container.scrollTo({ left: 0, behavior: "smooth" });
            } else {
                // Advance by approximately 1-2 categories
                const step = 200;
                container.scrollBy({ left: step, behavior: "smooth" });
            }
        }, autoScrollInterval);

        return () => clearInterval(interval);
    }, [isPaused, isDragging, categories.length, autoScrollInterval]);

    // Manual navigation buttons
    const scroll = (direction: "left" | "right") => {
        const el = scrollRef.current;
        if (!el) return;

        const step = el.clientWidth * 0.75;
        el.scrollBy({
            left: direction === "left" ? -step : step,
            behavior: "smooth",
        });
    };

    // Mouse Drag to Scroll Event Handlers
    const handleMouseDown = (e: React.MouseEvent<HTMLDivElement>) => {
        // Only trigger on left-click
        if (e.button !== 0) return;
        const el = scrollRef.current;
        if (!el) return;

        isMouseDown.current = true;
        hasDragged.current = false;
        startX.current = e.pageX;
        scrollStartX.current = el.scrollLeft;
        setIsDragging(true);
    };

    const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
        if (!isMouseDown.current) return;
        const el = scrollRef.current;
        if (!el) return;

        const delta = e.pageX - startX.current;
        if (Math.abs(delta) > 5) {
            hasDragged.current = true;
        }

        // Apply 1-to-1 natural drag movement
        el.scrollLeft = scrollStartX.current - delta;
    };

    const handleMouseUpOrLeave = () => {
        if (!isMouseDown.current) return;
        isMouseDown.current = false;
        setIsDragging(false);

        // Keep hasDragged flag briefly so click event on links can be prevented
        setTimeout(() => {
            hasDragged.current = false;
        }, 80);
    };

    if (!categories || categories.length === 0) {
        return null;
    }

    return (
        <section
            className="py-20 bg-bg relative overflow-hidden"
            onMouseEnter={() => setIsPaused(true)}
            onMouseLeave={() => {
                setIsPaused(false);
                handleMouseUpOrLeave();
            }}
            onTouchStart={() => setIsPaused(true)}
            onTouchEnd={() => {
                // Resume after 2s of inactivity on touch
                setTimeout(() => setIsPaused(false), 2000);
            }}
        >
            {/* Ambient Cyan Glow Flare */}
            <div className="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[320px] bg-cyan-500/5 blur-[120px] rounded-full" />

            <div className="container relative z-10 mx-auto px-4 md:px-6">
                {/* Section Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                    <div className="max-w-2xl">
                        <span className="inline-block text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                            {subtitle}
                        </span>
                        <h2 className="font-display text-3xl md:text-5xl font-bold text-fg tracking-tight">
                            {title}
                        </h2>
                        {description && (
                            <p className="text-muted mt-3 text-sm md:text-base leading-relaxed">
                                {description}
                            </p>
                        )}
                    </div>

                    {/* Navigation Controls & Counter */}
                    <div className="flex items-center gap-3 self-end md:self-auto">
                        <span className="text-xs font-mono text-muted tracking-wider hidden sm:inline-block mr-1">
                            {categories.length} CATEGORIES
                        </span>

                        {/* Arrow Left */}
                        <button
                            onClick={() => scroll("left")}
                            disabled={!canScrollLeft}
                            aria-label="Previous categories"
                            className={cn(
                                "w-10 h-10 rounded-full border flex items-center justify-center transition-all duration-300",
                                canScrollLeft
                                    ? "border-border/80 bg-card hover:border-cyan-500 hover:text-cyan-400 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] text-fg cursor-pointer"
                                    : "border-border/30 bg-card/40 text-muted/30 cursor-not-allowed opacity-40",
                            )}
                        >
                            <ChevronLeft className="w-5 h-5" />
                        </button>

                        {/* Arrow Right */}
                        <button
                            onClick={() => scroll("right")}
                            disabled={!canScrollRight}
                            aria-label="Next categories"
                            className={cn(
                                "w-10 h-10 rounded-full border flex items-center justify-center transition-all duration-300",
                                canScrollRight
                                    ? "border-border/80 bg-card hover:border-cyan-500 hover:text-cyan-400 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] text-fg cursor-pointer"
                                    : "border-border/30 bg-card/40 text-muted/30 cursor-not-allowed opacity-40",
                            )}
                        >
                            <ChevronRight className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Categories Slider Container */}
                <div className="relative group">
                    {/* Soft gradient edge fade on left when scrolled */}
                    <div
                        className={cn(
                            "pointer-events-none absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-bg to-transparent z-10 transition-opacity duration-300",
                            canScrollLeft ? "opacity-100" : "opacity-0",
                        )}
                    />

                    {/* Soft gradient edge fade on right when overflowing */}
                    <div
                        className={cn(
                            "pointer-events-none absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-bg to-transparent z-10 transition-opacity duration-300",
                            canScrollRight ? "opacity-100" : "opacity-0",
                        )}
                    />

                    {/* Horizontal Drag-To-Scroll Track */}
                    <div
                        ref={scrollRef}
                        onMouseDown={handleMouseDown}
                        onMouseMove={handleMouseMove}
                        onMouseUp={handleMouseUpOrLeave}
                        className={cn(
                            "flex items-start gap-4 sm:gap-6 lg:gap-8 overflow-x-auto pb-4 pt-2 px-1 select-none",
                            // Hide scrollbars across browsers
                            "[scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden",
                            // Mouse cursor feedback
                            isDragging
                                ? "cursor-grabbing select-none"
                                : "cursor-grab",
                            // Disable smooth scroll and snap while actively dragging for 1-to-1 instant tracking
                            !isDragging && "scroll-smooth",
                        )}
                        style={{
                            scrollSnapType: isDragging
                                ? "none"
                                : "x mandatory",
                        }}
                    >
                        {categories.map((category) => {
                            const catSlug =
                                category.slug ||
                                category.name
                                    .toLowerCase()
                                    .trim()
                                    .replace(/[^a-z0-9]+/g, "-");

                            return (
                                <Link
                                    key={category.slug || category.name}
                                    href={`/shop?category=${catSlug}`}
                                    onClick={(e) => {
                                        // Prevent link click if user was dragging
                                        if (hasDragged.current) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                        }
                                    }}
                                    className="group/item flex flex-col items-center text-center cursor-pointer flex-shrink-0 w-[120px] sm:w-[136px] lg:w-[148px]"
                                    style={{ scrollSnapAlign: "start" }}
                                    draggable={false}
                                >
                                    {/* Circular Image Container */}
                                    <div
                                        className="
                                            relative rounded-full overflow-hidden
                                            w-[110px] h-[110px]
                                            sm:w-[124px] sm:h-[124px]
                                            lg:w-[136px] lg:h-[136px]
                                            bg-card border border-border/80
                                            transition-all duration-300 ease-out
                                            group-hover/item:border-cyan-500 group-hover/item:ring-4 group-hover/item:ring-cyan-500/20 
                                            group-hover/item:shadow-lg group-hover/item:shadow-cyan-500/20 group-hover/item:-translate-y-1
                                            flex items-center justify-center p-3.5 mb-3
                                        "
                                    >
                                        {/* Soft ambient inner radial glow on hover */}
                                        <div className="pointer-events-none absolute inset-0 bg-radial from-cyan-500/20 via-transparent to-transparent opacity-0 group-hover/item:opacity-100 transition-opacity duration-300" />

                                        <img
                                            src={category.image}
                                            alt={category.name}
                                            loading="lazy"
                                            draggable={false}
                                            onError={(e) => {
                                                const target = e.currentTarget;
                                                const currentSrc = target.src;
                                                if (/[A-Z]/.test(currentSrc)) {
                                                    target.src =
                                                        currentSrc.toLowerCase();
                                                    return;
                                                }
                                                target.src =
                                                    "/images/categories/products.png";
                                            }}
                                            className="h-full w-full object-contain drop-shadow-sm transition-transform duration-500 ease-out group-hover/item:scale-110 pointer-events-none select-none"
                                        />
                                    </div>

                                    {/* Category Label & Details */}
                                    <div className="flex flex-col items-center w-full px-1">
                                        <h3 className="font-display font-bold uppercase tracking-wider text-fg text-xs sm:text-sm group-hover/item:text-cyan-600 dark:group-hover/item:text-cyan-400 transition-colors leading-tight line-clamp-2">
                                            {category.name}
                                        </h3>
                                        <span className="block h-0.5 w-0 bg-cyan-500 transition-all duration-300 group-hover/item:w-6 rounded-full mt-1.5" />
                                        <p className="text-[11px] sm:text-xs text-muted mt-1 font-mono">
                                            {category.count ?? 0} Products
                                        </p>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                </div>
            </div>
        </section>
    );
}

export default CategoriesCarousel;
