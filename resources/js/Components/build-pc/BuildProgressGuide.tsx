import { cn } from "@/utils/cn";
import {
    ArrowRight,
    CheckCircle2,
    CircuitBoard,
    Compass,
    Cpu,
    Eye,
    EyeOff,
    HardDrive,
    HelpCircle,
    Layers,
    Lightbulb,
    Monitor,
    ShieldCheck,
    Wind,
    Zap,
} from "lucide-react";
import React, { useMemo, useState } from "react";

interface BuildProgressGuideProps {
    selectedParts: Record<string, any>;
    activeSlotId: string;
    onSelectSlot: (slotId: string) => void;
    onOpenFullGuide: () => void;
    onOpenTour?: () => void;
    estimatedPowerDraw: number;
    recommendedPsuWattage: number;
}

interface StepInstruction {
    stepNumber: number;
    targetSlotId: string;
    title: string;
    description: string;
    actionLabel: string;
    proTip: string;
    icon: React.ElementType;
    iconColor: string;
}

export function BuildProgressGuide({
    selectedParts,
    activeSlotId,
    onSelectSlot,
    onOpenFullGuide,
    onOpenTour,
    estimatedPowerDraw,
    recommendedPsuWattage,
}: BuildProgressGuideProps) {
    // Allows user to minimize/dismiss the in-page guide if they already know what to do
    const [isCollapsed, setIsCollapsed] = useState(() => {
        try {
            return (
                localStorage.getItem("comlink_builder_tips_collapsed") === "true"
            );
        } catch {
            return false;
        }
    });

    const toggleCollapsed = () => {
        setIsCollapsed((prev) => {
            const next = !prev;
            try {
                localStorage.setItem(
                    "comlink_builder_tips_collapsed",
                    String(next),
                );
            } catch {}
            return next;
        });
    };

    // Calculate core essential components count
    const coreSlots = [
        "processors",
        "motherboards",
        "memory",
        "graphic-cards",
        "ssd",
        "power-supply",
        "pc-cases",
    ];

    const completedCoreCount = coreSlots.filter(
        (id) => Boolean(selectedParts[id]),
    ).length;
    const progressPercent = Math.round(
        (completedCoreCount / coreSlots.length) * 100,
    );

    // Determine the next logical recommendation
    const nextStepInstruction = useMemo<StepInstruction>(() => {
        if (!selectedParts["processors"]) {
            return {
                stepNumber: 1,
                targetSlotId: "processors",
                title: "Step 1: Choose your Processor (CPU)",
                description:
                    "Select AMD or Intel platform above to filter compatible processors for your build.",
                actionLabel: "Pick Processor",
                proTip: "The CPU is the brain of your PC and determines socket compatibility.",
                icon: Cpu,
                iconColor: "text-cyan-400",
            };
        }

        if (!selectedParts["motherboards"]) {
            const cpu = selectedParts["processors"];
            return {
                stepNumber: 2,
                targetSlotId: "motherboards",
                title: "Step 2: Choose a Compatible Motherboard",
                description: `Pick a board that supports ${cpu.platform || "your CPU"} (${cpu.socket || "matching socket"}). We automatically filter compatible boards!`,
                actionLabel: "Pick Motherboard",
                proTip: "Look for features like built-in Wi-Fi and multiple M.2 NVMe slots.",
                icon: CircuitBoard,
                iconColor: "text-sky-400",
            };
        }

        if (!selectedParts["memory"]) {
            const mobo = selectedParts["motherboards"];
            const gen = mobo?.memoryGen || "DDR4 / DDR5";
            return {
                stepNumber: 3,
                targetSlotId: "memory",
                title: "Step 3: Add High-Speed Memory (RAM)",
                description: `Choose ${gen} RAM to match your motherboard. 16GB is great for gaming, 32GB+ for editing & multitasking.`,
                actionLabel: "Pick Memory",
                proTip: "Dual-channel memory (2 sticks) delivers noticeably higher gaming frame rates.",
                icon: Layers,
                iconColor: "text-indigo-400",
            };
        }

        if (!selectedParts["graphic-cards"]) {
            return {
                stepNumber: 4,
                targetSlotId: "graphic-cards",
                title: "Step 4: Select your Graphics Card (GPU)",
                description:
                    "Choose an NVIDIA GeForce or AMD Radeon GPU based on your gaming resolution (1080p, 1440p, or 4K).",
                actionLabel: "Pick Graphics Card",
                proTip: "Higher VRAM (8GB-16GB+) helps with high-res textures and modern games.",
                icon: Zap,
                iconColor: "text-amber-400",
            };
        }

        if (!selectedParts["ssd"]) {
            return {
                stepNumber: 5,
                targetSlotId: "ssd",
                title: "Step 5: Pick Fast Storage (NVMe M.2 SSD)",
                description:
                    "Select an ultra-fast PCIe NVMe SSD for instant Windows boot times and snappy game launches.",
                actionLabel: "Pick Fast Storage",
                proTip: "1TB NVMe M.2 is recommended as your primary boot drive.",
                icon: HardDrive,
                iconColor: "text-emerald-400",
            };
        }

        if (!selectedParts["power-supply"]) {
            return {
                stepNumber: 6,
                targetSlotId: "power-supply",
                title: "Step 6: Choose a Power Supply (PSU)",
                description: `Your system currently draws ~${estimatedPowerDraw}W. We recommend at least a ${recommendedPsuWattage}W 80+ rated PSU for stable headroom.`,
                actionLabel: "Pick Power Supply",
                proTip: "Never cheap out on a power supply; 80+ Bronze/Gold ensures reliable voltage.",
                icon: Zap,
                iconColor: "text-orange-400",
            };
        }

        if (!selectedParts["pc-cases"]) {
            return {
                stepNumber: 7,
                targetSlotId: "pc-cases",
                title: "Step 7: Select your PC Case (Chassis)",
                description:
                    "Choose a chassis that accommodates your motherboard form factor and has excellent airflow ventilation.",
                actionLabel: "Pick PC Case",
                proTip: "Mesh front panels provide superior intake airflow to keep your hardware cool.",
                icon: Wind,
                iconColor: "text-teal-400",
            };
        }

        // All core slots chosen
        return {
            stepNumber: 8,
            targetSlotId: "cart",
            title: "🎉 All Core Components Selected!",
            description:
                "Your core system is fully equipped and verified compatible. Add optional accessories or add the entire rig to your cart!",
            actionLabel: "Ready to Review",
            proTip: "Check your Build Summary on the right to review warranty and checkout.",
            icon: CheckCircle2,
            iconColor: "text-emerald-400",
        };
    }, [selectedParts, estimatedPowerDraw, recommendedPsuWattage]);

    const isCurrentTargetActive = activeSlotId === nextStepInstruction.targetSlotId;
    const IconComponent = nextStepInstruction.icon;

    // Collapsed State: Sleek compact helper pill
    if (isCollapsed) {
        return (
            <div className="flex items-center justify-between px-4 py-2 rounded-xl bg-slate-900/60 border border-cyan-500/20 text-xs text-slate-300 shadow-sm">
                <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-cyan-400 animate-pulse" />
                    <span className="font-semibold text-white">
                        Next: {nextStepInstruction.title.split(":")[1]?.trim() || nextStepInstruction.title}
                    </span>
                    <span className="text-[10px] text-cyan-400 font-mono">
                        ({completedCoreCount}/7 parts)
                    </span>
                </div>

                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={toggleCollapsed}
                        className="inline-flex items-center gap-1 text-[11px] text-cyan-400 hover:text-cyan-300 font-bold cursor-pointer"
                    >
                        <Eye className="w-3.5 h-3.5" />
                        <span>Show Guide</span>
                    </button>
                    <span className="text-slate-600">|</span>
                    <button
                        type="button"
                        onClick={onOpenFullGuide}
                        className="text-[11px] text-slate-400 hover:text-white font-medium cursor-pointer"
                    >
                        Full Tutorial
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-cyan-500/30 bg-gradient-to-r from-[#0c182a] via-[#0c1524] to-[#0d1c2d] p-4 sm:p-5 shadow-[0_0_25px_rgba(6,182,212,0.1)] relative overflow-hidden transition-all">
            {/* Ambient Background Flare */}
            <div className="pointer-events-none absolute -right-12 -top-12 w-48 h-48 bg-cyan-500/10 blur-2xl rounded-full" />

            {/* Top Row: Progress Bar & Dismiss Controls */}
            <div className="flex items-center justify-between mb-3 relative z-10">
                <div className="flex items-center gap-2">
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-[10px] font-mono font-bold tracking-wider text-cyan-400 uppercase">
                        <Compass className="w-3 h-3" />
                        Smart Assistant Guide
                    </span>
                    <span className="text-xs font-mono text-slate-400">
                        {completedCoreCount} of 7 Essentials Selected ({progressPercent}%)
                    </span>
                </div>

                <div className="flex items-center gap-3">
                    {onOpenTour && (
                        <button
                            type="button"
                            onClick={onOpenTour}
                            className="inline-flex items-center gap-1 text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold cursor-pointer transition-colors"
                            title="Start spotlight walkthrough with pointing arrows"
                        >
                            <Compass className="w-3.5 h-3.5 text-cyan-400" />
                            <span className="hidden sm:inline">Interactive Tour</span>
                        </button>
                    )}

                    <button
                        type="button"
                        onClick={onOpenFullGuide}
                        className="inline-flex items-center gap-1 text-[11px] text-slate-300 hover:text-white font-semibold cursor-pointer transition-colors"
                        title="Open interactive 4-step walkthrough"
                    >
                        <HelpCircle className="w-3.5 h-3.5" />
                        <span className="hidden sm:inline">Guide</span>
                    </button>

                    <button
                        type="button"
                        onClick={toggleCollapsed}
                        className="inline-flex items-center gap-1 text-[11px] text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                        title="Minimize guide bar"
                    >
                        <EyeOff className="w-3.5 h-3.5" />
                        <span className="hidden sm:inline">Hide Tips</span>
                    </button>
                </div>
            </div>

            {/* Progress Bar */}
            <div className="w-full h-1.5 rounded-full bg-slate-800/80 overflow-hidden mb-4 relative z-10 border border-white/5">
                <div
                    style={{ width: `${Math.max(5, progressPercent)}%` }}
                    className="h-full bg-gradient-to-r from-cyan-500 via-sky-400 to-emerald-400 transition-all duration-500 shadow-[0_0_10px_rgba(6,182,212,0.6)]"
                />
            </div>

            {/* Main Interactive Step Card */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div className="flex items-start gap-3.5 max-w-xl">
                    <div className="w-10 h-10 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                        <IconComponent className={cn("w-5 h-5", nextStepInstruction.iconColor)} />
                    </div>

                    <div>
                        <h4 className="font-display text-sm sm:text-base font-bold text-white flex items-center gap-2">
                            <span>{nextStepInstruction.title}</span>
                            {isCurrentTargetActive && (
                                <span className="px-1.5 py-0.2 rounded text-[10px] font-mono bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 font-semibold">
                                    CURRENT VIEW
                                </span>
                            )}
                        </h4>
                        <p className="text-xs text-slate-300 mt-1 leading-relaxed">
                            {nextStepInstruction.description}
                        </p>
                        <p className="text-[11px] text-cyan-400/90 mt-1.5 flex items-center gap-1.5 font-medium">
                            <Lightbulb className="w-3.5 h-3.5 shrink-0" />
                            <span>{nextStepInstruction.proTip}</span>
                        </p>
                    </div>
                </div>

                {/* Direct Action Button */}
                {nextStepInstruction.targetSlotId !== "cart" && (
                    <div className="shrink-0 self-end sm:self-center">
                        <button
                            type="button"
                            onClick={() => onSelectSlot(nextStepInstruction.targetSlotId)}
                            className={cn(
                                "inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all cursor-pointer shadow-sm active:scale-95",
                                isCurrentTargetActive
                                    ? "bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-750"
                                    : "bg-cyan-600 hover:bg-cyan-500 text-white shadow-[0_0_20px_rgba(6,182,212,0.3)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)]",
                            )}
                        >
                            <span>{isCurrentTargetActive ? "Browsing Now" : nextStepInstruction.actionLabel}</span>
                            <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

export default BuildProgressGuide;
