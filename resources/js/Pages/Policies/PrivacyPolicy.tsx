import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
    Clock,
    EyeOff,
    FileCheck2,
    KeyRound,
    Lock,
    Shield,
    ShieldCheck,
    Sparkles,
    UserCheck,
} from "lucide-react";
import React from "react";

interface PrivacyData {
    title: string;
    last_updated: string;
    content: string;
}

interface PrivacyPolicyProps {
    auth: any;
    privacy: PrivacyData;
}

/* -------------------------------------------------------------------------- */
/*  Creative & Minimalist Vector Privacy Icons                                */
/* -------------------------------------------------------------------------- */

// 1. Sleek Floating Cyber Shield with Keyhole
function MinimalCyberShieldSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 120"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <defs>
                <linearGradient id="shieldFillGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stopColor="#38bdf8" stopOpacity="0.25" />
                    <stop offset="100%" stopColor="#0284c7" stopOpacity="0.08" />
                </linearGradient>
                <filter id="shieldNeonGlow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="0" stdDeviation="6" floodColor="#00f0ff" floodOpacity="0.5" />
                </filter>
            </defs>

            {/* Shield Contour */}
            <path
                d="M 50 10 C 76 10, 90 18, 90 42 C 90 74, 68 96, 50 110 C 32 96, 10 74, 10 42 C 10 18, 24 10, 50 10 Z"
                fill="url(#shieldFillGrad)"
                stroke="#38bdf8"
                strokeWidth="2.5"
                filter="url(#shieldNeonGlow)"
            />

            {/* Inner Border */}
            <path
                d="M 50 18 C 70 18, 82 24, 82 44 C 82 70, 64 88, 50 100 C 36 88, 18 70, 18 44 C 18 24, 30 18, 50 18 Z"
                stroke="#67e8f9"
                strokeWidth="1.2"
                strokeDasharray="4 3"
                opacity="0.6"
            />

            {/* Keyhole */}
            <circle cx="50" cy="48" r="6" fill="#ffffff" />
            <path d="M 47 50 L 45 64 H 55 L 53 50 Z" fill="#ffffff" />
        </svg>
    );
}

// 2. Pointy-topped Hexagon Mathematical Path
function getHexPoints(cx: number, cy: number, r: number) {
    const points: string[] = [];
    for (let i = 0; i < 6; i++) {
        const angle = -Math.PI / 2 + (i * Math.PI) / 3;
        points.push(`${(cx + r * Math.cos(angle)).toFixed(1)},${(cy + r * Math.sin(angle)).toFixed(1)}`);
    }
    return points.join(" ");
}

// 3. Floating Glass Hexagon Badge with Padlock
function HexPadlockBadgeSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 90 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <polygon
                points={getHexPoints(45, 50, 42)}
                fill="#0369a1"
                fillOpacity="0.15"
                stroke="#38bdf8"
                strokeWidth="2"
            />
            <polygon
                points={getHexPoints(45, 50, 24)}
                fill="#0284c7"
                fillOpacity="0.3"
                stroke="#67e8f9"
                strokeWidth="1.2"
                strokeDasharray="3 2"
            />
            {/* Padlock Icon in Center */}
            <path
                d="M 39 46 V 39 C 39 34, 51 34, 51 39 V 46"
                stroke="#ffffff"
                strokeWidth="2.2"
                strokeLinecap="round"
                fill="none"
            />
            <rect x="35" y="46" width="20" height="15" rx="3" fill="#38bdf8" stroke="#ffffff" strokeWidth="1.5" />
            <circle cx="45" cy="52" r="2" fill="#ffffff" />
            <line x1="45" y1="52" x2="45" y2="57" stroke="#ffffff" strokeWidth="1.5" />
        </svg>
    );
}

