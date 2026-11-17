import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
    Clock,
    FileCheck2,
    Lock,
    Scale,
    ShieldCheck,
    Sparkles,
} from "lucide-react";
import React from "react";

interface TermsData {
    title: string;
    last_updated: string;
    content: string;
}

interface TermsOfServiceProps {
    auth: any;
    terms: TermsData;
}

/* -------------------------------------------------------------------------- */
/*  Vector Mechanical Gears & Granular Particle Field (Reference Image Style) */
/* -------------------------------------------------------------------------- */

// 1. SpokeGearSVG - 12 Outer Teeth + 6 Spoke Holes + Center Hub
function SpokeGearSVG({
    className,
    isCyan = false,
}: {
    className?: string;
    isCyan?: boolean;
}) {
    const teeth = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330];
    const spokes = [0, 60, 120, 180, 240, 300];
    const id = React.useId();

    return (
        <svg
            viewBox="0 0 100 100"
            className={className}
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <linearGradient
                    id={`spokeGrad-${id}`}
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="100%"
                >
                    <stop
                        offset="0%"
                        stopColor={isCyan ? "#7dd3fc" : "#ffffff"}
                    />
                    <stop
                        offset="50%"
                        stopColor={isCyan ? "#06b6d4" : "#e2e8f0"}
                    />
                    <stop
                        offset="100%"
                        stopColor={isCyan ? "#0284c7" : "#cbd5e1"}
                    />
                </linearGradient>
                <filter
                    id={`gearShadow-${id}`}
                    x="-20%"
                    y="-20%"
                    width="140%"
                    height="140%"
                >
                    <feDropShadow
                        dx="0"
                        dy="4"
                        stdDeviation="4"
                        floodOpacity={isCyan ? "0.45" : "0.22"}
                        floodColor={isCyan ? "#06b6d4" : "#000000"}
                    />
                </filter>
            </defs>
            <g filter={`url(#gearShadow-${id})`}>
                {/* Outer Teeth */}
                {teeth.map((deg) => (
                    <rect
                        key={deg}
                        x="45"
                        y="2.5"
                        width="10"
                        height="13"
                        rx="2"
                        transform={`rotate(${deg} 50 50)`}
                        fill={`url(#spokeGrad-${id})`}
                    />
                ))}
                {/* Main Outer Rim */}
                <circle cx="50" cy="50" r="41" fill={`url(#spokeGrad-${id})`} />
                {/* Groove Inset */}
                <circle
                    cx="50"
                    cy="50"
                    r="33"
                    fill={isCyan ? "#083344" : "#94a3b8"}
                    opacity="0.35"
                />
                <circle cx="50" cy="50" r="30" fill={`url(#spokeGrad-${id})`} />
                {/* Spoke Cutout Holes */}
                {spokes.map((deg) => (
                    <circle
                        key={deg}
                        cx="50"
                        cy="28"
                        r="5.5"
                        transform={`rotate(${deg} 50 50)`}
                        className="dark:fill-[#0c1424] fill-slate-100"
                    />
                ))}
                {/* Center Hub & Axle */}
                <circle
                    cx="50"
                    cy="50"
                    r="14"
                    fill={isCyan ? "#0284c7" : "#cbd5e1"}
                />
                <circle cx="50" cy="50" r="9" fill={`url(#spokeGrad-${id})`} />
                <circle
                    cx="50"
                    cy="50"
                    r="5"
                    className="dark:fill-[#0c1424] fill-slate-100"
                />
            </g>
        </svg>
    );
}

