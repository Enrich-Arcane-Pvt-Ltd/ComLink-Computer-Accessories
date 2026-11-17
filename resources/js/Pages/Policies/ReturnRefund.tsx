import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
    CheckCircle2,
    Clock,
    RefreshCw,
    RotateCcw,
    ShieldAlert,
    ShieldCheck,
    Truck,
} from "lucide-react";
import React from "react";

interface RefundData {
    title: string;
    last_updated: string;
    content: string;
}

interface ReturnRefundProps {
    auth: any;
    refunds: RefundData;
}

/* -------------------------------------------------------------------------- */
/*  Vector Returns & Refund Illustration Elements (Reference Image Style)     */
/* -------------------------------------------------------------------------- */

// 1. Rotating Mechanical Gear / Cog
function GearSVG({
    className,
}: {
    className?: string;
}) {
    return (
        <svg
            viewBox="0 0 100 100"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <path d="M 43 5 H 57 L 59 16 A 35 35 0 0 1 68 20 L 78 12 L 88 22 L 80 32 A 35 35 0 0 1 84 41 L 95 43 V 57 L 84 59 A 35 35 0 0 1 80 68 L 88 78 L 78 88 L 68 80 A 35 35 0 0 1 59 84 L 57 95 H 43 L 41 84 A 35 35 0 0 1 32 80 L 22 88 L 12 78 L 20 68 A 35 35 0 0 1 16 59 L 5 57 V 43 L 16 41 A 35 35 0 0 1 20 32 L 12 22 L 22 12 L 32 20 A 35 35 0 0 1 41 16 Z M 50 35 A 15 15 0 1 0 50 65 A 15 15 0 1 0 50 35 Z" />
        </svg>
    );
}

// 2. 3D Return Parcel with White Curved Return Arrow
function ReturnBoxSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 120 110"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Top face */}
            <polygon
                points="60,10 105,32 60,54 15,32"
                fill="url(#boxTopGrad)"
            />
            {/* Left face */}
            <polygon points="15,32 60,54 60,100 15,78" fill="#0284c7" />
            {/* Right face */}
            <polygon points="60,54 105,32 105,78 60,100" fill="#0369a1" />

            {/* Packaging Tape */}
            <polygon
                points="54,13 66,19 66,51 54,45"
                fill="#fef3c7"
                opacity="0.85"
            />
            <polygon
                points="54,45 66,51 66,97 54,91"
                fill="#fef3c7"
                opacity="0.85"
            />

            {/* Curved Return Arrow on left face */}
            <g transform="translate(24, 48)">
                <path
                    d="M 22 24 C 22 14 16 8 6 8 L 8 4 L 0 9 L 8 14 L 6 10 C 14 10 18 14 18 24 Z"
                    fill="white"
                    className="drop-shadow-sm"
                />
            </g>

            <defs>
                <linearGradient
                    id="boxTopGrad"
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="100%"
                >
                    <stop offset="0%" stopColor="#7dd3fc" />
                    <stop offset="100%" stopColor="#38bdf8" />
                </linearGradient>
            </defs>
        </svg>
    );
}

// 3. Verified Return Package with Checkmark Badge
function VerifiedPackageSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Dotted border circle */}
            <circle
                cx="50"
                cy="50"
                r="44"
                fill="#0284c7"
                fillOpacity="0.1"
                stroke="#38bdf8"
                strokeWidth="2"
                strokeDasharray="4 4"
            />
            {/* 3D Cube */}
            <polygon points="50,22 74,34 50,46 26,34" fill="#38bdf8" />
            <polygon points="26,34 50,46 50,70 26,58" fill="#0284c7" />
            <polygon points="50,46 74,34 74,58 50,70" fill="#0369a1" />
            {/* Verified Emerald Badge */}
            <circle cx="70" cy="70" r="16" fill="#10b981" />
            <path
                d="M 64 70 L 68 74 L 76 66"
                stroke="white"
                strokeWidth="3"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

// 4. Indoor Potted Plant with Stylized Leaves
function PottedPlantSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 130"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Botanical Blue Leaves */}
            <path d="M 50 70 Q 20 40 32 10 Q 52 35 50 70 Z" fill="#0284c7" />
            <path d="M 50 70 Q 15 65 10 35 Q 35 45 50 70 Z" fill="#38bdf8" />
            <path d="M 50 70 Q 75 40 68 15 Q 52 40 50 70 Z" fill="#0369a1" />
            <path d="M 50 70 Q 85 65 90 35 Q 65 48 50 70 Z" fill="#0ea5e9" />
            {/* Geometric Pot */}
            <polygon
                points="32,75 68,75 62,118 38,118"
                fill="#0f172a"
                className="dark:fill-slate-800"
            />
            <rect
                x="28"
                y="70"
                width="44"
                height="8"
                rx="2"
                fill="#1e293b"
                className="dark:fill-slate-700"
            />
        </svg>
    );
}