// 4. Floating Solid Glowing Hexagon
function HexGlowDotSVG({
    className,
    isCyan = true,
}: {
    className?: string;
    isCyan?: boolean;
}) {
    return (
        <svg
            viewBox="0 0 60 70"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <polygon
                points={getHexPoints(30, 35, 26)}
                fill={isCyan ? "#06b6d4" : "#2563eb"}
                fillOpacity={isCyan ? "0.85" : "0.75"}
                stroke={isCyan ? "#a5f3fc" : "#93c5fd"}
                strokeWidth="1.8"
            />
        </svg>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Privacy Policy Component                                             */
/* -------------------------------------------------------------------------- */

export default function PrivacyPolicy({ auth, privacy }: PrivacyPolicyProps) {
    return (
        <AppLayout>
            <Head title={privacy?.title || "Privacy Policy"} />

            <div className="relative min-h-screen bg-slate-50 dark:bg-[#070e1b] py-16 md:py-24 overflow-x-hidden selection:bg-cyan-500/20 selection:text-cyan-700 dark:selection:text-cyan-300">
                {/* ========================================================================= */}
                {/* 1. CREATIVE & MINIMALIST CYBERSECURITY BACKGROUND                         */}
                {/* ========================================================================= */}
                <div
                    className="absolute inset-0 pointer-events-none select-none overflow-hidden"
                    style={{ contain: "paint" }}
                >
                    {/* Soft radial background ambiance */}
                    <div className="absolute inset-0 bg-radial-gradient from-transparent via-slate-100/60 to-slate-200/80 dark:from-transparent dark:via-[#0c1830]/70 dark:to-[#040812]" />

                    {/* Calming ambient glow orbs */}
                    <div className="absolute top-[12%] right-[12%] w-[550px] h-[380px] bg-cyan-500/12 dark:bg-cyan-500/16 blur-[130px] rounded-full" />
                    <div className="absolute top-[40%] left-[8%] w-[500px] h-[350px] bg-sky-500/10 dark:bg-sky-500/14 blur-[120px] rounded-full" />
                    <div className="absolute bottom-[10%] right-[18%] w-[480px] h-[300px] bg-blue-600/10 dark:bg-blue-600/12 blur-[130px] rounded-full" />

                    {/* Subtle, minimal circuit hairline tracks */}
                    <svg
                        viewBox="0 0 1440 900"
                        className="absolute inset-0 w-full h-full opacity-20 dark:opacity-25"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <g stroke="#38bdf8" strokeWidth="1.2" strokeLinecap="round">
                            {/* Top right trace */}
                            <path d="M 1120 120 H 1220 L 1260 80 H 1340" />
                            <circle cx="1340" cy="80" r="3" fill="#ffffff" />

                            {/* Mid left trace */}
                            <path d="M 100 360 H 180 L 220 400 H 280" />
                            <circle cx="100" cy="360" r="3" fill="#ffffff" />

                            {/* Bottom right trace */}
                            <path d="M 1160 680 H 1240 L 1280 720 H 1360" />
                            <circle cx="1360" cy="720" r="3" fill="#ffffff" />
                        </g>
                    </svg>

                    {/* 1. Floating Cyber Shield (Top-Right Zone) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [-10, 10, -10],
                            rotate: [-2, 2, -2],
                        }}
                        transition={{
                            duration: 7,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-12 sm:top-16 right-[4%] sm:right-[8%] md:right-[11%] w-[100px] sm:w-[130px] md:w-[150px] drop-shadow-[0_10px_25px_rgba(6,182,212,0.35)]"
                    >
                        <MinimalCyberShieldSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 2. Floating Hexagon Padlock Badge (Top-Left Zone) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [9, -9, 9],
                            rotate: [2, -2, 2],
                        }}
                        transition={{
                            duration: 6.5,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 0.5,
                        }}
                        className="absolute top-16 sm:top-20 left-[4%] sm:left-[8%] md:left-[11%] w-[85px] sm:w-[110px] md:w-[125px] drop-shadow-[0_8px_20px_rgba(56,189,248,0.3)]"
                    >
                        <HexPadlockBadgeSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 3. Floating Solid Cyan Hex Accent (Top-Center Right) */}
                    <motion.div
                        initial={{ scale: 1, opacity: 0.8 }}
                        animate={{
                            scale: [0.95, 1.06, 0.95],
                            opacity: [0.75, 1, 0.75],
                        }}
                        transition={{
                            duration: 4.8,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 0.3,
                        }}
                        className="absolute top-28 right-[24%] sm:right-[26%] w-[38px] sm:w-[48px] drop-shadow-[0_0_15px_#00f0ff] hidden md:block"
                    >
                        <HexGlowDotSVG isCyan={true} className="w-full h-auto" />
                    </motion.div>

                    {/* 4. Mid-Left Floating Shield Check Badge */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [-8, 8, -8],
                        }}
                        transition={{
                            duration: 6.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 1.0,
                        }}
                        className="absolute top-[42%] left-[3%] sm:left-[5%] p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-400/30 backdrop-blur-md shadow-[0_0_20px_rgba(6,182,212,0.25)] hidden lg:flex items-center gap-2.5"
                    >
                        <ShieldCheck className="w-5 h-5 text-cyan-400" />
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-cyan-200">
                            SSL 256-Bit Secured
                        </span>
                    </motion.div>

                    {/* 5. Mid-Right Floating Solid Blue Hex Accent */}
                    <motion.div
                        initial={{ scale: 1 }}
                        animate={{
                            scale: [1, 1.1, 1],
                            y: [6, -6, 6],
                        }}
                        transition={{
                            duration: 5.5,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 0.8,
                        }}
                        className="absolute top-[46%] right-[3%] sm:right-[6%] w-[42px] sm:w-[52px] drop-shadow-[0_0_15px_rgba(37,99,235,0.4)] hidden md:block"
                    >
                        <HexGlowDotSVG isCyan={false} className="w-full h-auto" />
                    </motion.div>

                    {/* 6. Bottom-Right Floating Hexagon Padlock */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [-7, 7, -7],
                            rotate: [-1.5, 1.5, -1.5],
                        }}
                        transition={{
                            duration: 7.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 1.2,
                        }}
                        className="absolute bottom-12 right-[4%] sm:right-[8%] w-[80px] sm:w-[105px] opacity-85 drop-shadow-[0_8px_20px_rgba(56,189,248,0.25)]"
                    >
                        <HexPadlockBadgeSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 7. Bottom-Left Floating User Privacy Badge */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [8, -8, 8],
                        }}
                        transition={{
                            duration: 6.8,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 1.4,
                        }}
                        className="absolute bottom-16 left-[3%] sm:left-[6%] p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-400/30 backdrop-blur-md shadow-[0_0_20px_rgba(6,182,212,0.25)] hidden lg:flex items-center gap-2.5"
                    >
                        <Lock className="w-5 h-5 text-cyan-400" />
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-cyan-200">
                            Zero Data Selling
                        </span>
                    </motion.div>
                </div>

                {/* ========================================================================= */}
                {/* 2. PRIVACY POLICY GLASSMORPHIC CONTENT CONTAINER                          */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6 max-w-4xl">
                    <div className="rounded-3xl border border-slate-200/90 dark:border-white/10 bg-white/92 dark:bg-[#0c1424]/90 backdrop-blur-xl p-8 sm:p-12 md:p-16 shadow-2xl shadow-cyan-950/5 dark:shadow-black/50">
                        {/* Header Section */}
                        <div className="text-center mb-12 border-b border-slate-200/80 dark:border-white/10 pb-8">
                            <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-3 px-3.5 py-1.5 rounded-full bg-cyan-50 dark:bg-cyan-500/10 border border-cyan-200/70 dark:border-cyan-500/20 shadow-sm">
                                <ShieldCheck className="w-4 h-4 text-cyan-500" />
                                ComLink Privacy & Cyber Security Guarantee
                            </span>
                            <h1 className="font-display text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                                {privacy?.title || "Privacy Policy"}
                            </h1>
                            {privacy?.last_updated && (
                                <p className="text-slate-500 dark:text-slate-400 text-sm flex items-center justify-center gap-2">
                                    <Clock className="w-4 h-4 text-cyan-500" />
                                    <span>Last Updated: {privacy.last_updated}</span>
                                </p>
                            )}

                            {/* Trust Pillars Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-white/5 text-left">
                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <Lock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            256-Bit SSL Encryption
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            End-to-end data security
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <EyeOff className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Zero Data Selling
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Never shared or monetized
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <UserCheck className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            User Control First
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Access, edit & purge your data
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* HTML Privacy Content Render */}
                        {privacy?.content && (
                            <div
                                className="prose prose-base sm:prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed prose-headings:font-display prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:uppercase prose-headings:tracking-wide prose-a:text-cyan-600 dark:prose-a:text-cyan-400 prose-a:underline hover:prose-a:text-cyan-700 space-y-6"
                                dangerouslySetInnerHTML={{
                                    __html: privacy.content,
                                }}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
