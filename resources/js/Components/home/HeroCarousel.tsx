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

export function HeroCarousel({
    slides = [],
    totalFrames = 192,
    framesFolder = "/images/hero-frames",
}: HeroCarouselProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);

    // Frame cache & loading status
    const imagesCache = useRef<Map<number, HTMLImageElement>>(new Map());
    const [loadedCount, setLoadedCount] = useState<number>(0);
    const [hasMissingFrames, setHasMissingFrames] = useState<boolean>(false);
    const [currentFrameDisplay, setCurrentFrameDisplay] = useState<number>(1);
    const [scrollPercentDisplay, setScrollPercentDisplay] =
        useState<number>(0);

    // Animation loop state
    const currentFrameFloat = useRef<number>(1);
    const targetFrame = useRef<number>(1);
    const animationFrameId = useRef<number | null>(null);

    // Helper: format frame number e.g. 1 -> "0001"
    const getFramePath = (index: number) => {
        const padded = String(index).padStart(4, "0");
        return `${framesFolder}/frame-${padded}.jpg`;
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
        } else {
            // High-tech procedural placeholder while frames load or if zip isn't extracted yet
            renderTechPlaceholder(ctx, width, height);
        }
    };

    // Procedural sci-fi placeholder fallback
    const renderTechPlaceholder = (
        ctx: CanvasRenderingContext2D,
        w: number,
        h: number,
    ) => {
        // Deep cyber backdrop
        const grad = ctx.createRadialGradient(
            w / 2,
            h / 2,
            50,
            w / 2,
            h / 2,
            Math.max(w, h) * 0.7,
        );
        grad.addColorStop(0, "#132338");
        grad.addColorStop(0.5, "#0b121c");
        grad.addColorStop(1, "#05080e");
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);

        // Grid lines
        ctx.strokeStyle = "rgba(34, 211, 238, 0.08)";
        ctx.lineWidth = 1;
        const gridSize = 60;
        for (let x = 0; x < w; x += gridSize) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let y = 0; y < h; y += gridSize) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }

        // Concentric circles representing PC internal core / fan assembly
        const cx = w / 2;
        const cy = h / 2;
        const p = (targetFrame.current - 1) / (totalFrames - 1);

        ctx.strokeStyle = `rgba(34, 211, 238, ${0.15 + p * 0.2})`;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(cx, cy, 140 + p * 80, 0, Math.PI * 2);
        ctx.stroke();

        ctx.strokeStyle = `rgba(14, 165, 233, ${0.2 + p * 0.3})`;
        ctx.setLineDash([12, 12]);
        ctx.beginPath();
        ctx.arc(cx, cy, 90 + p * 50, 0, Math.PI * 2);
        ctx.stroke();
        ctx.setLineDash([]);

        // Core icon representation
        ctx.fillStyle = "rgba(34, 211, 238, 0.35)";
        ctx.beginPath();
        ctx.arc(cx, cy, 30, 0, Math.PI * 2);
        ctx.fill();
    };

    // Preload frames progressively
    useEffect(() => {
        let isMounted = true;
        const cache = imagesCache.current;
        let loaded = 0;

        // 1. Immediately load frame 1 for instant display
        const firstImg = new Image();
        firstImg.src = getFramePath(1);
        firstImg.onload = () => {
            if (!isMounted) return;
            cache.set(1, firstImg);
            loaded++;
            setLoadedCount(loaded);
            renderFrame(firstImg);
        };
        firstImg.onerror = () => {
            if (!isMounted) return;
            // Frames not found in public folder yet
            setHasMissingFrames(true);
            renderFrame(null);
        };

        // 2. Preload strategic keyframes first (every 8 frames) for rapid scrub response
        const keyframeIndices: number[] = [];
        for (let i = 2; i <= totalFrames; i += 8) {
            keyframeIndices.push(i);
        }

        // 3. Followed by remaining intermediate frames
        const remainingIndices: number[] = [];
        for (let i = 2; i <= totalFrames; i++) {
            if (!keyframeIndices.includes(i)) {
                remainingIndices.push(i);
            }
        }

        const loadOrder = [...keyframeIndices, ...remainingIndices];
        let indexInQueue = 0;
        const maxConcurrent = 6;
        let activeLoads = 0;

        const loadNext = () => {
            if (!isMounted) return;
            while (activeLoads < maxConcurrent && indexInQueue < loadOrder.length) {
                const frameIndex = loadOrder[indexInQueue++];
                activeLoads++;

                const img = new Image();
                img.src = getFramePath(frameIndex);
                img.onload = () => {
                    if (!isMounted) return;
                    cache.set(frameIndex, img);
                    loaded++;
                    setLoadedCount(loaded);
                    activeLoads--;
                    loadNext();
                };
                img.onerror = () => {
                    if (!isMounted) return;
                    activeLoads--;
                    loadNext();
                };
            }
        };

        loadNext();

        return () => {
            isMounted = false;
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
            const img = imagesCache.current.get(activeIndex) || null;
            renderFrame(img);
        };

        updateCanvasSize();
        window.addEventListener("resize", updateCanvasSize);
        return () => window.removeEventListener("resize", updateCanvasSize);
    }, []);

    // Smooth physics-based render loop using linear interpolation (LERP)
    useEffect(() => {
        let isRunning = true;

        const loop = () => {
            if (!isRunning) return;

            // LERP for smooth cinematic frame scrubbing
            const diff = targetFrame.current - currentFrameFloat.current;
            if (Math.abs(diff) > 0.01) {
                currentFrameFloat.current += diff * 0.18;
            } else {
                currentFrameFloat.current = targetFrame.current;
            }

            const frameToDraw = Math.min(
                totalFrames,
                Math.max(1, Math.round(currentFrameFloat.current)),
            );

            // Display updates
            setCurrentFrameDisplay(frameToDraw);
            const pct = Math.round(
                ((frameToDraw - 1) / (totalFrames - 1)) * 100,
            );
            setScrollPercentDisplay(pct);

            // Find closest available cached frame if current one is still downloading
            let imgToDraw = imagesCache.current.get(frameToDraw);
            if (!imgToDraw) {
                // Look for closest loaded frame
                for (let offset = 1; offset < 20; offset++) {
                    const before = imagesCache.current.get(frameToDraw - offset);
                    if (before) {
                        imgToDraw = before;
                        break;
                    }
                    const after = imagesCache.current.get(frameToDraw + offset);
                    if (after) {
                        imgToDraw = after;
                        break;
                    }
                }
            }

            renderFrame(imgToDraw || null);

            animationFrameId.current = requestAnimationFrame(loop);
        };

        animationFrameId.current = requestAnimationFrame(loop);

        return () => {
            isRunning = false;
            if (animationFrameId.current) {
                cancelAnimationFrame(animationFrameId.current);
            }
        };
    }, [totalFrames]);

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
        };

        window.addEventListener("scroll", handleScroll, { passive: true });
        handleScroll(); // initialize

        return () => window.removeEventListener("scroll", handleScroll);
    }, [totalFrames]);

    // Dynamic HUD Phase description based on scroll percent
    const hudPhase = useMemo(() => {
        if (scrollPercentDisplay < 15) {
            return {
                code: "STATUS: STABLE",
                title: "COM-LINK CUSTOM RIG",
                desc: "Fully assembled high-performance system",
            };
        } else if (scrollPercentDisplay < 50) {
            return {
                code: "PHASE 01 // DECOUPLING",
                title: "CHASSIS & AIRFLOW MATRIX",
                desc: "Tempered panoramic glass & high-static pressure fans",
            };
        } else if (scrollPercentDisplay < 80) {
            return {
                code: "PHASE 02 // THERMAL CORE",
                title: "LIQUID COOLING & GPU ARRAY",
                desc: "Precision copper block & triple-fan cooling dissipation",
            };
        } else {
            return {
                code: "PHASE 03 // FULL EXPLODED VIEW",
                title: "MODULAR COMPONENT ARCHITECTURE",
                desc: "100% precision engineered hardware layer by layer",
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

    return (
        <section
            ref={containerRef}
            className="relative w-full h-[280vh] sm:h-[320vh] bg-[#070b12] text-white"
        >
            {/* Sticky 100vh Viewport: Pinned on screen while scrubbing */}
            <div className="sticky top-0 h-screen w-full overflow-hidden flex flex-col justify-between select-none">
                {/* Background Canvas: Renders frame 0001 to 0192 */}
                <canvas
                    ref={canvasRef}
                    className="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none"
                />

                {/* Cyber Cinematic Atmospheric Gradients */}
                {/* Top Navbar Blend */}
                <div className="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-[#070b12]/90 via-[#070b12]/50 to-transparent pointer-events-none z-10" />

                {/* Bottom Section Blend */}
                <div className="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-[#070b12] via-[#070b12]/70 to-transparent pointer-events-none z-10" />

                {/* Radial Cyber Vignette for high focus & contrast */}
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(7,11,18,0.75)_100%)] pointer-events-none z-10" />

                {/* Subtle Cyber Grid Lines Overlay */}
                <div className="absolute inset-0 bg-[linear-gradient(to_right,rgba(34,211,238,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(34,211,238,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none z-10" />

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