// 5. Browser Mockup Return Portal
function BrowserMockupSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 280 180"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Window Container */}
            <rect
                x="2"
                y="2"
                width="276"
                height="176"
                rx="12"
                fill="currentColor"
                className="text-white/60 dark:text-[#0f172a]/70"
            />
            <rect
                x="2"
                y="2"
                width="276"
                height="176"
                rx="12"
                stroke="#0284c7"
                strokeWidth="2"
                strokeOpacity="0.25"
            />
            {/* Header bar */}
            <path
                d="M 2 14 A 12 12 0 0 1 14 2 H 266 A 12 12 0 0 1 278 14 V 28 H 2 Z"
                fill="#0f172a"
                fillOpacity="0.85"
            />
            {/* macOS Buttons */}
            <circle cx="16" cy="15" r="4" fill="#ef4444" />
            <circle cx="28" cy="15" r="4" fill="#f59e0b" />
            <circle cx="40" cy="15" r="4" fill="#10b981" />
            {/* URL bar */}
            <rect
                x="65"
                y="8"
                width="130"
                height="14"
                rx="7"
                fill="white"
                fillOpacity="0.1"
            />
            {/* Cart icons */}
            <circle cx="240" cy="15" r="4" fill="white" fillOpacity="0.5" />
            <circle cx="256" cy="15" r="4" fill="white" fillOpacity="0.5" />
            {/* Inner Return Product Mock */}
            <rect
                x="24"
                y="45"
                width="80"
                height="50"
                rx="6"
                fill="#0284c7"
                fillOpacity="0.18"
                stroke="#0284c7"
                strokeWidth="1.5"
            />
            <path
                d="M 46 72 C 46 64 52 60 62 60 L 60 56 L 68 61 L 60 66 L 62 62 C 54 62 50 65 50 72 Z"
                fill="#0284c7"
            />
            {/* Mock text lines */}
            <rect
                x="120"
                y="50"
                width="130"
                height="8"
                rx="4"
                fill="#0284c7"
                fillOpacity="0.7"
            />
            <rect
                x="120"
                y="65"
                width="100"
                height="6"
                rx="3"
                fill="#64748b"
                fillOpacity="0.4"
            />
            <rect
                x="120"
                y="78"
                width="80"
                height="6"
                rx="3"
                fill="#64748b"
                fillOpacity="0.3"
            />
            {/* Orange Action CTA Button */}
            <rect x="120" y="92" width="65" height="15" rx="5" fill="#f59e0b" />
            <path
                d="M 148 99 H 158 M 155 96 L 158 99 L 155 102"
                stroke="white"
                strokeWidth="1.5"
                strokeLinecap="round"
            />
            {/* Mock Return Form Inputs */}
            <rect
                x="24"
                y="118"
                width="232"
                height="18"
                rx="4"
                fill="#e2e8f0"
                fillOpacity="0.5"
            />
            <rect
                x="24"
                y="142"
                width="232"
                height="22"
                rx="4"
                fill="#e2e8f0"
                fillOpacity="0.5"
            />
        </svg>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Returns & Refunds Page Component                                     */
/* -------------------------------------------------------------------------- */

