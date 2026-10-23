import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import { motion, useScroll, useTransform } from "framer-motion";
import {
    ArrowRight,
    ChevronDown,
    Cpu,
    Crosshair,
    Layers,
    RotateCcw,
    Sparkles,
} from "lucide-react";
import React, { useEffect, useMemo, useRef, useState } from "react";
import { Button } from "../ui/Button";

interface Slide {
    id?: number;
    image?: string;
    title?: string;
    subtitle?: string;
    description?: string;
    cta?: string;
    link?: string;
}

interface HeroCarouselProps {
    slides?: Slide[];
    totalFrames?: number;
    framesFolder?: string;
}

// Module-level persistent cache across page navigation (prevents re-fetching 192 frames)
const GLOBAL_FRAME_CACHE = new Map<number, HTMLImageElement>();
let globalActiveLoads = 0;
const MAX_CONCURRENT_BG_LOADS = 2; // Keep low to avoid starving PHP/network

export function HeroCarousel({
    slides = [],
    totalFrames = 192,
    framesFolder = "/images/hero-frames",
}: HeroCarouselProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);

    // Frame cache & loading status
    const [loadedCount, setLoadedCount] = useState<number>(GLOBAL_FRAME_CACHE.size);
    const [hasMissingFrames, setHasMissingFrames] = useState<boolean>(false);
    const [currentFrameDisplay, setCurrentFrameDisplay] = useState<number>(1);
    const [scrollPercentDisplay, setScrollPercentDisplay] =
        useState<number>(0);

    // Animation loop state
    const currentFrameFloat = useRef<number>(1);
    const targetFrame = useRef<number>(1);
    const lastDrawnFrame = useRef<number>(-1);
    const animationFrameId = useRef<number | null>(null);

    // Helper: format frame number e.g. 1 -> "0001"
    const getFramePath = (index: number) => {
        const padded = String(index).padStart(4, "0");
        return `${framesFolder}/frame-${padded}.jpg`;
    };

    // Helper: load a single frame into global cache
    const loadSingleFrame = (index: number, priority: boolean = false) => {
        if (GLOBAL_FRAME_CACHE.has(index)) return Promise.resolve(GLOBAL_FRAME_CACHE.get(index)!);

        return new Promise<HTMLImageElement | null>((resolve) => {
            const img = new Image();
            img.src = getFramePath(index);
            img.onload = () => {
                GLOBAL_FRAME_CACHE.set(index, img);
                setLoadedCount(GLOBAL_FRAME_CACHE.size);
                resolve(img);
            };
            img.onerror = () => {
                resolve(null);
            };
        });
    };

    // Draw frame on canvas with "object-fit: cover" aspect ratio
    const renderFrame = (img: HTMLImageElement | null) => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext("2d");
        if (!ctx) return;

        const { width, height } = canvas;
        ctx.clearRect(0, 0, width, height);

        if (img && img.complete && img.naturalWidth > 0) {
            // Draw actual image scaled to cover
            const imgW = img.naturalWidth;
            const imgH = img.naturalHeight;
            const hRatio = width / imgW;
            const vRatio = height / imgH;
            const ratio = Math.max(hRatio, vRatio);

            const drawW = imgW * ratio;
            const drawH = imgH * ratio;
            const shiftX = (width - drawW) / 2;
            const shiftY = (height - drawH) / 2;

            ctx.drawImage(img, 0, 0, imgW, imgH, shiftX, shiftY, drawW, drawH);
        }
    };

    // Preload frames progressively without choking the network
    useEffect(() => {
        let isMounted = true;

        // 1. Instantly display Frame 1 if already cached, or load only Frame 1 immediately
        if (GLOBAL_FRAME_CACHE.has(1)) {
            renderFrame(GLOBAL_FRAME_CACHE.get(1)!);
        } else {
            loadSingleFrame(1).then((img) => {
                if (!isMounted) return;
                if (img) {
                    renderFrame(img);
                } else {
                    setHasMissingFrames(true);
                    renderFrame(null);
                }
            });
        }

        // 2. Delayed background preloading (starts only after page stabilizes)
        const timer = setTimeout(() => {
            if (!isMounted) return;

            // Preload every 6th frame first for rapid scrub responsiveness (~32 frames)
            const keyframes: number[] = [];
            for (let i = 2; i <= totalFrames; i += 6) {
                if (!GLOBAL_FRAME_CACHE.has(i)) keyframes.push(i);
            }

            // Intermediate frames loaded later
            const remaining: number[] = [];
            for (let i = 2; i <= totalFrames; i++) {
                if (!GLOBAL_FRAME_CACHE.has(i) && !keyframes.includes(i)) {
                    remaining.push(i);
                }
            }

            const queue = [...keyframes, ...remaining];
            let qIdx = 0;

            const processQueue = () => {
                if (!isMounted || qIdx >= queue.length) return;

                while (globalActiveLoads < MAX_CONCURRENT_BG_LOADS && qIdx < queue.length) {
                    const idx = queue[qIdx++];
                    if (GLOBAL_FRAME_CACHE.has(idx)) continue;

                    globalActiveLoads++;
                    loadSingleFrame(idx).then(() => {
                        globalActiveLoads--;
                        // Small polite pause to let browser handle user requests smoothly
                        setTimeout(processQueue, 25);
                    });
                }
            };

            processQueue();
        }, 800);

        return () => {
            isMounted = false;
            clearTimeout(timer);
        };
    }, [totalFrames, framesFolder]);

    // Resize canvas on window resize with devicePixelRatio for ultra-crisp Retina rendering
    useEffect(() => {
        const updateCanvasSize = () => {
            const canvas = canvasRef.current;
            if (!canvas) return;
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;

            // Draw current active frame
            const activeIndex = Math.round(currentFrameFloat.current);
            const img = GLOBAL_FRAME_CACHE.get(activeIndex) || null;
            renderFrame(img);
        };

        updateCanvasSize();
        window.addEventListener("resize", updateCanvasSize);
        return () => window.removeEventListener("resize", updateCanvasSize);
    }, []);

    // Function to wake up loop when scrolling
    const startRenderLoop = () => {
        if (animationFrameId.current) return;

        const loop = () => {
            const diff = targetFrame.current - currentFrameFloat.current;
            const isSettled = Math.abs(diff) < 0.01;

            if (!isSettled) {
                // Ultra-gentle, slow cinematic glide so every frame is crystal clear
                currentFrameFloat.current += diff * 0.11;
            } else {
                currentFrameFloat.current = targetFrame.current;
            }

            const frameToDraw = Math.min(
                totalFrames,
                Math.max(1, Math.round(currentFrameFloat.current)),
            );

            // Update only when integer frame changes to eliminate 60fps React re-renders!
            if (frameToDraw !== lastDrawnFrame.current) {
                lastDrawnFrame.current = frameToDraw;
                setCurrentFrameDisplay(frameToDraw);

                const pct = Math.round(
                    ((frameToDraw - 1) / (totalFrames - 1)) * 100,
                );
                setScrollPercentDisplay(pct);

                // Find frame or closest loaded frame
                let imgToDraw = GLOBAL_FRAME_CACHE.get(frameToDraw);
                if (!imgToDraw) {
                    // Try to load this target frame on demand
                    loadSingleFrame(frameToDraw);
                    for (let offset = 1; offset < 24; offset++) {
                        const before = GLOBAL_FRAME_CACHE.get(frameToDraw - offset);
                        if (before) { imgToDraw = before; break; }
                        const after = GLOBAL_FRAME_CACHE.get(frameToDraw + offset);
                        if (after) { imgToDraw = after; break; }
                    }
                }

                renderFrame(imgToDraw || null);
            }

            // Stop loop when settled to save 100% CPU
            if (isSettled) {
                animationFrameId.current = null;
                return;
            }

            animationFrameId.current = requestAnimationFrame(loop);
        };

        animationFrameId.current = requestAnimationFrame(loop);
    };

    // Track scroll position of the tall section
    useEffect(() => {
        const handleScroll = () => {
            const container = containerRef.current;
            if (!container) return;

            const rect = container.getBoundingClientRect();
            const totalScrollable = rect.height - window.innerHeight;

            if (totalScrollable <= 0) return;

            // Progress from 0 (top of section) to 1 (bottom of section)
            const progress = Math.min(
                1,
                Math.max(0, -rect.top / totalScrollable),
            );

            // Map progress to frames: 0 -> 1 (stable), 1 -> totalFrames (exploded view)
            const target = 1 + progress * (totalFrames - 1);
            targetFrame.current = target;

            // Wake up loop on scroll
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
    }, [totalFrames]);

    // Dynamic HUD Phase description based on scroll percent
    const hudPhase = useMemo(() => {
        if (scrollPercentDisplay < 15) {
            return {
                desc: "Fully assembled high-performance system",
            };
        } else{
            return {
                desc: "Fully assembled high-performance system for your technical needs.",
            };
        }
    }, [scrollPercentDisplay]);

    // Opacity for hero content as user scrolls down into exploded view
    // Fades smoothly out as scroll progress increases so user can marvel at the exploded view
    const heroContentOpacity = Math.max(
        0,
        Math.min(1, 1 - (scrollPercentDisplay / 45) * 1.3),
    );
    const heroContentTransformY = -(scrollPercentDisplay * 0.8);

    // Dynamic exploded view callout opacity (visible during mid/late scroll)
    const explodedCalloutOpacity =
        scrollPercentDisplay > 18
            ? Math.min(1, (scrollPercentDisplay - 18) / 20)
            : 0;

    // Second text section: fades in as the first one fades out, stays until the last frame.
    // The first block reaches 0 opacity at ~35%, so the second starts at 34% to avoid an empty gap.
    const SECOND_FADE_START = 34;
    const SECOND_FADE_END = 46;

    const secondContentOpacity = Math.max(
        0,
        Math.min(
            1,
            (scrollPercentDisplay - SECOND_FADE_START) /
                (SECOND_FADE_END - SECOND_FADE_START),
        ),
    );
    const secondContentTransformY = (1 - secondContentOpacity) * 40;

    return (
        <section
            ref={containerRef}
            className="relative w-full h-[460vh] sm:h-[500vh] bg-[#070b12] text-white"
        >
            {/* Sticky 100vh Viewport: Pinned on screen while scrubbing */}
            <div className="sticky top-0 h-screen w-full overflow-hidden flex flex-col justify-between select-none">
                {/* Instant Poster Image: Displays frame-0001.jpg immediately from HTML preload without any delay */}
                <img
                    src={getFramePath(1)}
                    alt="ComLink Gaming Rig"
                    {...{ fetchpriority: "high" }}
                    decoding="async"
                    className="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none transition-opacity duration-300"
                    style={{
                        opacity: scrollPercentDisplay > 3 ? 0 : 1,
                    }}
                />

                {/* Background Canvas: Renders interactive frames 0001 to 0192 */}
                <canvas
                    ref={canvasRef}
                    className="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none"
                />

                {/* Clean Top Navbar Shadow Blend */}
                <div className="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-[#070b12]/80 via-[#070b12]/30 to-transparent pointer-events-none z-10" />

                {/* Clean Bottom Section Transition Blend */}
                <div className="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-[#070b12] to-transparent pointer-events-none z-10" />

                {/* Missing Frames Notification Toast (Only displays if user hasn't unzipped yet) */}
                {hasMissingFrames && (
                    <div className="absolute top-24 left-1/2 -translate-x-1/2 z-40 max-w-lg w-[92%] bg-black/85 border border-cyan-500/40 rounded-xl p-4 shadow-[0_0_30px_rgba(6,182,212,0.3)] backdrop-blur-md">
                        <div className="flex items-start gap-3">
                            <span className="p-2 rounded-lg bg-cyan-500/20 text-cyan-400">
                                <Cpu className="w-5 h-5 animate-pulse" />
                            </span>
                            <div className="text-xs text-slate-300">
                                <p className="font-bold text-white uppercase tracking-wider mb-1">
                                    Ready for Video Frames
                                </p>
                                <p className="leading-relaxed">
                                    Extract your 192 frames into:
                                    <code className="block mt-1 px-2 py-1 bg-white/10 rounded text-cyan-300 font-mono text-[11px] select-all">
                                        public/images/hero-frames/frame-0001.jpg
                                        ... frame-0192.jpg
                                    </code>
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Top HUD Bar */}
                {/* <div className="relative z-20 container mx-auto px-4 md:px-8 pt-20 md:pt-24 flex items-center justify-between text-xs font-mono tracking-widest text-slate-400">
                    <div className="hidden sm:flex items-center gap-2">
                        <Crosshair className="w-3.5 h-3.5 text-cyan-400 animate-spin" />
                        <span className="text-cyan-400 font-semibold">
                            SYS.01 // HARDWARE EXPLODER
                        </span>
                        <span className="text-slate-600">|</span>
                        <span className="text-slate-400">PRECISION 4K RENDER</span>
                    </div> */}

                    {/* Frame Counter HUD */}
                    {/* <div className="flex items-center gap-3 bg-black/60 backdrop-blur-md border border-cyan-500/20 rounded-full px-4 py-1.5 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                        <span className="w-2 h-2 rounded-full bg-cyan-400 animate-pulse" />
                        <span className="text-cyan-300 font-bold">
                            FRAME {String(currentFrameDisplay).padStart(4, "0")}
                        </span>
                        <span className="text-slate-600">/</span>
                        <span className="text-slate-400">
                            {String(totalFrames).padStart(4, "0")}
                        </span>
                        <span className="text-cyan-400 font-bold pl-1">
                            [{scrollPercentDisplay}%]
                        </span>
                    </div>
                </div> */}

                {/* Main Center Content: Hero Text & Buttons */}
                <div className="relative z-20 container mx-auto px-4 md:px-8 my-auto flex flex-col justify-center">
                    <div
                        style={{
                            opacity: heroContentOpacity,
                            transform: `translateY(${heroContentTransformY}px)`,
                            transition:
                                "opacity 0.15s ease-out, transform 0.15s ease-out",
                            pointerEvents:
                                heroContentOpacity < 0.2 ? "none" : "auto",
                        }}
                        className="max-w-3xl"
                    >
                        {/* 1. Label */}
                        {/* <motion.div
                            initial={{ opacity: 0, y: 15 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6 }}
                            className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-cyan-400/30 bg-cyan-950/40 backdrop-blur-md text-cyan-300 text-xs font-mono tracking-[0.25em] uppercase mb-6 shadow-[0_0_20px_rgba(34,211,238,0.2)]"
                        >
                            <span className="w-2 h-2 rounded-full bg-cyan-400 animate-ping" />
                            TOP BRANDS IN STOCK
                        </motion.div> */}

                        {/* 2. Headline: UPGRADE / YOUR PC / TODAY */}
                        <motion.h1
                            initial={{ opacity: 0, y: 25 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.1 }}
                            className="font-display font-black tracking-tight leading-[0.92] text-5xl sm:text-7xl lg:text-8xl xl:text-9xl uppercase mb-6"
                        >
                            <span className="block text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.8)]">
                                UPGRADE
                            </span>
                            <span className="block text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-sky-300 to-white drop-shadow-[0_0_40px_rgba(34,211,238,0.45)]">
                                YOUR PC
                            </span>
                            <span className="block text-white/90 drop-shadow-[0_4px_20px_rgba(0,0,0,0.8)]">
                                TODAY
                            </span>
                        </motion.h1>

                        {/* 3. Text */}
                        <motion.p
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            className="text-base sm:text-lg md:text-xl text-slate-300/90 mb-8 max-w-xl leading-relaxed drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]"
                        >
                            Premium peripherals and accessories for gamers,
                            creators, and professionals.
                        </motion.p>

                        {/* 4. Buttons */}
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.3 }}
                            className="flex flex-wrap items-center gap-4 pt-2"
                        >
                            {/* Shop Now Button */}
                            <Button
                                size="lg"
                                className="group relative overflow-hidden bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-8 h-13 shadow-[0_0_30px_rgba(6,182,212,0.4)] hover:shadow-[0_0_45px_rgba(6,182,212,0.7)] transition-all duration-300 transform hover:-translate-y-0.5 cursor-pointer rounded-none"
                                asChild
                            >
                                <Link
                                    href="/shop"
                                    className="inline-flex items-center gap-3"
                                >
                                    <span className="tracking-wider uppercase text-sm font-black">
                                        Shop Now
                                    </span>
                                    <ArrowRight className="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                                </Link>
                            </Button>

                            {/* Build Your PC Button */}
                            <Button
                                size="lg"
                                variant="outline"
                                className="group relative border border-white/30 hover:border-cyan-400/80 bg-white/5 hover:bg-cyan-500/10 backdrop-blur-md text-white hover:text-cyan-300 font-semibold px-8 h-13 shadow-[0_0_20px_rgba(0,0,0,0.5)] transition-all duration-300 transform hover:-translate-y-0.5 cursor-pointer rounded-none"
                                asChild
                            >
                                <Link
                                    href="/shop?category=pc-components"
                                    className="inline-flex items-center gap-3"
                                >
                                    <Cpu className="w-4 h-4 text-cyan-400 transition-transform duration-300 group-hover:rotate-45" />
                                    <span className="tracking-wider uppercase text-sm font-bold">
                                        Build Your PC
                                    </span>
                                </Link>
                            </Button>
                        </motion.div>
                    </div>

                    {/* ---------- Second section: appears while the frames explode ---------- */}
                    <div
                        className="absolute inset-x-4 md:inset-x-8 top-1/2 -translate-y-1/2"
                        style={{
                            pointerEvents:
                                secondContentOpacity < 0.2 ? "none" : "auto",
                        }}
                    >
                        <div
                            style={{
                                opacity: secondContentOpacity,
                                transform: `translateY(${secondContentTransformY}px)`,
                                transition:
                                    "opacity 0.15s ease-out, transform 0.15s ease-out",
                            }}
                            className="max-w-3xl"
                        >
                            {/* Label: changes with the scroll phase */}
                            {/* <span className="inline-flex items-center gap-2 mb-5 text-xs font-mono tracking-[0.25em] uppercase text-cyan-300">
                                <span className="w-2 h-2 rounded-full bg-cyan-400 animate-pulse" />
                                {hudPhase.code}
                            </span> */}

                            {/* Headline */}
                            <h2 className="font-display font-black tracking-tight leading-[0.95] text-4xl sm:text-6xl lg:text-7xl xl:text-8xl uppercase mb-6">
                                <span className="block text-white drop-shadow-[0_4px_20px_rgba(0,0,0,0.8)]">
                                    BUILT FOR
                                </span>
                                <span className="block text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-sky-300 to-white drop-shadow-[0_0_40px_rgba(34,211,238,0.45)]">
                                    PERFORMANCE
                                </span>
                            </h2>

                            {/* Text: also changes with the scroll phase */}
                            <p className="text-base sm:text-lg md:text-xl text-slate-300/90 mb-8 max-w-xl leading-relaxed drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]">
                                
                                {hudPhase.desc}
                            </p>

                            {/* Buttons */}
                            <div className="flex flex-wrap items-center gap-4 pt-2">
                                <Button
                                    size="lg"
                                    className="group relative overflow-hidden bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-8 h-13 shadow-[0_0_30px_rgba(6,182,212,0.4)] hover:shadow-[0_0_45px_rgba(6,182,212,0.7)] transition-all duration-300 transform hover:-translate-y-0.5 cursor-pointer rounded-none"
                                    asChild
                                >
                                    <Link
                                        href="/shop?category=pc-components"
                                        className="inline-flex items-center gap-3"
                                    >
                                        <span className="tracking-wider uppercase text-sm font-black">
                                            Explore Components
                                        </span>
                                        <ArrowRight className="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                                    </Link>
                                </Button>

                                <Button
                                    size="lg"
                                    variant="outline"
                                    className="group relative border border-white/30 hover:border-cyan-400/80 bg-white/5 hover:bg-cyan-500/10 backdrop-blur-md text-white hover:text-cyan-300 font-semibold px-8 h-13 shadow-[0_0_20px_rgba(0,0,0,0.5)] transition-all duration-300 transform hover:-translate-y-0.5 cursor-pointer rounded-none"
                                    asChild
                                >
                                    <Link
                                        href="/shop"
                                        className="inline-flex items-center gap-3"
                                    >
                                        <span className="tracking-wider uppercase text-sm font-bold">
                                            Shop All
                                        </span>
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* Exploded Architecture HUD Hologram (Appears dynamically as user scrolls down) */}
                    {/* <div
                        style={{
                            opacity: explodedCalloutOpacity,
                            pointerEvents:
                                explodedCalloutOpacity < 0.2 ? "none" : "auto",
                        }}
                        className="absolute right-4 md:right-12 bottom-28 md:bottom-24 max-w-sm text-right transition-opacity duration-300"
                    >
                        <div className="inline-block p-4 bg-black/60 border-r-2 border-cyan-400 border-t border-b border-l border-white/10 backdrop-blur-md shadow-[0_0_30px_rgba(0,0,0,0.7)]">
                            <span className="text-[10px] font-mono tracking-widest text-cyan-400 block uppercase">
                                {hudPhase.code}
                            </span>
                            <h3 className="font-display text-lg md:text-xl font-bold text-white mt-1 uppercase">
                                {hudPhase.title}
                            </h3>
                            <p className="text-xs text-slate-300 mt-1 leading-relaxed">
                                {hudPhase.desc}
                            </p>
                        </div>
                    </div> */}
                </div>

                {/* Bottom Interactive HUD & Scroll Prompt */}
                <div className="relative z-20 container mx-auto px-4 md:px-8 pb-8 flex items-end justify-between">
                    {/* Left corner: Tech Specifications Tag */}
                    {/* <div className="hidden sm:flex flex-col gap-1 text-[11px] font-mono text-slate-400">
                        <span className="text-cyan-400/80">
                            [01 // EXPLODED INTERACTIVE VIEW]
                        </span>
                        <span className="text-slate-500">
                            SCROLL DOWN TO DECONSTRUCT • SCROLL UP TO ASSEMBLE
                        </span>
                    </div> */}

                    {/* Center: Scroll Down Indicator (Fades out once user begins scrolling) */}
                    <div
                        style={{
                            opacity: Math.max(
                                0,
                                1 - (scrollPercentDisplay / 15) * 1.5,
                            ),
                            pointerEvents: "none",
                        }}
                        className="absolute left-1/2 -translate-x-1/2 bottom-8 flex flex-col items-center gap-2 transition-opacity duration-300"
                    >
                        <span className="text-[10px] font-mono tracking-[0.25em] uppercase text-cyan-300/80">
                            Scroll to Explode
                        </span>
                        <div className="w-5 h-9 rounded-full border border-cyan-400/40 flex items-start justify-center p-1 bg-black/40 backdrop-blur-sm">
                            <motion.div
                                animate={{
                                    y: [0, 14, 0],
                                    opacity: [1, 0.2, 1],
                                }}
                                transition={{
                                    duration: 1.8,
                                    repeat: Infinity,
                                    ease: "easeInOut",
                                }}
                                className="w-1.5 h-1.5 rounded-full bg-cyan-400"
                            />
                        </div>
                    </div>

                    {/* Right corner: Frame Scrubber Track */}
                    {/* <div className="flex flex-col items-end gap-1.5 w-44 md:w-56">
                        <div className="w-full flex justify-between text-[10px] font-mono text-slate-400">
                            <span>STABLE</span>
                            <span className="text-cyan-400">
                                {scrollPercentDisplay}%
                            </span>
                            <span>EXPLODED</span>
                        </div> */}
                        {/* Interactive Timeline Progress Bar */}
                        {/* <div className="w-full h-1.5 bg-white/10 rounded-full overflow-hidden border border-white/5">
                            <div
                                style={{ width: `${scrollPercentDisplay}%` }}
                                className="h-full bg-gradient-to-r from-cyan-500 via-sky-400 to-cyan-300 shadow-[0_0_10px_rgba(34,211,238,0.8)] transition-all duration-75"
                            />
                        </div>
                    </div> */}
                </div>
            </div>
        </section>
    );
}