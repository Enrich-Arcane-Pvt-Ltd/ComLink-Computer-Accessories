import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head, Link } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronDown, ChevronUp, MessageSquare, Star } from "lucide-react";
import React, { useState } from "react";

interface TestimonialItem {
    id: string;
    name: string;
    location: string;
    avatar: string;
    rating: number;
    quote: string;
}

interface TestimonialsProps {
    testimonials: TestimonialItem[];
    title?: string;
}

/* -------------------------------------------------------------------------- */
/*  Vector Speech Bubbles & Quotation Marks (Reference Image Aesthetic)        */
/* -------------------------------------------------------------------------- */

type BubbleType =
    | "bubble-solid"
    | "bubble-outline"
    | "rect-bubble"
    | "thought-cloud"
    | "quotes-pair"
    | "quote-badge";

interface FloatingBubbleProps {
    type: BubbleType;
    size: number;
    top?: string;
    bottom?: string;
    left?: string;
    right?: string;
    rotate?: number;
    opacity?: number;
    blur?: string;
    duration?: number;
    delay?: number;
    driftY?: number;
}

function FloatingBubble({
    type,
    size,
    top,
    bottom,
    left,
    right,
    rotate = 0,
    opacity = 0.25,
    blur = "blur-none",
    duration = 7,
    delay = 0,
    driftY = 12,
}: FloatingBubbleProps) {
    return (
        <motion.div
            initial={{ y: 0, rotate }}
            animate={{
                y: [-driftY, driftY, -driftY],
                rotate: [rotate - 2.5, rotate + 2.5, rotate - 2.5],
            }}
            transition={{
                duration,
                repeat: Infinity,
                repeatType: "mirror",
                ease: "easeInOut",
                delay,
            }}
            className={cn(
                "pointer-events-none select-none absolute z-0 text-cyan-600/30 dark:text-cyan-400/25",
                blur,
            )}
            style={{
                width: size,
                height: size,
                top,
                bottom,
                left,
                right,
                opacity,
            }}
            aria-hidden="true"
        >
            {type === "bubble-solid" && (
                <svg
                    viewBox="0 0 100 80"
                    fill="currentColor"
                    className="w-full h-full drop-shadow-sm"
                >
                    <path d="M 10 32 C 10 14 26 4 50 4 C 74 4 90 14 90 32 C 90 50 74 60 50 60 C 40 60 32 62 18 70 C 21 63 20 60 16 57 C 12 50 10 42 10 32 Z" />
                </svg>
            )}

            {type === "bubble-outline" && (
                <svg
                    viewBox="0 0 100 80"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="3.2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    className="w-full h-full drop-shadow-sm"
                >
                    <path d="M 12 32 C 12 16 27 6 50 6 C 73 6 88 16 88 32 C 88 48 73 58 50 58 C 40 58 32 60 18 68 C 21 61 20 58 17 55 C 13 49 12 41 12 32 Z" />
                </svg>
            )}

            {type === "rect-bubble" && (
                <svg
                    viewBox="0 0 100 70"
                    fill="currentColor"
                    className="w-full h-full drop-shadow-sm"
                >
                    <path d="M 6 6 H 94 A 6 6 0 0 1 100 12 V 46 A 6 6 0 0 1 94 52 H 35 L 20 66 V 52 H 6 A 6 6 0 0 1 0 46 V 12 A 6 6 0 0 1 6 6 Z" />
                </svg>
            )}

            {type === "thought-cloud" && (
                <svg
                    viewBox="0 0 100 95"
                    fill="currentColor"
                    className="w-full h-full drop-shadow-sm"
                >
                    <path d="M 28 42 A 15 15 0 0 1 40 22 A 19 19 0 0 1 70 20 A 17 17 0 0 1 85 36 A 15 15 0 0 1 86 52 A 16 16 0 0 1 70 64 A 15 15 0 0 1 38 64 A 16 16 0 0 1 28 42 Z" />
                    <circle cx="24" cy="72" r="5" />
                    <circle cx="17" cy="80" r="3.2" />
                    <circle cx="12" cy="86" r="2" />
                </svg>
            )}

            {type === "quotes-pair" && (
                <svg
                    viewBox="0 0 70 50"
                    fill="currentColor"
                    className="w-full h-full drop-shadow-sm"
                >
                    <path d="M 12 30 C 12 18 19 10 30 8 L 32 14 C 24 16 21 21 21 26 H 32 V 44 H 12 Z M 44 30 C 44 18 51 10 62 8 L 64 14 C 56 16 53 21 53 26 H 64 V 44 H 44 Z" />
                </svg>
            )}

            {type === "quote-badge" && (
                <svg
                    viewBox="0 0 70 70"
                    fill="none"
                    className="w-full h-full drop-shadow-sm"
                >
                    <circle cx="35" cy="35" r="32" fill="currentColor" fillOpacity="0.85" />
                    <path
                        d="M 22 35 C 22 28 26 23 31 21 L 32 25 C 28 27 27 29 27 32 H 32 V 42 H 22 Z M 38 35 C 38 28 42 23 47 21 L 48 25 C 44 27 43 29 43 32 H 48 V 42 H 38 Z"
                        fill="white"
                        fillOpacity="0.9"
                    />
                </svg>
            )}
        </motion.div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Testimonials Page Component                                          */
/* -------------------------------------------------------------------------- */

export default function Testimonials({
    testimonials = [],
    title = "Customer Reviews",
}: TestimonialsProps) {
    // Items to load per page
    const ITEMS_PER_PAGE = 9;
    const [visibleCount, setVisibleCount] = useState(ITEMS_PER_PAGE);

    const visibleTestimonials = testimonials.slice(0, visibleCount);

    const averageRating =
        testimonials.length > 0
            ? testimonials.reduce((acc, curr) => acc + curr.rating, 0) /
              testimonials.length
            : 0;

    const handleSeeMore = () => {
        setVisibleCount((prevCount) => prevCount + ITEMS_PER_PAGE);
    };

    const handleSeeLess = () => {
        setVisibleCount(ITEMS_PER_PAGE);
        window.scrollTo({ top: 250, behavior: "smooth" });
    };

    return (
        <AppLayout>
            <Head title={title} />

            <div
                className="relative overflow-hidden min-h-screen bg-gradient-to-b from-[#eaf4ff] via-[#f2f8ff] to-[#edf5ff] dark:from-[#06101e] dark:via-[#09182d] dark:to-[#050c18] text-slate-800 dark:text-slate-200 transition-colors duration-300 py-16"
                style={{ contain: "paint" }}
            >
                {/* ========================================================================= */}
                {/* 1. CREATIVE 3D SPEECH BUBBLES & QUOTES BACKGROUND (Reference Image Style)  */}
                {/* ========================================================================= */}
                <div
                    className="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
                    style={{ contain: "paint" }}
                >
                    {/* Ambient Glow Atmosphere */}
                    <div className="absolute left-1/2 top-1/4 -translate-x-1/2 -translate-y-1/2 w-[900px] max-w-full h-[550px] bg-gradient-to-tr from-cyan-400/20 via-sky-400/20 to-blue-500/15 dark:from-cyan-500/12 dark:via-blue-600/12 dark:to-cyan-400/8 blur-[140px] rounded-full" />
                    <div className="absolute -top-20 -left-20 w-[450px] h-[450px] bg-cyan-400/20 dark:bg-cyan-500/10 blur-[130px] rounded-full" />
                    <div className="absolute -bottom-24 -right-24 w-[480px] h-[480px] bg-blue-500/20 dark:bg-blue-600/10 blur-[130px] rounded-full" />

                    {/* --- FOREGROUND & CORNERS (Large crisp elements) --- */}
                    <FloatingBubble
                        type="thought-cloud"
                        size={155}
                        top="3%"
                        left="4%"
                        rotate={-8}
                        opacity={0.45}
                        duration={7.5}
                        delay={0}
                        driftY={14}
                    />
                    <FloatingBubble
                        type="rect-bubble"
                        size={140}
                        top="4%"
                        right="5%"
                        rotate={10}
                        opacity={0.42}
                        duration={8.2}
                        delay={0.6}
                        driftY={12}
                    />
                    <FloatingBubble
                        type="bubble-outline"
                        size={160}
                        bottom="6%"
                        left="3%"
                        rotate={-12}
                        opacity={0.38}
                        duration={8.8}
                        delay={1.2}
                        driftY={15}
                    />
                    <FloatingBubble
                        type="bubble-solid"
                        size={150}
                        bottom="5%"
                        right="4%"
                        rotate={14}
                        opacity={0.4}
                        duration={7.8}
                        delay={0.4}
                        driftY={16}
                    />

                    {/* --- MIDGROUND (Medium speech bubbles & quote badges) --- */}
                    <FloatingBubble
                        type="quote-badge"
                        size={75}
                        top="24%"
                        left="8%"
                        rotate={-15}
                        opacity={0.5}
                        duration={6.5}
                        delay={1.5}
                        driftY={10}
                    />
                    <FloatingBubble
                        type="quotes-pair"
                        size={80}
                        top="18%"
                        right="10%"
                        rotate={12}
                        opacity={0.45}
                        duration={7.0}
                        delay={2.0}
                        driftY={11}
                    />
                    <FloatingBubble
                        type="bubble-outline"
                        size={110}
                        top="45%"
                        left="5%"
                        rotate={16}
                        opacity={0.35}
                        duration={8.5}
                        delay={0.9}
                        driftY={13}
                    />
                    <FloatingBubble
                        type="rect-bubble"
                        size={120}
                        top="48%"
                        right="6%"
                        rotate={-8}
                        opacity={0.38}
                        duration={9.0}
                        delay={1.8}
                        driftY={12}
                    />
                    <FloatingBubble
                        type="quote-badge"
                        size={85}
                        bottom="28%"
                        right="8%"
                        rotate={18}
                        opacity={0.45}
                        duration={7.4}
                        delay={2.4}
                        driftY={10}
                    />
                    <FloatingBubble
                        type="quotes-pair"
                        size={70}
                        bottom="30%"
                        left="9%"
                        rotate={-20}
                        opacity={0.42}
                        duration={6.8}
                        delay={1.1}
                        driftY={9}
                    />

                    {/* --- BACKGROUND (Soft blurred depth-of-field elements) --- */}
                    <FloatingBubble
                        type="bubble-solid"
                        size={70}
                        top="14%"
                        left="24%"
                        rotate={15}
                        opacity={0.25}
                        blur="blur-[2px]"
                        duration={6.2}
                        delay={0.3}
                        driftY={8}
                    />
                    <FloatingBubble
                        type="thought-cloud"
                        size={85}
                        top="12%"
                        right="26%"
                        rotate={-10}
                        opacity={0.25}
                        blur="blur-[2px]"
                        duration={7.2}
                        delay={1.7}
                        driftY={8}
                    />
                    <FloatingBubble
                        type="rect-bubble"
                        size={65}
                        bottom="18%"
                        left="22%"
                        rotate={-14}
                        opacity={0.22}
                        blur="blur-[2.5px]"
                        duration={8.0}
                        delay={2.3}
                        driftY={7}
                    />
                    <FloatingBubble
                        type="bubble-outline"
                        size={80}
                        bottom="20%"
                        right="24%"
                        rotate={12}
                        opacity={0.22}
                        blur="blur-[2.5px]"
                        duration={7.6}
                        delay={0.7}
                        driftY={7}
                    />
                    <FloatingBubble
                        type="quotes-pair"
                        size={45}
                        top="32%"
                        left="18%"
                        rotate={-25}
                        opacity={0.28}
                        blur="blur-[1.5px]"
                        duration={6.0}
                        delay={1.4}
                        driftY={6}
                    />
                    <FloatingBubble
                        type="quotes-pair"
                        size={50}
                        top="36%"
                        right="19%"
                        rotate={22}
                        opacity={0.28}
                        blur="blur-[1.5px]"
                        duration={6.4}
                        delay={2.2}
                        driftY={6}
                    />
                </div>

                {/* ========================================================================= */}
                {/* 2. TESTIMONIALS CONTENT                                                   */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6">
                    {/* Header Section */}
                    <div className="text-center mb-16 max-w-2xl mx-auto">
                        <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                            <MessageSquare className="w-3.5 h-3.5" />
                            Verified Customer Feedback
                        </span>
                        <h1 className="font-display text-4xl sm:text-5xl md:text-6xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                            {title}
                        </h1>

                        <div className="flex items-center justify-center gap-3 mb-3">
                            <div className="flex text-amber-400">
                                {[...Array(5)].map((_, i) => (
                                    <Star
                                        key={i}
                                        className={`h-6 w-6 ${
                                            i < Math.round(averageRating)
                                                ? "fill-current"
                                                : "text-slate-300 dark:text-slate-700"
                                        }`}
                                    />
                                ))}
                            </div>
                            <span className="text-2xl font-bold text-slate-900 dark:text-white">
                                {averageRating.toFixed(1)} / 5.0
                            </span>
                        </div>

                        <p className="text-slate-600 dark:text-slate-300 text-sm sm:text-base font-medium leading-relaxed">
                            Based on {testimonials.length} authentic customer
                            reviews from gamers, creators, and professionals.
                        </p>
                    </div>

                    {/* Testimonials Grid with Framer Motion Animation */}
                    {visibleTestimonials.length > 0 ? (
                        <motion.div
                            layout
                            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
                        >
                            <AnimatePresence mode="popLayout">
                                {visibleTestimonials.map((testimonial) => (
                                    <motion.div
                                        layout
                                        initial={{ opacity: 0, scale: 0.95 }}
                                        animate={{ opacity: 1, scale: 1 }}
                                        exit={{ opacity: 0, scale: 0.95 }}
                                        transition={{ duration: 0.3 }}
                                        key={testimonial.id}
                                        className="rounded-2xl border border-slate-200/90 dark:border-white/10 bg-white/92 dark:bg-[#0c1322]/88 backdrop-blur-md p-8 flex flex-col h-full shadow-lg shadow-cyan-950/5 dark:shadow-black/25 hover:border-cyan-500/60 hover:shadow-cyan-500/10 transition-all duration-300 hover:-translate-y-1"
                                    >
                                        <div className="flex text-amber-400 mb-4">
                                            {[...Array(5)].map((_, i) => (
                                                <Star
                                                    key={i}
                                                    className={`h-4 w-4 ${
                                                        i < testimonial.rating
                                                            ? "fill-current"
                                                            : "text-slate-200 dark:text-slate-700"
                                                    }`}
                                                />
                                            ))}
                                        </div>

                                        <p className="text-slate-800 dark:text-slate-200 italic mb-6 flex-grow leading-relaxed text-sm sm:text-base font-normal">
                                            "{testimonial.quote}"
                                        </p>

                                        <div className="flex items-center gap-4 mt-auto pt-5 border-t border-slate-100 dark:border-white/5">
                                            <img
                                                src={testimonial.avatar}
                                                alt={testimonial.name}
                                                className="w-12 h-12 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                                                onError={(e) => {
                                                    (
                                                        e.target as HTMLElement
                                                    ).style.display = "none";
                                                }}
                                            />
                                            <div className="min-w-0">
                                                <h4 className="font-bold text-slate-900 dark:text-white text-sm truncate">
                                                    {testimonial.name}
                                                </h4>
                                                <p className="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                    {testimonial.location}
                                                </p>
                                            </div>
                                        </div>
                                    </motion.div>
                                ))}
                            </AnimatePresence>
                        </motion.div>
                    ) : (
                        <div className="col-span-full flex flex-col items-center justify-center text-center py-20 px-4">
                            <motion.div
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.5 }}
                                className="flex flex-col items-center max-w-md"
                            >
                                <div className="flex items-center space-x-1 mb-6 bg-cyan-500/10 px-4 py-2 rounded-full border border-cyan-500/20">
                                    {[...Array(5)].map((_, i) => (
                                        <Star
                                            key={i}
                                            className="h-5 w-5 fill-cyan-500 text-cyan-500 animate-pulse"
                                            style={{
                                                animationDelay: `${i * 150}ms`,
                                            }}
                                        />
                                    ))}
                                </div>

                                <h3 className="font-display text-2xl font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">
                                    No Reviews Yet
                                </h3>

                                <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-8">
                                    We haven't received any testimonials at the
                                    moment. Our customers' verified feedback and
                                    shopping experiences will appear here
                                    shortly!
                                </p>

                                <Link
                                    href="/shop"
                                    className="inline-flex items-center justify-center h-12 px-8 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-display tracking-widest text-xs font-bold uppercase transition-all duration-200 rounded-xl shadow-lg shadow-cyan-500/20"
                                >
                                    Be the first to review
                                </Link>
                            </motion.div>
                        </div>
                    )}

                    {/* Pagination Actions (See More / See Less) */}
                    <div className="mt-16 text-center flex justify-center">
                        {visibleCount < testimonials.length ? (
                            /* --- See More Button --- */
                            <button
                                onClick={handleSeeMore}
                                className="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl border border-slate-300 dark:border-white/10 bg-white/90 dark:bg-card/80 text-slate-800 dark:text-white font-display text-sm font-semibold uppercase tracking-wider hover:bg-cyan-500 hover:text-slate-950 hover:border-cyan-500 transition-all duration-300 shadow-sm cursor-pointer"
                            >
                                See More Reviews
                                <ChevronDown className="h-4 w-4" />
                            </button>
                        ) : (
                            /* --- See Less Button --- */
                            testimonials.length > ITEMS_PER_PAGE && (
                                <button
                                    onClick={handleSeeLess}
                                    className="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl border border-cyan-500 bg-cyan-500 text-slate-950 font-display text-sm font-bold uppercase tracking-wider hover:bg-cyan-400 transition-all duration-300 shadow-sm cursor-pointer"
                                >
                                    See Less
                                    <ChevronUp className="h-4 w-4" />
                                </button>
                            )
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
