import { cn } from "@/utils/cn";
import {
    ArrowLeft,
    ArrowRight,
    Check,
    CheckCircle2,
    CircuitBoard,
    Cpu,
    HelpCircle,
    Monitor,
    ShieldCheck,
    X,
    Zap,
} from "lucide-react";
import React, { useCallback, useEffect, useRef, useState } from "react";

export interface TourStep {
    id: string;
    targetId: string;
    title: string;
    stepNumber: number;
    badge: string;
    description: string;
    tip: string;
    preferredPlacement?: "top" | "bottom" | "left" | "right";
    icon: React.ElementType;
}

interface BuildWalkthroughTourProps {
    isOpen: boolean;
    onClose: () => void;
    onComplete: () => void;
}

const TOUR_STEPS: TourStep[] = [
    {
        id: "step-components",
        targetId: "tour-step-components",
        stepNumber: 1,
        badge: "STEP 1 OF 4",
        title: "Select Platform & Component Slot",
        description:
            "Start your build here! Filter by AMD or Intel platform, then click on any component slot (like Processors or Motherboards) to view available hardware.",
        tip: "Pro Tip: Always start with the CPU (Processor) to automatically match compatible motherboards and RAM.",
        preferredPlacement: "right",
        icon: Cpu,
    },
    {
        id: "step-products",
        targetId: "tour-step-products",
        stepNumber: 2,
        badge: "STEP 2 OF 4",
        title: "Choose Parts & Click '+ Add to Build'",
        description:
            "Browse through processors, motherboards, GPUs, and memory. Check prices, specs, and in-stock badges, then click '+ Add to Build' to attach a component.",
        tip: "Pro Tip: With Auto-Advance turned on, the system automatically opens the next component category for you!",
        preferredPlacement: "top",
        icon: CircuitBoard,
    },
    {
        id: "step-metrics",
        targetId: "tour-step-metrics",
        stepNumber: 3,
        badge: "STEP 3 OF 4",
        title: "Live Compatibility & Power Draw Guard",
        description:
            "This top bar monitors your build in real time. It calculates total wattage draw, recommends safe PSU wattage headroom, and checks socket and memory compatibility.",
        tip: "Pro Tip: If anything is incompatible, this status indicator turns red and warns you immediately before buying.",
        preferredPlacement: "bottom",
        icon: Zap,
    },
    {
        id: "step-summary",
        targetId: "tour-step-summary",
        stepNumber: 4,
        badge: "STEP 4 OF 4",
        title: "Review Build & 1-Click Checkout",
        description:
            "View your complete configuration and running total price. Once your dream build is ready, click 'Add to Cart' to order everything together, or save and share your build link!",
        tip: "Pro Tip: You can download a text configuration spec sheet to bring into the physical store.",
        preferredPlacement: "left",
        icon: Monitor,
    },
];