export default function ReturnRefund({ auth, refunds }: ReturnRefundProps) {
    return (
        <AppLayout>
            <Head title={refunds?.title || "Returns, Refunds & Cancellations"} />

            <div
                className="relative overflow-hidden min-h-screen bg-gradient-to-b from-[#eaf4ff] via-[#f1f7ff] to-[#e8f3fe] dark:from-[#060f1c] dark:via-[#09182d] dark:to-[#050b15] text-slate-800 dark:text-slate-200 transition-colors duration-300 py-16 md:py-24"
                style={{ contain: "paint" }}
            >
                {/* ========================================================================= */}
                {/* 1. CREATIVE RETURNS & REFUNDS VECTOR ILLUSTRATION BACKGROUND              */}
                {/* ========================================================================= */}
                <div
                    className="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
                    style={{ contain: "paint" }}
                >
                    {/* Atmospheric Glow Blurs */}
                    <div className="absolute left-1/2 top-1/4 -translate-x-1/2 -translate-y-1/2 w-[900px] max-w-full h-[550px] bg-gradient-to-tr from-cyan-400/20 via-sky-300/25 to-blue-400/15 dark:from-cyan-500/12 dark:via-blue-600/10 dark:to-cyan-400/10 blur-[140px] rounded-full" />
                    <div className="absolute -top-20 -left-20 w-[450px] h-[450px] bg-cyan-400/20 dark:bg-cyan-500/10 blur-[130px] rounded-full" />
                    <div className="absolute -bottom-24 -right-24 w-[480px] h-[480px] bg-amber-500/15 dark:bg-amber-600/10 blur-[130px] rounded-full" />

                    {/* --- ANIMATED VECTOR LOGISTICS & PROCESS ASSETS --- */}

                    {/* 1. Large Amber Rotating Gear (Top-Right) */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 26,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-[6%] right-[5%] w-[120px] sm:w-[150px] text-amber-500 dark:text-amber-400 drop-shadow-md opacity-75 dark:opacity-60"
                    >
                        <GearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 2. Small Cyan Counter-Rotating Gear (Top-Right Accent) */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 18,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-[17%] right-[11%] w-[65px] sm:w-[80px] text-cyan-500 dark:text-cyan-400 drop-shadow-md opacity-70 dark:opacity-60"
                    >
                        <GearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 3. Medium Cyan Rotating Gear (Mid-Left) */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 22,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-[36%] left-[4%] w-[110px] sm:w-[135px] text-sky-500 dark:text-cyan-500 drop-shadow-md opacity-70 dark:opacity-60"
                    >
                        <GearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 4. Small Accent Gear (Top-Left) */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 16,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-[18%] left-[7%] w-[60px] sm:w-[75px] text-cyan-400 dark:text-cyan-400 drop-shadow-md opacity-65 dark:opacity-50"
                    >
                        <GearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 5. 3D Return Parcel with Return Arrow (Floating Top-Left) */}
                    <motion.div
                        initial={{ y: 0, rotate: -4 }}
                        animate={{
                            y: [-12, 12, -12],
                            rotate: [-6, 2, -6],
                        }}
                        transition={{
                            duration: 7.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-[6%] left-[4%] w-[130px] sm:w-[160px] drop-shadow-xl"
                    >
                        <ReturnBoxSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 6. Verified Package with Checkmark (Floating Mid-Right) */}
                    <motion.div
                        initial={{ y: 0, rotate: 6 }}
                        animate={{
                            y: [-9, 9, -9],
                            rotate: [3, 9, 3],
                        }}
                        transition={{
                            duration: 6.8,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 0.6,
                        }}
                        className="absolute top-[46%] right-[4%] w-[115px] sm:w-[140px] drop-shadow-lg"
                    >
                        <VerifiedPackageSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 7. Botanical Potted Plant (Swaying Bottom-Left) */}
                    <motion.div
                        initial={{ rotate: 0 }}
                        animate={{ rotate: [-2.5, 2.5, -2.5] }}
                        transition={{
                            duration: 6.0,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute bottom-[4%] left-[4%] w-[110px] sm:w-[135px] drop-shadow-lg origin-bottom"
                    >
                        <PottedPlantSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 8. Online Return Portal Browser Window (Floating Bottom-Right) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{ y: [-8, 8, -8] }}
                        transition={{
                            duration: 8.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 1.0,
                        }}
                        className="absolute bottom-[4%] right-[3%] w-[210px] sm:w-[260px] drop-shadow-2xl opacity-75 dark:opacity-50"
                    >
                        <BrowserMockupSVG className="w-full h-auto" />
                    </motion.div>
                </div>

                {/* ========================================================================= */}
                {/* 2. RETURNS & REFUNDS POLICY CONTENT CARD                                  */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6 max-w-4xl">
                    <div className="rounded-3xl border border-slate-200/90 dark:border-white/10 bg-white/92 dark:bg-[#0c1424]/90 backdrop-blur-xl p-8 sm:p-12 md:p-16 shadow-2xl shadow-cyan-950/5 dark:shadow-black/40">
                        {/* Header Section */}
                        <div className="text-center mb-12 border-b border-slate-200/80 dark:border-white/10 pb-8">
                            <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-3">
                                <RotateCcw className="w-4 h-4" />
                                ComLink Customer Satisfaction Promise
                            </span>
                            <h1 className="font-display text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                                {refunds?.title || "Returns, Refunds & Cancellations"}
                            </h1>
                            {refunds?.last_updated && (
                                <p className="text-slate-500 dark:text-slate-400 text-sm flex items-center justify-center gap-2">
                                    <Clock className="w-4 h-4 text-cyan-500" />
                                    <span>Last Updated: {refunds.last_updated}</span>
                                </p>
                            )}

                            {/* Trust Pillars Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-white/5 text-left">
                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <RotateCcw className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            7-Day Easy Returns
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Hassle-free return claims
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <ShieldCheck className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Genuine Refunds
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Original payment method
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <RefreshCw className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Warranty Swaps
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Fast distributor replacements
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* HTML Policy Content Render */}
                        {refunds?.content && (
                            <div
                                className="prose prose-base sm:prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed prose-headings:font-display prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:uppercase prose-headings:tracking-wide prose-a:text-cyan-600 dark:prose-a:text-cyan-400 prose-a:underline hover:prose-a:text-cyan-700 space-y-6"
                                dangerouslySetInnerHTML={{
                                    __html: refunds.content,
                                }}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
