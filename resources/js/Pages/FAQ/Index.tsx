import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronDown, HelpCircle, Search, X } from "lucide-react";
import React, { useState } from "react";

interface FAQItem {
    id: string;
    question: string;
    answer: string;
    category: any;
}

interface FAQsProps {
    faqs: FAQItem[];
    title?: string;
}

/* -------------------------------------------------------------------------- */
/*  Floating 3D Question Mark Component (Inspired by Image 2 Reference)       */
/* -------------------------------------------------------------------------- */
interface FloatingQuestionMarkProps {
    className?: string;
    size: number;
    rotate?: number;
    blur?: string;
    opacity?: number;
    delay?: number;
    duration?: number;
    driftY?: number;
    top?: string;
    left?: string;
    right?: string;
    bottom?: string;
    gradientId: string;
}

function FloatingQuestionMark({
    className,
    size,
    rotate = 0,
    blur = "blur-none",
    opacity = 0.5,
    delay = 0,
    duration = 6,
    driftY = 12,
    top,
    left,
    right,
    bottom,
    gradientId,
}: FloatingQuestionMarkProps) {
    return (
        <motion.div
            initial={{ y: 0, rotate: rotate }}
            animate={{
                y: [-driftY, driftY, -driftY],
                rotate: [rotate - 3, rotate + 3, rotate - 3],
            }}
            transition={{
                duration,
                repeat: Infinity,
                repeatType: "mirror",
                ease: "easeInOut",
                delay,
            }}
            className={cn(
                "pointer-events-none select-none absolute z-0 transition-opacity",
                blur,
                className,
            )}
            style={{
                width: size,
                height: size * 1.25,
                opacity,
                top,
                left,
                right,
                bottom,
            }}
            aria-hidden="true"
        >
            <svg
                viewBox="0 0 100 130"
                className="w-full h-full drop-shadow-[0_12px_24px_rgba(2,132,199,0.3)] dark:drop-shadow-[0_12px_28px_rgba(6,182,212,0.38)]"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <defs>
                    <linearGradient
                        id={gradientId}
                        x1="0%"
                        y1="0%"
                        x2="100%"
                        y2="100%"
                    >
                        <stop offset="0%" stopColor="#bae6fd" />
                        <stop offset="40%" stopColor="#38bdf8" />
                        <stop offset="100%" stopColor="#0284c7" />
                    </linearGradient>
                </defs>
                <text
                    x="50"
                    y="105"
                    textAnchor="middle"
                    fontSize="120"
                    fontFamily="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif"
                    fontWeight="900"
                    fill={`url(#${gradientId})`}
                >
                    ?
                </text>
            </svg>
        </motion.div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main FAQs Page Component                                                  */
/* -------------------------------------------------------------------------- */

export default function FAQs({ faqs = [], title = "FAQs" }: FAQsProps) {
    const [searchQuery, setSearchQuery] = useState("");
    const [openId, setOpenId] = useState<string | null>("1");

    const categories = Array.from(new Set(faqs.map((faq) => faq.category)));

    const filteredFaqs = faqs.filter(
        (faq) =>
            faq.question.toLowerCase().includes(searchQuery.toLowerCase()) ||
            faq.answer.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    return (
        <AppLayout>
            <Head title={title} />

            <div
                className="relative overflow-hidden min-h-screen bg-gradient-to-b from-[#eaf4ff] via-[#f3f8ff] to-[#edf5ff] dark:from-[#070b12] dark:via-[#0c1322] dark:to-[#070b12] text-slate-800 dark:text-slate-200 transition-colors duration-300"
                style={{ contain: "paint" }}
            >
                {/* ========================================================================= */}
                {/* 1. CREATIVE 3D FLOATING QUESTION MARKS BACKGROUND (Reference Image 2)      */}
                {/* ========================================================================= */}
                <div
                    className="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
                    style={{ contain: "paint" }}
                >
                    {/* Ambient Glow Orbs */}
                    <div className="absolute left-1/2 top-1/4 -translate-x-1/2 -translate-y-1/2 w-[800px] max-w-full h-[500px] bg-gradient-to-tr from-cyan-400/20 via-sky-300/25 to-blue-400/15 dark:from-cyan-500/12 dark:via-blue-600/10 dark:to-cyan-400/10 blur-[130px] rounded-full" />
                    <div className="absolute -top-16 -left-16 w-[400px] h-[400px] bg-cyan-400/20 dark:bg-cyan-500/10 blur-[120px] rounded-full" />
                    <div className="absolute -bottom-20 -right-20 w-[450px] h-[450px] bg-blue-500/20 dark:bg-blue-600/10 blur-[130px] rounded-full" />

                    {/* FOREGROUND: Large Crisp Question Marks */}
                    <FloatingQuestionMark
                        gradientId="qg-top-left-large"
                        size={145}
                        top="4%"
                        left="3%"
                        rotate={-14}
                        delay={0}
                        duration={6.5}
                        driftY={14}
                        opacity={0.65}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-bottom-right-xlarge"
                        size={175}
                        bottom="8%"
                        right="3%"
                        rotate={12}
                        delay={0.4}
                        duration={7}
                        driftY={16}
                        opacity={0.7}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-bottom-left-large"
                        size={135}
                        bottom="14%"
                        left="4%"
                        rotate={-10}
                        delay={1.2}
                        duration={8}
                        driftY={15}
                        opacity={0.6}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-top-right-med"
                        size={110}
                        top="7%"
                        right="4%"
                        rotate={16}
                        delay={0.8}
                        duration={7.2}
                        driftY={12}
                        opacity={0.65}
                    />

                    {/* MIDGROUND: Medium Question Marks */}
                    <FloatingQuestionMark
                        gradientId="qg-mid-left"
                        size={80}
                        top="42%"
                        left="6%"
                        rotate={8}
                        delay={1.6}
                        duration={7.5}
                        driftY={10}
                        blur="blur-[0.5px]"
                        opacity={0.55}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-mid-right"
                        size={85}
                        top="48%"
                        right="5%"
                        rotate={-15}
                        delay={2.1}
                        duration={8.2}
                        driftY={11}
                        blur="blur-[0.5px]"
                        opacity={0.55}
                    />

                    {/* BACKGROUND: Soft Depth-of-Field Blurred Question Marks */}
                    <FloatingQuestionMark
                        gradientId="qg-far-top-center-left"
                        size={50}
                        top="16%"
                        left="22%"
                        rotate={22}
                        delay={0.5}
                        duration={6.2}
                        driftY={8}
                        blur="blur-[2px]"
                        opacity={0.38}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-far-top-center-right"
                        size={52}
                        top="15%"
                        right="20%"
                        rotate={-12}
                        delay={1.8}
                        duration={6.6}
                        driftY={8}
                        blur="blur-[2px]"
                        opacity={0.38}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-far-bottom-center-left"
                        size={44}
                        bottom="26%"
                        left="18%"
                        rotate={-18}
                        delay={2.4}
                        duration={7.2}
                        driftY={7}
                        blur="blur-[2.5px]"
                        opacity={0.35}
                    />
                    <FloatingQuestionMark
                        gradientId="qg-far-bottom-center-right"
                        size={48}
                        bottom="30%"
                        right="22%"
                        rotate={15}
                        delay={1.0}
                        duration={7.8}
                        driftY={7}
                        blur="blur-[2.5px]"
                        opacity={0.35}
                    />
                </div>

                {/* ========================================================================= */}
                {/* 2. PAGE HEADER & WIDE SEARCH INPUT BOX                                    */}
                {/* ========================================================================= */}
                <div className="relative z-10 pt-16 pb-12 text-center">
                    <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-2">
                        <HelpCircle className="w-3.5 h-3.5" />
                        Help & Support Center
                    </span>
                    <h1 className="font-display text-4xl sm:text-5xl md:text-6xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                        {title}
                    </h1>
                    <p className="text-slate-600 dark:text-slate-300 text-sm sm:text-base max-w-2xl mx-auto px-4 mb-8 leading-relaxed">
                        Find quick answers to common questions about our products,
                        orders, island-wide delivery, warranty, and returns.
                    </p>

                    {/* ENLARGED SEARCH BOX (Increased width from max-w-md to max-w-2xl) */}
                    <div className="max-w-2xl mx-auto px-4 relative">
                        <input
                            type="text"
                            placeholder="Search FAQs by question or keyword..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full h-15 pl-13 pr-11 rounded-2xl border border-slate-300/80 dark:border-white/10 bg-white/95 dark:bg-[#0d1626]/90 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 shadow-lg shadow-cyan-500/5 backdrop-blur-md text-base transition-all duration-200"
                        />
                        <Search className="absolute left-8 top-1/2 -translate-y-1/2 h-5 w-5 text-cyan-600 dark:text-cyan-400 pointer-events-none" />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery("")}
                                className="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs bg-slate-100 dark:bg-white/10 rounded-full w-5 h-5 flex items-center justify-center transition-colors cursor-pointer"
                                aria-label="Clear search"
                            >
                                <X className="w-3 h-3" />
                            </button>
                        )}
                    </div>
                </div>

                {/* ========================================================================= */}
                {/* 3. FAQ ACCORDION LIST (Increased width from max-w-3xl to max-w-5xl)        */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6 py-8 pb-24 max-w-5xl">
                    {searchQuery ? (
                        /* --- Search Results State --- */
                        <div className="space-y-4">
                            <div className="flex items-center justify-between mb-6 pb-3 border-b border-slate-200/80 dark:border-white/10">
                                <h3 className="font-display text-xl sm:text-2xl font-bold text-slate-900 dark:text-white uppercase tracking-wide">
                                    Search Results ({filteredFaqs.length})
                                </h3>
                                <button
                                    onClick={() => setSearchQuery("")}
                                    className="text-xs text-cyan-600 dark:text-cyan-400 hover:underline font-semibold"
                                >
                                    Clear Search
                                </button>
                            </div>

                            {filteredFaqs.length > 0 ? (
                                filteredFaqs.map((faq) => (
                                    <div
                                        key={faq.id}
                                        className="rounded-2xl border border-slate-200/90 dark:border-white/10 bg-white/90 dark:bg-[#0c1424]/85 backdrop-blur-md overflow-hidden shadow-sm hover:shadow-md hover:border-cyan-500/50 transition-all duration-200"
                                    >
                                        <button
                                            onClick={() =>
                                                setOpenId(
                                                    openId === faq.id
                                                        ? null
                                                        : faq.id,
                                                )
                                            }
                                            className="w-full flex items-center justify-between p-6 sm:p-7 text-left focus:outline-none group cursor-pointer"
                                        >
                                            <span className="font-semibold text-base sm:text-lg text-slate-900 dark:text-white pr-6 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                                {faq.question}
                                            </span>
                                            <span
                                                className={cn(
                                                    "w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300",
                                                    openId === faq.id
                                                        ? "bg-cyan-500 text-white shadow-[0_0_12px_rgba(6,182,212,0.4)]"
                                                        : "bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 group-hover:bg-cyan-50 dark:group-hover:bg-cyan-500/10 group-hover:text-cyan-600 dark:group-hover:text-cyan-400",
                                                )}
                                            >
                                                <ChevronDown
                                                    className={cn(
                                                        "h-5 w-5 transition-transform duration-300",
                                                        openId === faq.id
                                                            ? "rotate-180"
                                                            : "",
                                                    )}
                                                />
                                            </span>
                                        </button>
                                        <AnimatePresence>
                                            {openId === faq.id && (
                                                <motion.div
                                                    initial={{
                                                        height: 0,
                                                        opacity: 0,
                                                    }}
                                                    animate={{
                                                        height: "auto",
                                                        opacity: 1,
                                                    }}
                                                    exit={{
                                                        height: 0,
                                                        opacity: 0,
                                                    }}
                                                    transition={{
                                                        duration: 0.28,
                                                        ease: "easeInOut",
                                                    }}
                                                >
                                                    <div
                                                        className="px-6 sm:px-7 pb-6 sm:pb-7 text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/5 pt-4 bg-slate-50/50 dark:bg-white/[0.02] whitespace-pre-line prose prose-sm dark:prose-invert max-w-none text-sm sm:text-base"
                                                        dangerouslySetInnerHTML={{
                                                            __html: faq.answer,
                                                        }}
                                                    />
                                                </motion.div>
                                            )}
                                        </AnimatePresence>
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-12 rounded-2xl border border-dashed border-slate-300 dark:border-white/10 bg-white/50 dark:bg-white/[0.02]">
                                    <p className="text-slate-500 dark:text-slate-400 text-base mb-2">
                                        No results found for "{searchQuery}".
                                    </p>
                                    <p className="text-xs text-slate-400 dark:text-slate-500">
                                        Try searching with different keywords like
                                        "shipping", "warranty", or "payment".
                                    </p>
                                </div>
                            )}
                        </div>
                    ) : (
                        /* --- Default Grouped Categories State --- */
                        <div className="space-y-12">
                            {categories.map((category) => {
                                const categoryFaqs = faqs.filter(
                                    (f) => f.category === category,
                                );
                                return (
                                    <div key={category}>
                                        <div className="flex items-center gap-3 mb-6 pb-3 border-b border-slate-200/80 dark:border-white/10">
                                            <span className="w-2 h-6 rounded-full bg-cyan-500" />
                                            <h2 className="font-display text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                                {category}
                                            </h2>
                                        </div>

                                        <div className="space-y-4">
                                            {categoryFaqs.map((faq) => (
                                                <div
                                                    key={faq.id}
                                                    className="rounded-2xl border border-slate-200/90 dark:border-white/10 bg-white/90 dark:bg-[#0c1424]/85 backdrop-blur-md overflow-hidden shadow-sm hover:shadow-md hover:border-cyan-500/50 transition-all duration-200"
                                                >
                                                    <button
                                                        onClick={() =>
                                                            setOpenId(
                                                                openId ===
                                                                    faq.id
                                                                    ? null
                                                                    : faq.id,
                                                            )
                                                        }
                                                        className="w-full flex items-center justify-between p-6 sm:p-7 text-left focus:outline-none group cursor-pointer"
                                                    >
                                                        <span className="font-semibold text-base sm:text-lg text-slate-900 dark:text-white pr-6 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                                                            {faq.question}
                                                        </span>
                                                        <span
                                                            className={cn(
                                                                "w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300",
                                                                openId ===
                                                                    faq.id
                                                                    ? "bg-cyan-500 text-white shadow-[0_0_12px_rgba(6,182,212,0.4)]"
                                                                    : "bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 group-hover:bg-cyan-50 dark:group-hover:bg-cyan-500/10 group-hover:text-cyan-600 dark:group-hover:text-cyan-400",
                                                            )}
                                                        >
                                                            <ChevronDown
                                                                className={cn(
                                                                    "h-5 w-5 transition-transform duration-300",
                                                                    openId ===
                                                                        faq.id
                                                                        ? "rotate-180"
                                                                        : "",
                                                                )}
                                                            />
                                                        </span>
                                                    </button>
                                                    <AnimatePresence>
                                                        {openId === faq.id && (
                                                            <motion.div
                                                                initial={{
                                                                    height: 0,
                                                                    opacity: 0,
                                                                }}
                                                                animate={{
                                                                    height: "auto",
                                                                    opacity: 1,
                                                                }}
                                                                exit={{
                                                                    height: 0,
                                                                    opacity: 0,
                                                                }}
                                                                transition={{
                                                                    duration: 0.28,
                                                                    ease: "easeInOut",
                                                                }}
                                                            >
                                                                <div
                                                                    className="px-6 sm:px-7 pb-6 sm:pb-7 text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/5 pt-4 bg-slate-50/50 dark:bg-white/[0.02] whitespace-pre-line prose prose-sm dark:prose-invert max-w-none text-sm sm:text-base"
                                                                    dangerouslySetInnerHTML={{
                                                                        __html: faq.answer,
                                                                    }}
                                                                />
                                                            </motion.div>
                                                        )}
                                                    </AnimatePresence>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