export function BuildWalkthroughTour({
    isOpen,
    onClose,
    onComplete,
}: BuildWalkthroughTourProps) {
    const [currentStepIndex, setCurrentStepIndex] = useState(0);
    const [targetRect, setTargetRect] = useState<DOMRect | null>(null);
    const [popoverPos, setPopoverPos] = useState<{
        top: number;
        left: number;
        placement: "top" | "bottom" | "left" | "right";
        arrowOffset: number;
    } | null>(null);

    const popoverRef = useRef<HTMLDivElement>(null);

    const currentStep = TOUR_STEPS[currentStepIndex];

    // Function to calculate element rect and popover position
    const updatePosition = useCallback(() => {
        if (!isOpen || !currentStep) return;

        const targetEl = document.getElementById(currentStep.targetId);
        if (!targetEl) return;

        const rect = targetEl.getBoundingClientRect();
        setTargetRect(rect);

        const popoverEl = popoverRef.current;
        const popoverWidth = popoverEl?.offsetWidth || 400;
        const popoverHeight = popoverEl?.offsetHeight || 270;
        const margin = 20;

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        let placement = currentStep.preferredPlacement || "bottom";

        // For mobile screens (< 768px), default to bottom or top
        if (viewportWidth < 768) {
            if (rect.bottom + popoverHeight + margin < viewportHeight) {
                placement = "bottom";
            } else {
                placement = "top";
            }
        }

        let top = 0;
        let left = 0;

        // Ideal vertical anchor: middle of element or first 350px if tall
        const visibleHeight = Math.min(rect.height, Math.max(300, viewportHeight * 0.5));
        const anchorY = rect.top + visibleHeight / 2;

        if (placement === "right") {
            left = rect.right + margin;
            top = anchorY - popoverHeight / 2;

            // If overflows right, flip to left or bottom
            if (left + popoverWidth > viewportWidth - margin) {
                if (rect.left - popoverWidth - margin > margin) {
                    placement = "left";
                    left = rect.left - popoverWidth - margin;
                } else {
                    placement = "bottom";
                    top = rect.bottom + margin;
                    left = Math.max(
                        margin,
                        Math.min(
                            rect.left + rect.width / 2 - popoverWidth / 2,
                            viewportWidth - popoverWidth - margin,
                        ),
                    );
                }
            }
        } else if (placement === "left") {
            left = rect.left - popoverWidth - margin;
            top = anchorY - popoverHeight / 2;

            // If overflows left, flip to right or bottom
            if (left < margin) {
                if (rect.right + popoverWidth + margin < viewportWidth - margin) {
                    placement = "right";
                    left = rect.right + margin;
                } else {
                    placement = "bottom";
                    top = rect.bottom + margin;
                    left = Math.max(
                        margin,
                        Math.min(
                            rect.left + rect.width / 2 - popoverWidth / 2,
                            viewportWidth - popoverWidth - margin,
                        ),
                    );
                }
            }
        } else if (placement === "bottom") {
            top = rect.bottom + margin;
            left = rect.left + rect.width / 2 - popoverWidth / 2;

            // If overflows bottom, flip to top
            if (top + popoverHeight > viewportHeight - margin) {
                if (rect.top - popoverHeight - margin > margin) {
                    placement = "top";
                    top = rect.top - popoverHeight - margin;
                }
            }
        } else if (placement === "top") {
            top = rect.top - popoverHeight - margin;
            left = rect.left + rect.width / 2 - popoverWidth / 2;

            // If overflows top, flip to bottom
            if (top < margin) {
                if (rect.bottom + popoverHeight + margin < viewportHeight - margin) {
                    placement = "bottom";
                    top = rect.bottom + margin;
                }
            }
        }

        // Clamp popover within viewport
        top = Math.max(margin + 70, Math.min(top, viewportHeight - popoverHeight - margin));
        left = Math.max(margin, Math.min(left, viewportWidth - popoverWidth - margin));

        // Calculate dynamic pointing arrow offset
        let arrowOffset = 0;
        if (placement === "left" || placement === "right") {
            arrowOffset = Math.max(
                28,
                Math.min(anchorY - top, popoverHeight - 28),
            );
        } else {
            const targetCenterX = rect.left + rect.width / 2;
            arrowOffset = Math.max(
                28,
                Math.min(targetCenterX - left, popoverWidth - 28),
            );
        }

        setPopoverPos({ top, left, placement, arrowOffset });
    }, [isOpen, currentStep]);

    // Scroll target into view with navbar padding and update rect on step change
    useEffect(() => {
        if (!isOpen || !currentStep) return;

        const targetEl = document.getElementById(currentStep.targetId);
        if (targetEl) {
            const navbarOffset = 130;
            const elementPosition =
                targetEl.getBoundingClientRect().top + window.scrollY;
            const targetScrollTop = Math.max(0, elementPosition - navbarOffset);

            window.scrollTo({
                top: targetScrollTop,
                behavior: "smooth",
            });
        }

        const timer = setTimeout(() => {
            updatePosition();
        }, 350);

        return () => clearTimeout(timer);
    }, [isOpen, currentStepIndex, currentStep, updatePosition]);

    // Handle resize & scroll
    useEffect(() => {
        if (!isOpen) return;

        const handleScrollOrResize = () => {
            updatePosition();
        };

        window.addEventListener("resize", handleScrollOrResize);
        window.addEventListener("scroll", handleScrollOrResize, true);

        return () => {
            window.removeEventListener("resize", handleScrollOrResize);
            window.removeEventListener("scroll", handleScrollOrResize, true);
        };
    }, [isOpen, updatePosition]);

    // Keyboard navigation (Escape to skip, Right arrow for next, Left arrow for back)
    useEffect(() => {
        if (!isOpen) return;

        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === "Escape") {
                onClose();
            } else if (e.key === "ArrowRight") {
                if (currentStepIndex < TOUR_STEPS.length - 1) {
                    setCurrentStepIndex((prev) => prev + 1);
                } else {
                    onComplete();
                }
            } else if (e.key === "ArrowLeft") {
                if (currentStepIndex > 0) {
                    setCurrentStepIndex((prev) => prev - 1);
                }
            }
        };

        window.addEventListener("keydown", handleKeyDown);
        return () => window.removeEventListener("keydown", handleKeyDown);
    }, [isOpen, currentStepIndex, onClose, onComplete]);

    if (!isOpen || !currentStep) return null;

    const isFirstStep = currentStepIndex === 0;
    const isLastStep = currentStepIndex === TOUR_STEPS.length - 1;
    const StepIcon = currentStep.icon;

    const handleNext = () => {
        if (isLastStep) {
            onComplete();
        } else {
            setCurrentStepIndex((prev) => prev + 1);
        }
    };

    const handlePrev = () => {
        if (!isFirstStep) {
            setCurrentStepIndex((prev) => prev - 1);
        }
    };

    return (
        <div className="fixed inset-0 z-[100] pointer-events-auto">
            {/* SVG Dimmed Backdrop with Spotlight Cutout */}
            <svg
                className="fixed inset-0 w-full h-full pointer-events-none transition-all duration-300"
                style={{ zIndex: 101 }}
            >
                <defs>
                    <mask id="spotlight-tour-mask">
                        {/* White fills everything (opaque mask) */}
                        <rect x="0" y="0" width="100%" height="100%" fill="white" />
                        {/* Black cut out reveals the target element */}
                        {targetRect && (
                            <rect
                                x={Math.max(0, targetRect.left - 8)}
                                y={Math.max(0, targetRect.top - 8)}
                                width={targetRect.width + 16}
                                height={targetRect.height + 16}
                                rx="16"
                                fill="black"
                            />
                        )}
                    </mask>
                </defs>

                {/* Dark overlay backdrop */}
                <rect
                    x="0"
                    y="0"
                    width="100%"
                    height="100%"
                    fill="rgba(5, 10, 20, 0.78)"
                    mask="url(#spotlight-tour-mask)"
                />

                {/* Animated Glowing Spotlight Ring around target */}
                {targetRect && (
                    <rect
                        x={Math.max(0, targetRect.left - 8)}
                        y={Math.max(0, targetRect.top - 8)}
                        width={targetRect.width + 16}
                        height={targetRect.height + 16}
                        rx="16"
                        fill="none"
                        stroke="#06b6d4"
                        strokeWidth="2.5"
                        strokeDasharray="8 4"
                        className="animate-pulse"
                        style={{
                            filter: "drop-shadow(0 0 12px rgba(6,182,212,0.8))",
                        }}
                    />
                )}
            </svg>

            {/* Click outside to skip option or invisible backdrop guard */}
            <div
                className="fixed inset-0"
                style={{ zIndex: 102 }}
                onClick={(e) => {
                    // Prevent closing unless clicked directly on background
                    if (e.target === e.currentTarget) {
                        onClose();
                    }
                }}
            />

            {/* Floating Popover Card with Pointing Arrow */}
            {popoverPos && (
                <div
                    ref={popoverRef}
                    style={{
                        position: "fixed",
                        top: popoverPos.top,
                        left: popoverPos.left,
                        zIndex: 103,
                    }}
                    className="w-[90vw] max-w-[420px] rounded-3xl border border-cyan-500/40 bg-gradient-to-b from-slate-900 via-[#0b1728] to-slate-950 p-6 shadow-[0_20px_60px_rgba(0,0,0,0.8),0_0_30px_rgba(6,182,212,0.25)] text-white animate-fade-in relative transition-all duration-300"
                >
                    {/* Pointing Arrow Indicator */}
                    {popoverPos.placement === "right" && (
                        <div
                            style={{ top: popoverPos.arrowOffset }}
                            className="absolute -left-3 -translate-y-1/2 w-0 h-0 border-y-[10px] border-y-transparent border-r-[12px] border-r-cyan-500/90 drop-shadow-[0_0_10px_rgba(6,182,212,0.9)]"
                        />
                    )}
                    {popoverPos.placement === "left" && (
                        <div
                            style={{ top: popoverPos.arrowOffset }}
                            className="absolute -right-3 -translate-y-1/2 w-0 h-0 border-y-[10px] border-y-transparent border-l-[12px] border-l-cyan-500/90 drop-shadow-[0_0_10px_rgba(6,182,212,0.9)]"
                        />
                    )}
                    {popoverPos.placement === "bottom" && (
                        <div
                            style={{ left: popoverPos.arrowOffset }}
                            className="absolute -top-3 -translate-x-1/2 w-0 h-0 border-x-[10px] border-x-transparent border-b-[12px] border-b-cyan-500/90 drop-shadow-[0_0_10px_rgba(6,182,212,0.9)]"
                        />
                    )}
                    {popoverPos.placement === "top" && (
                        <div
                            style={{ left: popoverPos.arrowOffset }}
                            className="absolute -bottom-3 -translate-x-1/2 w-0 h-0 border-x-[10px] border-x-transparent border-t-[12px] border-t-cyan-500/90 drop-shadow-[0_0_10px_rgba(6,182,212,0.9)]"
                        />
                    )}

                    {/* Header: Step Badge & Skip Button */}
                    <div className="flex items-center justify-between pb-3 border-b border-white/10">
                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full bg-cyan-500/15 border border-cyan-500/40 text-[10px] font-mono font-bold tracking-wider text-cyan-400 uppercase">
                                {currentStep.badge}
                            </span>
                        </div>

                        <button
                            type="button"
                            onClick={onClose}
                            className="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 hover:text-white px-2 py-1 rounded-lg hover:bg-white/5 transition-colors cursor-pointer"
                            title="Skip this walkthrough tour"
                        >
                            <span>Skip Tour</span>
                            <X className="w-3.5 h-3.5" />
                        </button>
                    </div>

                    {/* Step Content */}
                    <div className="py-4">
                        <div className="flex items-start gap-3.5">
                            <div className="h-10 w-10 rounded-2xl bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center shrink-0 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.3)] mt-0.5">
                                <StepIcon className="w-5 h-5" />
                            </div>
                            <div className="min-w-0">
                                <h3 className="font-display text-base sm:text-lg font-bold text-white tracking-wide">
                                    {currentStep.title}
                                </h3>
                                <p className="text-xs text-slate-300 mt-1.5 leading-relaxed">
                                    {currentStep.description}
                                </p>
                            </div>
                        </div>

                        {/* Pro-Tip Box */}
                        <div className="mt-4 rounded-xl border border-cyan-500/20 bg-cyan-950/40 p-3 flex items-start gap-2.5">
                            <Zap className="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" />
                            <p className="text-[11px] text-cyan-200/90 leading-snug">
                                {currentStep.tip}
                            </p>
                        </div>
                    </div>

                    {/* Footer: Progress Dots + Navigation Buttons */}
                    <div className="flex items-center justify-between pt-3 border-t border-white/10 mt-1">
                        {/* Step indicator dots */}
                        <div className="flex items-center gap-1.5">
                            {TOUR_STEPS.map((step, idx) => (
                                <button
                                    key={step.id}
                                    type="button"
                                    onClick={() => setCurrentStepIndex(idx)}
                                    className={cn(
                                        "h-2 rounded-full transition-all cursor-pointer",
                                        idx === currentStepIndex
                                            ? "w-6 bg-cyan-400 shadow-[0_0_8px_rgba(6,182,212,0.8)]"
                                            : "w-2 bg-slate-700 hover:bg-slate-500",
                                    )}
                                    title={`Go to step ${idx + 1}`}
                                />
                            ))}
                        </div>

                        {/* Prev / Next controls */}
                        <div className="flex items-center gap-2">
                            {!isFirstStep && (
                                <button
                                    type="button"
                                    onClick={handlePrev}
                                    className="px-3 py-1.5 rounded-xl border border-white/10 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-colors cursor-pointer"
                                >
                                    Back
                                </button>
                            )}

                            <button
                                type="button"
                                onClick={handleNext}
                                className={cn(
                                    "inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer",
                                    isLastStep
                                        ? "bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-extrabold shadow-[0_0_20px_rgba(16,185,129,0.4)]"
                                        : "bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 text-white shadow-[0_0_20px_rgba(6,182,212,0.4)]",
                                )}
                            >
                                <span>
                                    {isLastStep ? "Start Building!" : "Next"}
                                </span>
                                {isLastStep ? (
                                    <Check className="w-3.5 h-3.5" />
                                ) : (
                                    <ArrowRight className="w-3.5 h-3.5" />
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