// 2. DualStackedGearSVG - Double-Decker 3D Gear (Large base gear + stacked small gear)
function DualStackedGearSVG({
    className,
    isCyan = false,
}: {
    className?: string;
    isCyan?: boolean;
}) {
    const baseTeeth = Array.from({ length: 16 }, (_, i) => i * 22.5);
    const topTeeth = Array.from({ length: 8 }, (_, i) => i * 45);
    const id = React.useId();

    return (
        <svg
            viewBox="0 0 100 100"
            className={className}
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <linearGradient
                    id={`baseGrad-${id}`}
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="100%"
                >
                    <stop
                        offset="0%"
                        stopColor={isCyan ? "#38bdf8" : "#f8fafc"}
                    />
                    <stop
                        offset="100%"
                        stopColor={isCyan ? "#0284c7" : "#94a3b8"}
                    />
                </linearGradient>
                <linearGradient
                    id={`topGrad-${id}`}
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="100%"
                >
                    <stop
                        offset="0%"
                        stopColor={isCyan ? "#a5f3fc" : "#ffffff"}
                    />
                    <stop
                        offset="100%"
                        stopColor={isCyan ? "#0891b2" : "#cbd5e1"}
                    />
                </linearGradient>
                <filter
                    id={`dualShadow-${id}`}
                    x="-20%"
                    y="-20%"
                    width="140%"
                    height="140%"
                >
                    <feDropShadow
                        dx="0"
                        dy="5"
                        stdDeviation="5"
                        floodOpacity="0.3"
                        floodColor={isCyan ? "#06b6d4" : "#000000"}
                    />
                </filter>
            </defs>

            <g filter={`url(#dualShadow-${id})`}>
                {/* Base Tier Teeth */}
                {baseTeeth.map((deg) => (
                    <rect
                        key={deg}
                        x="46"
                        y="3"
                        width="8"
                        height="12"
                        rx="1.5"
                        transform={`rotate(${deg} 50 50)`}
                        fill={`url(#baseGrad-${id})`}
                    />
                ))}
                <circle cx="50" cy="50" r="41" fill={`url(#baseGrad-${id})`} />
                <circle
                    cx="50"
                    cy="50"
                    r="34"
                    fill={isCyan ? "#083344" : "#64748b"}
                    opacity="0.3"
                />

                {/* Top Tier (Stacked Smaller Gear) */}
                {topTeeth.map((deg) => (
                    <rect
                        key={`top-${deg}`}
                        x="46"
                        y="22"
                        width="8"
                        height="9"
                        rx="1.5"
                        transform={`rotate(${deg} 50 50)`}
                        fill={`url(#topGrad-${id})`}
                    />
                ))}
                <circle cx="50" cy="50" r="23" fill={`url(#topGrad-${id})`} />
                <circle
                    cx="50"
                    cy="50"
                    r="15"
                    fill={isCyan ? "#0e7490" : "#94a3b8"}
                    opacity="0.4"
                />
                <circle cx="50" cy="50" r="12" fill={`url(#topGrad-${id})`} />
                <circle
                    cx="50"
                    cy="50"
                    r="6"
                    className="dark:fill-[#0c1424] fill-slate-100"
                />
            </g>
        </svg>
    );
}

// 3. DenseTeethCogSVG - Industrial Fine-Tooth Gear (20 Teeth with Slotted Cutouts)
function DenseTeethCogSVG({
    className,
    isCyan = false,
}: {
    className?: string;
    isCyan?: boolean;
}) {
    const teeth = Array.from({ length: 20 }, (_, i) => i * 18);
    const slots = [0, 45, 90, 135, 180, 225, 270, 315];
    const id = React.useId();

    return (
        <svg
            viewBox="0 0 100 100"
            className={className}
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <linearGradient
                    id={`cogGrad-${id}`}
                    x1="0%"
                    y1="0%"
                    x2="100%"
                    y2="100%"
                >
                    <stop
                        offset="0%"
                        stopColor={isCyan ? "#67e8f9" : "#ffffff"}
                    />
                    <stop
                        offset="100%"
                        stopColor={isCyan ? "#06b6d4" : "#94a3b8"}
                    />
                </linearGradient>
                <filter
                    id={`cogShadow-${id}`}
                    x="-20%"
                    y="-20%"
                    width="140%"
                    height="140%"
                >
                    <feDropShadow
                        dx="0"
                        dy="4"
                        stdDeviation="4"
                        floodOpacity={isCyan ? "0.4" : "0.2"}
                        floodColor={isCyan ? "#06b6d4" : "#000000"}
                    />
                </filter>
            </defs>
            <g filter={`url(#cogShadow-${id})`}>
                {/* 20 Precise Teeth */}
                {teeth.map((deg) => (
                    <rect
                        key={deg}
                        x="46.5"
                        y="4"
                        width="7"
                        height="10"
                        rx="1.5"
                        transform={`rotate(${deg} 50 50)`}
                        fill={`url(#cogGrad-${id})`}
                    />
                ))}
                <circle cx="50" cy="50" r="41" fill={`url(#cogGrad-${id})`} />
                <circle
                    cx="50"
                    cy="50"
                    r="32"
                    fill={isCyan ? "#083344" : "#64748b"}
                    opacity="0.35"
                />
                <circle cx="50" cy="50" r="28" fill={`url(#cogGrad-${id})`} />

                {/* Slotted Spoke Openings */}
                {slots.map((deg) => (
                    <rect
                        key={deg}
                        x="47"
                        y="23"
                        width="6"
                        height="10"
                        rx="2"
                        transform={`rotate(${deg} 50 50)`}
                        className="dark:fill-[#0c1424] fill-slate-100"
                    />
                ))}

                {/* Core Hub */}
                <circle
                    cx="50"
                    cy="50"
                    r="12"
                    fill={isCyan ? "#0891b2" : "#cbd5e1"}
                />
                <circle
                    cx="50"
                    cy="50"
                    r="6"
                    className="dark:fill-[#0c1424] fill-slate-100"
                />
            </g>
        </svg>
    );
}

