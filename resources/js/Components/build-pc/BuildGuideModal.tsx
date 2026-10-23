import { cn } from "@/utils/cn";
import {
    ArrowRight,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircuitBoard,
    Compass,
    Cpu,
    HelpCircle,
    Layers,
    Lightbulb,
    ShieldCheck,
    ShoppingCart,
    X,
    Zap,
} from "lucide-react";
import React, { useState } from "react";

interface BuildGuideModalProps {
    isOpen: boolean;
    onClose: () => void;
    onStartBuilding: () => void;
    onStartTour?: () => void;
}

interface GuideStep {
    id: number;
    title: string;
    subtitle: string;
    badge: string;
    icon: React.ElementType;
    iconColor: string;
    iconBg: string;
    description: string;
    highlights: string[];
    proTip: string;
}

const GUIDE_STEPS: GuideStep[] = [
    {
        id: 1,
        title: "Start with the Brain: Platform & Processor",
        subtitle: "Step 1 · Foundation of your system",
        badge: "STEP 1 OF 4",
        icon: Cpu,
        iconColor: "text-cyan-400",
        iconBg: "bg-cyan-500/10 border-cyan-500/30",
        description:
            "Every custom PC begins with the processor (CPU). On the left panel, choose your architecture (AMD or Intel), then select your processor. Your CPU dictates which motherboards and RAM generations are compatible.",
        highlights: [
            "Filter by Platform: Select AMD or Intel platform toggle",
            "Automatic Locking: Selecting a CPU automatically filters compatible motherboards",
            "Gaming vs Productivity: Higher clock speeds boost FPS; higher cores benefit streaming and 3D rendering",
        ],
        proTip: "Tip: For AMD Ryzen 5000/7000 or Intel 12th-14th Gen, we will automatically match compatible sockets!",
    },
    {
        id: 2,
        title: "Work Down the Checklist with Auto-Advance",
        subtitle: "Step 2 · Step-by-step part selection",
        badge: "STEP 2 OF 4",
        icon: Layers,
        iconColor: "text-sky-400",
        iconBg: "bg-sky-500/10 border-sky-500/30",
        description:
            "After picking your CPU, follow the Essentials list: Motherboard → Memory (RAM) → Graphics Card (GPU) → Storage (SSD) → Power Supply (PSU) → PC Case. With Auto-Advance enabled, the builder automatically takes you to the next required part after each selection.",
        highlights: [
            "Auto-Advance Enabled: Seamlessly jumps to the next slot when you click '+ ADD TO BUILD'",
            "Switch Tabs Anytime: Jump between 'Essentials' (core PC parts) and 'Accessories' (monitors, keyboards, audio)",
            "Change Anytime: Click on any slot in the left panel to swap out or upgrade components",
        ],
        proTip: "Tip: You can toggle Auto-Advance ON or OFF at the top of the middle screen whenever you want full manual control.",
    },
    {
        id: 3,
        title: "Real-Time Compatibility & Wattage Guard",
        subtitle: "Step 3 · Automatic hardware protection",
        badge: "STEP 3 OF 4",
        icon: ShieldCheck,
        iconColor: "text-emerald-400",
        iconBg: "bg-emerald-500/10 border-emerald-500/30",
        description:
            "Never worry about incompatible parts! Our built-in intelligence engine checks CPU sockets, RAM generations (DDR4 vs DDR5), and calculates total system power draw as you pick components.",
        highlights: [
            "Live Wattage Calculation: We sum component power draws so you never buy an underpowered PSU",
            "Socket Pin Protection: Warns immediately if a motherboard doesn't match your processor",
            "Headroom Recommendation: Always ensure your Power Supply has at least 20% headroom over total draw",
        ],
        proTip: "Tip: If the status says 'Compatible' in green, you're 100% good to assemble!",
    },
    {
        id: 4,
        title: "Review Summary & One-Click Checkout",
        subtitle: "Step 4 · Finalize your dream rig",
        badge: "STEP 4 OF 4",
        icon: ShoppingCart,
        iconColor: "text-amber-400",
        iconBg: "bg-amber-500/10 border-amber-500/30",
        description:
            "The right-hand panel displays your live Build Summary, complete part breakdown, and total cost in Sri Lankan Rupees (LKR). When you're happy, add everything to your cart in a single click!",
        highlights: [
            "Add To Cart: Adds all chosen components to your global cart in 1 click",
            "Save & Share: Save your build to your browser or copy a shareable specs link for friends",
            "Export Specs: Download or copy a formatted technical spec sheet with one click",
        ],
        proTip: "Tip: All custom PC orders are carefully assembled, cable-managed, and stress-tested before dispatch!",
    },
];

