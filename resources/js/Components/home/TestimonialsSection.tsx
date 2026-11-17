import { cn } from "@/utils/cn";
import { Quote, Star } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

export interface TestimonialItem {
    id: string | number;
    name: string;
    location?: string;
    role?: string;
    avatar?: string | null;
    avatar_url?: string | null;
    rating: number;
    quote?: string;
    description?: string;
}

interface TestimonialsSectionProps {
    testimonials?: TestimonialItem[];
}

const getInitials = (name: string) =>
    name
        .split(" ")
        .map((part) => part[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();

/* -------------------------------------------------------------------------- */
/*  Module-level frame cache (prevents reloading frames across visits)        */
/* -------------------------------------------------------------------------- */
const GLOBAL_TESTIMONIALS_FRAME_CACHE = new Map<number, HTMLImageElement>();
let globalActiveLoads = 0;
const MAX_CONCURRENT_BG_LOADS = 4;
const TOTAL_FRAMES = 192;
const FRAMES_FOLDER = "/images/testimonials-frames";

const getFramePath = (index: number) => {
    const padded = String(index).padStart(4, "0");
    return `${FRAMES_FOLDER}/frame-${padded}.jpg`;
};

const loadSingleFrame = (index: number): Promise<HTMLImageElement | null> => {
    if (GLOBAL_TESTIMONIALS_FRAME_CACHE.has(index)) {
        return Promise.resolve(GLOBAL_TESTIMONIALS_FRAME_CACHE.get(index)!);
    }

    return new Promise<HTMLImageElement | null>((resolve) => {
        const img = new Image();
        img.src = getFramePath(index);
        img.onload = () => {
            GLOBAL_TESTIMONIALS_FRAME_CACHE.set(index, img);
            resolve(img);
        };
        img.onerror = () => {
            resolve(null);
        };
    });
};

/* -------------------------------------------------------------------------- */
/*  Card Component                                                            */
/* -------------------------------------------------------------------------- */

function TestimonialCard({ item }: { item: TestimonialItem }) {
    const text = item.quote || item.description || "";
    const roleOrLocation = item.location || item.role || "Verified Customer";
    const avatar = item.avatar || item.avatar_url;

    return (
        <div className="mb-4 rounded-xl border border-slate-200/80 dark:border-white/10 bg-white/92 dark:bg-[#0c1322]/88 backdrop-blur-md p-6 transition-all duration-300 hover:border-cyan-500/70 hover:shadow-lg dark:hover:shadow-cyan-500/10 shadow-sm">
            <div className="mb-4 flex items-center justify-between">
                <Quote className="h-5 w-5 text-cyan-600/80 dark:text-cyan-400/80" aria-hidden="true" />
                <div
                    className="flex items-center gap-0.5"
                    aria-label={`${item.rating} out of 5 stars`}
                >
                    {Array.from({ length: 5 }).map((_, i) => (
                        <Star
                            key={i}
                            className={cn(
                                "h-4 w-4",
                                i < item.rating
                                    ? "fill-cyan-500 text-cyan-500 dark:fill-cyan-400 dark:text-cyan-400"
                                    : "text-slate-200 dark:text-slate-700",
                            )}
                        />
                    ))}
                </div>
            </div>

            <p className="mb-5 text-sm leading-relaxed text-slate-800 dark:text-slate-200 font-normal">
                {text}
            </p>

            <div className="flex items-center gap-3">
                {avatar ? (
                    <img
                        src={avatar}
                        alt={item.name}
                        className="size-9 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                        onError={(e) => {
                            (e.target as HTMLElement).style.display = "none";
                        }}
                    />
                ) : (
                    <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-full bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 text-xs font-bold border border-cyan-500/30">
                        {getInitials(item.name)}
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-semibold text-slate-900 dark:text-white">
                        {item.name}
                    </p>
                    <p className="truncate text-xs text-slate-500 dark:text-slate-400">
                        {roleOrLocation}
                    </p>
                </div>
            </div>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Section Component with Interactive Frame-by-Frame Scroll Animation        */
/* -------------------------------------------------------------------------- */

export default function TestimonialsSection({
    testimonials = [],
}: TestimonialsSectionProps) {
    const sectionRef = useRef<HTMLElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);

    // Animation & frame tracking
    const currentFrameFloat = useRef<number>(1);
    const targetFrame = useRef<number>(1);
    const lastDrawnFrame = useRef<number>(-1);
    const animationFrameId = useRef<number | null>(null);

    // Draw frame on canvas with object-fit: cover scaling and top vignette crop
    const renderFrame = (img: HTMLImageElement | null) => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext("2d");
        if (!ctx) return;

        const { width, height } = canvas;
        ctx.clearRect(0, 0, width, height);

        if (img && img.complete && img.naturalWidth > 0) {
            const rawW = img.naturalWidth;
            const rawH = img.naturalHeight;

            // Crop 8% off the top of the video to eliminate the dark camera vignette
            const cropTop = rawH * 0.08;
            const srcX = 0;
            const srcY = cropTop;
            const srcW = rawW;
            const srcH = rawH - cropTop;

            const hRatio = width / srcW;
            const vRatio = height / srcH;
            const ratio = Math.max(hRatio, vRatio);

            const drawW = srcW * ratio;
            const drawH = srcH * ratio;
            const shiftX = (width - drawW) / 2;
            const shiftY = (height - drawH) / 2;

            ctx.drawImage(img, srcX, srcY, srcW, srcH, shiftX, shiftY, drawW, drawH);
        }
    };

    // Responsive Canvas Resizing with Retina DevicePixelRatio
    useEffect(() => {
        const updateCanvasSize = () => {
            const canvas = canvasRef.current;
            if (!canvas) return;
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;

            const activeIndex = Math.round(currentFrameFloat.current);
            const img = GLOBAL_TESTIMONIALS_FRAME_CACHE.get(activeIndex) || null;
            renderFrame(img);
        };

        updateCanvasSize();
        window.addEventListener("resize", updateCanvasSize);
        return () => window.removeEventListener("resize", updateCanvasSize);
    }, []);

    // Preload frames progressively (Keyframes first, then remaining)
    useEffect(() => {
        let isMounted = true;

        // 1. Instantly display Frame 1 if already cached, or load frame 1
        if (GLOBAL_TESTIMONIALS_FRAME_CACHE.has(1)) {
            renderFrame(GLOBAL_TESTIMONIALS_FRAME_CACHE.get(1)!);
        } else {
            loadSingleFrame(1).then((img) => {
                if (!isMounted) return;
                if (img) renderFrame(img);
            });
        }

        // 2. Delayed background queue to prevent choking network
        const timer = setTimeout(() => {
            if (!isMounted) return;

            // Load every 4th frame first (~48 keyframes)
            const keyframes: number[] = [];
            for (let i = 2; i <= TOTAL_FRAMES; i += 4) {
                if (!GLOBAL_TESTIMONIALS_FRAME_CACHE.has(i)) keyframes.push(i);
            }

            // Intermediate frames
            const remaining: number[] = [];
            for (let i = 2; i <= TOTAL_FRAMES; i++) {
                if (!GLOBAL_TESTIMONIALS_FRAME_CACHE.has(i) && !keyframes.includes(i)) {
                    remaining.push(i);
                }
            }

            const queue = [...keyframes, ...remaining];
            let qIdx = 0;

            const processQueue = () => {
                if (!isMounted || qIdx >= queue.length) return;

                while (globalActiveLoads < MAX_CONCURRENT_BG_LOADS && qIdx < queue.length) {
                    const idx = queue[qIdx++];
                    if (GLOBAL_TESTIMONIALS_FRAME_CACHE.has(idx)) continue;

                    globalActiveLoads++;
                    loadSingleFrame(idx).then(() => {
                        globalActiveLoads--;
                        setTimeout(processQueue, 20);
                    });
                }
            };

            processQueue();
        }, 500);

        return () => {
            isMounted = false;
            clearTimeout(timer);
        };
    }, []);

    // Smooth Frame-by-Frame Render Loop (Forward & Reverse)
    const startRenderLoop = () => {
        if (animationFrameId.current) return;

        const loop = () => {
            const diff = targetFrame.current - currentFrameFloat.current;
            const isSettled = Math.abs(diff) < 0.01;

            if (!isSettled) {
                // Smooth damping glide
                currentFrameFloat.current += diff * 0.12;
            } else {
                currentFrameFloat.current = targetFrame.current;
            }

            const frameToDraw = Math.min(
                TOTAL_FRAMES,
                Math.max(1, Math.round(currentFrameFloat.current)),
            );

            if (frameToDraw !== lastDrawnFrame.current) {
                lastDrawnFrame.current = frameToDraw;

                let imgToDraw = GLOBAL_TESTIMONIALS_FRAME_CACHE.get(frameToDraw);
                if (!imgToDraw) {
                    loadSingleFrame(frameToDraw);
                    for (let offset = 1; offset < 20; offset++) {
                        const before = GLOBAL_TESTIMONIALS_FRAME_CACHE.get(frameToDraw - offset);
                        if (before) { imgToDraw = before; break; }
                        const after = GLOBAL_TESTIMONIALS_FRAME_CACHE.get(frameToDraw + offset);
                        if (after) { imgToDraw = after; break; }
                    }
                }

                renderFrame(imgToDraw || null);
            }

            if (isSettled) {
                animationFrameId.current = null;
                return;
            }

            animationFrameId.current = requestAnimationFrame(loop);
        };

        animationFrameId.current = requestAnimationFrame(loop);
    };

    // Track scroll position of the section:
    // When scrolling DOWN -> frames move forward (1 to 192)
    // When scrolling UP   -> frames move in reverse (192 to 1)
    useEffect(() => {
        const handleScroll = () => {
            const section = sectionRef.current;
            if (!section) return;

            const rect = section.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            // Section scroll range through the viewport
            const totalDistance = windowHeight + rect.height;
            const scrolled = windowHeight - rect.top;

            // Normalized progress from 0.0 (enters view) to 1.0 (leaves view)
            const progress = Math.min(1, Math.max(0, scrolled / totalDistance));

            // Map progress to frame index 1..192
            const target = 1 + progress * (TOTAL_FRAMES - 1);
            targetFrame.current = target;

            startRenderLoop();
        };

        window.addEventListener("scroll", handleScroll, { passive: true });
        handleScroll(); // initialize

        return () => {
            window.removeEventListener("scroll", handleScroll);
            if (animationFrameId.current) {
                cancelAnimationFrame(animationFrameId.current);
                animationFrameId.current = null;
            }
        };
    }, []);

    if (!testimonials || testimonials.length === 0) {
        return null;
    }

    // Distribute testimonials across 3 columns
    const col1: TestimonialItem[] = [];
    const col2: TestimonialItem[] = [];
    const col3: TestimonialItem[] = [];

    testimonials.forEach((item, index) => {
        if (index % 3 === 0) col1.push(item);
        else if (index % 3 === 1) col2.push(item);
        else col3.push(item);
    });

    if (col2.length === 0) col2.push(...col1);
    if (col3.length === 0) col3.push(...col1);

    // Ensure at least 3 items in each column so continuous scrolling is smooth
    const padItems = (items: TestimonialItem[], min = 3): TestimonialItem[] => {
        if (items.length === 0) return [];
        let res = [...items];
        while (res.length < min) {
            res = [...res, ...items];
        }
        return res;
    };

    const columns = [
        {
            items: padItems(col1),
            className: "animate-testimonial-scroll-1",
        },
        {
            items: padItems(col2),
            className: "hidden md:block animate-testimonial-scroll-2",
        },
        {
            items: padItems(col3),
            className: "hidden lg:block animate-testimonial-scroll-3",
        },
    ];

    return (
        <section
            ref={sectionRef}
            className="relative overflow-hidden bg-bg border-t border-border/40 px-4 py-16 md:py-24 transition-colors duration-300"
            style={{ contain: "paint" }}
        >
            {/* Scoped keyframes + animation classes */}
            <style>{`
                @keyframes testimonial-scroll-up {
                    0% { transform: translateY(0); }
                    100% { transform: translateY(-50%); }
                }
                .animate-testimonial-scroll-1 { animation: testimonial-scroll-up 25s linear infinite; }
                .animate-testimonial-scroll-2 { animation: testimonial-scroll-up 30s linear infinite; }
                .animate-testimonial-scroll-3 { animation: testimonial-scroll-up 20s linear infinite; }
                #testimonial-columns:hover > div { animation-play-state: paused; }
                @media (prefers-reduced-motion: reduce) {
                    .animate-testimonial-scroll-1,
                    .animate-testimonial-scroll-2,
                    .animate-testimonial-scroll-3 { animation: none; }
                }
            `}</style>

            {/* ========================================================================= */}
            {/* 1. INTERACTIVE INK VIDEO SCROLL CANVAS BACKGROUND                         */}
            {/* ========================================================================= */}
            <div
                className="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
                style={{ contain: "paint" }}
            >
                <canvas
                    ref={canvasRef}
                    className="absolute inset-0 w-full h-full object-cover transition-opacity duration-500 opacity-60 dark:opacity-35 mix-blend-multiply dark:mix-blend-screen dark:invert dark:hue-rotate-180"
                />

                {/* Subtle soft boundary highlights */}
                <div className="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-bg/30 to-transparent pointer-events-none" />
                <div className="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-bg/30 to-transparent pointer-events-none" />
            </div>

            {/* ========================================================================= */}
            {/* 2. TESTIMONIALS CONTENT & AUTO-SCROLLING COLUMNS                          */}
            {/* ========================================================================= */}
            <div className="container relative z-10 mx-auto px-4 md:px-6 flex flex-col items-center">
                {/* Heading with high contrast and readable subtitle */}
                <div className="mb-12 text-center max-w-2xl px-4">
                    <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                        Community Feedback
                    </span>
                    <h2 className="mb-3 font-display text-4xl md:text-5xl font-bold tracking-tight text-slate-900 dark:text-white uppercase drop-shadow-[0_1px_2px_rgba(255,255,255,0.8)] dark:drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]">
                        What Our Customers Say
                    </h2>
                    <p className="mx-auto max-w-lg text-sm sm:text-base font-medium text-slate-700 dark:text-slate-300 leading-relaxed drop-shadow-[0_1px_3px_rgba(255,255,255,0.95)] dark:drop-shadow-[0_1px_4px_rgba(0,0,0,0.9)]">
                        Real stories from gamers, creators, and professionals
                        who upgraded their setups with us.
                    </p>
                </div>

                {/* Scrolling columns with smooth CSS mask gradient (no harsh color patches or side margins) */}
                <div className="relative w-full overflow-hidden">
                    <div
                        id="testimonial-columns"
                        className="grid h-[620px] grid-cols-1 gap-5 overflow-hidden md:grid-cols-2 lg:grid-cols-3"
                        style={{
                            maskImage:
                                "linear-gradient(to bottom, transparent 0%, black 8%, black 92%, transparent 100%)",
                            WebkitMaskImage:
                                "linear-gradient(to bottom, transparent 0%, black 8%, black 92%, transparent 100%)",
                        }}
                    >
                        {columns.map((col, colIndex) => (
                            <div key={colIndex} className={col.className}>
                                {/* Duplicated list = seamless infinite loop */}
                                {[...col.items, ...col.items].map(
                                    (item, index) => (
                                        <TestimonialCard
                                            key={`${colIndex}-${item.id}-${index}`}
                                            item={item}
                                        />
                                    ),
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