// 4. GranularBeadsField - Scattered micro-beads / particles across the canvas
function GranularBeadsField() {
    // Deterministic array of particles mimicking the scattered beads in reference photo
    const particles = [
        { x: "5%", y: "8%", size: 3, opacity: 0.6 },
        { x: "9%", y: "14%", size: 4, opacity: 0.8 },
        { x: "13%", y: "6%", size: 2.5, opacity: 0.5 },
        { x: "18%", y: "19%", size: 3.5, opacity: 0.7 },
        { x: "24%", y: "11%", size: 4.5, opacity: 0.9 },
        { x: "29%", y: "7%", size: 2.5, opacity: 0.4 },
        { x: "35%", y: "15%", size: 3, opacity: 0.6 },
        { x: "42%", y: "9%", size: 4, opacity: 0.75 },
        { x: "48%", y: "18%", size: 2.5, opacity: 0.5 },
        { x: "55%", y: "8%", size: 3.5, opacity: 0.7 },
        { x: "62%", y: "14%", size: 4, opacity: 0.8 },
        { x: "69%", y: "6%", size: 2.5, opacity: 0.4 },
        { x: "74%", y: "18%", size: 4.5, opacity: 0.85 },
        { x: "81%", y: "10%", size: 3, opacity: 0.6 },
        { x: "87%", y: "16%", size: 3.5, opacity: 0.75 },
        { x: "93%", y: "8%", size: 2.5, opacity: 0.5 },
        { x: "96%", y: "21%", size: 4, opacity: 0.8 },

        // Mid-sides
        { x: "4%", y: "32%", size: 4, opacity: 0.7 },
        { x: "7%", y: "45%", size: 3, opacity: 0.5 },
        { x: "3%", y: "60%", size: 3.5, opacity: 0.6 },
        { x: "6%", y: "75%", size: 4.5, opacity: 0.8 },
        { x: "94%", y: "35%", size: 3.5, opacity: 0.6 },
        { x: "97%", y: "52%", size: 4, opacity: 0.75 },
        { x: "93%", y: "68%", size: 3, opacity: 0.5 },
        { x: "96%", y: "82%", size: 4.5, opacity: 0.8 },

        // Bottom clusters
        { x: "6%", y: "88%", size: 3.5, opacity: 0.7 },
        { x: "12%", y: "94%", size: 4, opacity: 0.85 },
        { x: "19%", y: "86%", size: 2.5, opacity: 0.5 },
        { x: "26%", y: "92%", size: 4.5, opacity: 0.8 },
        { x: "33%", y: "87%", size: 3, opacity: 0.6 },
        { x: "41%", y: "95%", size: 3.5, opacity: 0.7 },
        { x: "49%", y: "89%", size: 2.5, opacity: 0.45 },
        { x: "57%", y: "93%", size: 4, opacity: 0.75 },
        { x: "65%", y: "88%", size: 3, opacity: 0.6 },
        { x: "72%", y: "94%", size: 4.5, opacity: 0.85 },
        { x: "80%", y: "87%", size: 3.5, opacity: 0.7 },
        { x: "88%", y: "93%", size: 4, opacity: 0.8 },
        { x: "94%", y: "91%", size: 2.5, opacity: 0.5 },
    ];

    return (
        <div className="absolute inset-0 pointer-events-none overflow-hidden select-none">
            {particles.map((p, idx) => (
                <motion.div
                    key={idx}
                    initial={{ opacity: p.opacity, scale: 1 }}
                    animate={{
                        opacity: [p.opacity * 0.7, p.opacity, p.opacity * 0.7],
                        scale: [0.9, 1.15, 0.9],
                    }}
                    transition={{
                        duration: 3 + (idx % 4),
                        repeat: Infinity,
                        repeatType: "mirror",
                        ease: "easeInOut",
                        delay: (idx % 7) * 0.35,
                    }}
                    style={{
                        left: p.x,
                        top: p.y,
                        width: `${p.size}px`,
                        height: `${p.size}px`,
                    }}
                    className="absolute rounded-full bg-slate-300 dark:bg-slate-100 shadow-[0_0_6px_rgba(56,189,248,0.5)]"
                />
            ))}
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Terms of Service Component                                           */
/* -------------------------------------------------------------------------- */

export default function TermsOfService({ auth, terms }: TermsOfServiceProps) {
    return (
        <AppLayout>
            <Head title={terms?.title || "Terms Of Service"} />

            <div className="relative min-h-screen bg-slate-50 dark:bg-[#070b13] py-16 md:py-24 overflow-x-hidden selection:bg-cyan-500/20 selection:text-cyan-700 dark:selection:text-cyan-300">
                {/* ========================================================================= */}
                {/* 1. DYNAMIC GEAR & GRANULAR BACKGROUND (REFERENCE IMAGE STYLE)             */}
                {/* ========================================================================= */}
                <div
                    className="absolute inset-0 pointer-events-none select-none overflow-hidden"
                    style={{ contain: "paint" }}
                >
                    {/* Dark gradient vignette & lighting glow */}
                    <div className="absolute inset-0 bg-radial-gradient from-transparent via-slate-100/60 to-slate-200/80 dark:from-transparent dark:via-[#0c1424]/70 dark:to-[#05080e]" />
                    <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[450px] bg-cyan-400/10 dark:bg-cyan-500/10 blur-[130px] rounded-full" />
                    <div className="absolute bottom-1/4 right-1/4 w-[500px] h-[350px] bg-sky-400/10 dark:bg-sky-500/10 blur-[120px] rounded-full" />

                    {/* Scattered Micro-Beads Particle Field */}
                    <GranularBeadsField />

                    {/* ----------------- TOP GEAR MECHANISM ----------------- */}

                    {/* 1. Top-Center Big Spoke Gear (Slow Clockwise Rotation) */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 55,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -top-16 sm:-top-20 left-1/2 -translate-x-1/2 w-[220px] sm:w-[280px] md:w-[340px] drop-shadow-2xl opacity-90 dark:opacity-85"
                    >
                        <SpokeGearSVG className="w-full h-auto text-slate-200 dark:text-slate-100" />
                    </motion.div>

                    {/* 2. Top-Left Dual Stacked Gear (Counter-Clockwise Rotation) */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 45,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -top-12 left-[4%] sm:left-[8%] w-[150px] sm:w-[200px] md:w-[240px] drop-shadow-2xl opacity-85"
                    >
                        <DualStackedGearSVG className="w-full h-auto text-slate-100" />
                    </motion.div>

                    {/* 3. Top-Right Glowing Ice-Cyan Gear (Clockwise Rotation) */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 40,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -top-8 right-[5%] sm:right-[9%] w-[160px] sm:w-[210px] md:w-[250px] drop-shadow-[0_10px_25px_rgba(6,182,212,0.4)] opacity-95"
                    >
                        <DenseTeethCogSVG
                            isCyan={true}
                            className="w-full h-auto"
                        />
                    </motion.div>

                    {/* 4. Top-Right Secondary White Gear Intermeshed */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 35,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute top-10 sm:top-14 right-[1%] sm:right-[3%] w-[100px] sm:w-[130px] drop-shadow-lg opacity-80"
                    >
                        <SpokeGearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* ----------------- FLANKING SIDE GEARS ----------------- */}

                    {/* 5. Mid-Left Floating Cyan Gear (Floating & Rotating) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [-10, 10, -10],
                            rotate: 360,
                        }}
                        transition={{
                            y: {
                                duration: 7,
                                repeat: Infinity,
                                repeatType: "mirror",
                                ease: "easeInOut",
                            },
                            rotate: {
                                duration: 48,
                                repeat: Infinity,
                                ease: "linear",
                            },
                        }}
                        className="absolute top-[38%] -left-8 sm:left-[2%] w-[120px] sm:w-[160px] drop-shadow-[0_8px_20px_rgba(6,182,212,0.35)] opacity-80"
                    >
                        <DualStackedGearSVG
                            isCyan={true}
                            className="w-full h-auto"
                        />
                    </motion.div>

                    {/* 6. Mid-Right Small Precision Cog */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{
                            y: [10, -10, 10],
                            rotate: -360,
                        }}
                        transition={{
                            y: {
                                duration: 6.5,
                                repeat: Infinity,
                                repeatType: "mirror",
                                ease: "easeInOut",
                            },
                            rotate: {
                                duration: 38,
                                repeat: Infinity,
                                ease: "linear",
                            },
                        }}
                        className="absolute top-[42%] -right-8 sm:right-[2%] w-[110px] sm:w-[150px] drop-shadow-xl opacity-75"
                    >
                        <DenseTeethCogSVG className="w-full h-auto" />
                    </motion.div>

                    {/* ----------------- BOTTOM GEAR MECHANISM ----------------- */}

                    {/* 7. Bottom-Left Ice-Cyan Precision Cog (Continuous Rotation) */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 42,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -bottom-10 left-[2%] sm:left-[6%] w-[140px] sm:w-[180px] drop-shadow-[0_10px_25px_rgba(6,182,212,0.35)] opacity-85"
                    >
                        <DenseTeethCogSVG
                            isCyan={true}
                            className="w-full h-auto"
                        />
                    </motion.div>

                    {/* 8. Bottom-Left Intermeshing Spoke Gear */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 36,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -bottom-8 left-[16%] sm:left-[19%] w-[110px] sm:w-[140px] drop-shadow-xl opacity-80"
                    >
                        <SpokeGearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 9. Bottom-Center Tiny Companion Cog */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 25,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -bottom-4 left-[34%] w-[70px] sm:w-[90px] drop-shadow-md opacity-70"
                    >
                        <DualStackedGearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 10. Bottom-Right Stacked Large Gear Assembly */}
                    <motion.div
                        animate={{ rotate: -360 }}
                        transition={{
                            duration: 50,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -bottom-14 right-[12%] sm:right-[15%] w-[160px] sm:w-[210px] drop-shadow-2xl opacity-85"
                    >
                        <DualStackedGearSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 11. Bottom-Right Ice-Blue Outer Gear */}
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{
                            duration: 38,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="absolute -bottom-10 right-[2%] sm:right-[4%] w-[130px] sm:w-[170px] drop-shadow-[0_10px_25px_rgba(6,182,212,0.4)] opacity-90"
                    >
                        <DenseTeethCogSVG
                            isCyan={true}
                            className="w-full h-auto"
                        />
                    </motion.div>
                </div>

                {/* ========================================================================= */}
                {/* 2. TERMS & CONDITIONS CONTENT CONTAINER                                  */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6 max-w-4xl">
                    <div className="rounded-3xl border border-slate-200/90 dark:border-white/10 bg-white/92 dark:bg-[#0c1424]/90 backdrop-blur-xl p-8 sm:p-12 md:p-16 shadow-2xl shadow-cyan-950/5 dark:shadow-black/50">
                        {/* Header Section with 3D Terms Pill Badge */}
                        <div className="text-center mb-12 border-b border-slate-200/80 dark:border-white/10 pb-8">
                            <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-3 px-3.5 py-1.5 rounded-full bg-cyan-50 dark:bg-cyan-500/10 border border-cyan-200/70 dark:border-cyan-500/20 shadow-sm">
                                <Scale className="w-4 h-4" />
                                ComLink Legal Agreement & Policies
                            </span>
                            <h1 className="font-display text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                                {terms?.title || "Terms & Conditions"}
                            </h1>
                            {terms?.last_updated && (
                                <p className="text-slate-500 dark:text-slate-400 text-sm flex items-center justify-center gap-2">
                                    <Clock className="w-4 h-4 text-cyan-500" />
                                    <span>Last Updated: {terms.last_updated}</span>
                                </p>
                            )}

                            {/* Trust Pillars Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-white/5 text-left">
                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <FileCheck2 className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Clear Agreements
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Transparent, no hidden clauses
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <ShieldCheck className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Fair Consumer Rights
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Protected warranty & service
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <Lock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Privacy & Security
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Strict data confidentiality
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* HTML Terms Content Render */}
                        {terms?.content && (
                            <div
                                className="prose prose-base sm:prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed prose-headings:font-display prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:uppercase prose-headings:tracking-wide prose-a:text-cyan-600 dark:prose-a:text-cyan-400 prose-a:underline hover:prose-a:text-cyan-700 space-y-6"
                                dangerouslySetInnerHTML={{
                                    __html: terms.content,
                                }}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