export function BuildGuideModal({
    isOpen,
    onClose,
    onStartBuilding,
    onStartTour,
}: BuildGuideModalProps) {
    const [currentStep, setCurrentStep] = useState(0);

    if (!isOpen) return null;

    const step = GUIDE_STEPS[currentStep];
    const isFirst = currentStep === 0;
    const isLast = currentStep === GUIDE_STEPS.length - 1;

    const handleNext = () => {
        if (isLast) {
            onStartBuilding();
        } else {
            setCurrentStep((prev) => prev + 1);
        }
    };

    const handlePrev = () => {
        if (!isFirst) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-md animate-fade-in">
            {/* Modal Box */}
            <div className="relative w-full max-w-2xl rounded-3xl border border-cyan-500/30 bg-[#0c1322] p-6 sm:p-8 shadow-[0_0_50px_rgba(6,182,212,0.15)] flex flex-col justify-between overflow-hidden">
                {/* Ambient Top Glow */}
                <div className="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-48 bg-cyan-500/15 blur-3xl rounded-full" />

                {/* Top Header Bar */}
                <div className="flex items-center justify-between pb-4 border-b border-border/60 relative z-10">
                    <div className="flex items-center gap-2.5">
                        <span className="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                            <Lightbulb className="w-4 h-4" />
                        </span>
                        <div>
                            <span className="text-[10px] font-mono font-bold tracking-widest text-cyan-400 uppercase">
                                Beginner & Builder Guide
                            </span>
                            <h3 className="font-display text-lg sm:text-xl font-bold text-white leading-tight">
                                How to Build Your PC
                            </h3>
                        </div>
                    </div>

                    {/* Action buttons */}
                    <div className="flex items-center gap-2">
                        {onStartTour && (
                            <button
                                type="button"
                                onClick={onStartTour}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-cyan-500/15 border border-cyan-500/40 text-cyan-400 hover:bg-cyan-500/25 text-xs font-bold transition-all cursor-pointer"
                                title="Switch to interactive spotlight walkthrough"
                            >
                                <Compass className="w-3.5 h-3.5 text-cyan-400" />
                                <span className="hidden sm:inline">Spotlight Tour</span>
                            </button>
                        )}
                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-border/80 text-slate-400 hover:text-white hover:border-cyan-500/40 hover:bg-white/5 transition-all text-xs font-semibold cursor-pointer"
                            title="Skip tutorial and start building"
                        >
                            <span>Skip Guide</span>
                            <X className="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>

                {/* Step Progress Indicators (1, 2, 3, 4) */}
                <div className="flex items-center gap-2 my-5 relative z-10">
                    {GUIDE_STEPS.map((s, idx) => (
                        <button
                            key={s.id}
                            type="button"
                            onClick={() => setCurrentStep(idx)}
                            className={cn(
                                "flex-1 h-1.5 rounded-full transition-all duration-300 cursor-pointer",
                                idx === currentStep
                                    ? "bg-gradient-to-r from-cyan-500 to-sky-400 shadow-[0_0_10px_rgba(6,182,212,0.8)]"
                                    : idx < currentStep
                                      ? "bg-cyan-500/40"
                                      : "bg-slate-800",
                            )}
                            aria-label={`Go to step ${idx + 1}`}
                        />
                    ))}
                </div>

                {/* Active Slide Content */}
                <div className="relative z-10 py-2 min-h-[280px] flex flex-col justify-between">
                    <div>
                        {/* Step Badge & Icon */}
                        <div className="flex items-center gap-3 mb-3">
                            <div
                                className={cn(
                                    "w-12 h-12 rounded-2xl border flex items-center justify-center shrink-0 shadow-sm",
                                    step.iconBg,
                                )}
                            >
                                <step.icon
                                    className={cn("w-6 h-6", step.iconColor)}
                                />
                            </div>
                            <div>
                                <span className="inline-block px-2 py-0.5 rounded-full bg-cyan-950/70 border border-cyan-500/30 text-[10px] font-mono font-bold tracking-wider text-cyan-300">
                                    {step.badge}
                                </span>
                                <h4 className="font-display text-lg sm:text-xl font-bold text-white mt-0.5">
                                    {step.title}
                                </h4>
                            </div>
                        </div>

                        {/* Description */}
                        <p className="text-sm text-slate-300 leading-relaxed mt-2">
                            {step.description}
                        </p>

                        {/* Checklist Highlights */}
                        <div className="mt-4 space-y-2 bg-slate-900/60 rounded-xl p-3.5 border border-border/50">
                            {step.highlights.map((item, i) => (
                                <div
                                    key={i}
                                    className="flex items-start gap-2.5 text-xs text-slate-200"
                                >
                                    <Check className="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" />
                                    <span>{item}</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Pro Tip Box */}
                    <div className="mt-4 flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-cyan-950/30 border border-cyan-500/20 text-xs text-cyan-300/90">
                        <Lightbulb className="w-4 h-4 text-cyan-400 shrink-0" />
                        <span className="leading-snug">{step.proTip}</span>
                    </div>
                </div>

                {/* Bottom Footer Controls */}
                <div className="mt-6 pt-4 border-t border-border/60 flex items-center justify-between relative z-10">
                    <button
                        type="button"
                        onClick={handlePrev}
                        disabled={isFirst}
                        className={cn(
                            "inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all",
                            isFirst
                                ? "border-transparent text-slate-600 cursor-not-allowed opacity-40"
                                : "border-border/80 text-slate-300 hover:text-white hover:bg-white/5 cursor-pointer",
                        )}
                    >
                        <ChevronLeft className="w-4 h-4" />
                        <span>Previous</span>
                    </button>

                    <span className="text-xs font-mono text-slate-400">
                        Step {currentStep + 1} of {GUIDE_STEPS.length}
                    </span>

                    <button
                        type="button"
                        onClick={handleNext}
                        className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 hover:from-cyan-400 hover:to-sky-300 text-slate-950 text-xs font-black uppercase tracking-wider shadow-[0_0_20px_rgba(6,182,212,0.35)] hover:shadow-[0_0_30px_rgba(6,182,212,0.6)] transition-all cursor-pointer active:scale-95"
                    >
                        <span>{isLast ? "Start Building PC" : "Next Step"}</span>
                        {isLast ? (
                            <CheckCircle2 className="w-4 h-4" />
                        ) : (
                            <ChevronRight className="w-4 h-4" />
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default BuildGuideModal;
