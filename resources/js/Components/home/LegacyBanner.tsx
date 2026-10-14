import { cn } from "@/utils/cn";
import { useEffect, useRef, useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Hard-coded content (edit these)                                           */
/* -------------------------------------------------------------------------- */

const ESTABLISHED_YEAR = 2020; // <- replace with your real year
const YEARS_IN_BUSINESS = new Date().getFullYear() - ESTABLISHED_YEAR;

interface Stat {
    value: number;
    suffix: string;
    label: string;
    position: string; // corner placement + text alignment
}

const stats: Stat[] = [
    {
        value: 3,
        suffix: "",
        label: "Branches",
        position: "left-6 top-8 text-left md:left-16 md:top-12",
    },
    {
        value: YEARS_IN_BUSINESS,
        suffix: "",
        label: "Years of legacy",
        position: "right-6 top-8 text-right md:right-16 md:top-12",
    },
    {
        value: 5000,
        suffix: "+",
        label: "Products in stock",
        position: "bottom-8 left-6 text-left md:bottom-12 md:left-16",
    },
    {
        value: 25,
        suffix: "K+",
        label: "Happy customers",
        position: "bottom-8 right-6 text-right md:bottom-12 md:right-16",
    },
];

const tickerItems = [
    "Genuine products",
    "Warranty on every item",
    "Fast delivery",
    "Expert support",
    "Secure payments",
    "Custom PC builds",
];

/* -------------------------------------------------------------------------- */
/*  Count-up hook                                                             */
/* -------------------------------------------------------------------------- */

function useCountUp(target: number, start: boolean, duration = 2000) {
    const [count, setCount] = useState(0);

    useEffect(() => {
        if (!start) return;

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            setCount(target);
            return;
        }

        let frame = 0;
        const startTime = performance.now();

        const tick = (now: number) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            setCount(Math.round(target * eased));
            if (progress < 1) frame = requestAnimationFrame(tick);
        };

        frame = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(frame);
    }, [target, start, duration]);

    return count;
}

function CornerStat({ stat, start }: { stat: Stat; start: boolean }) {
    const count = useCountUp(stat.value, start);

    return (
        <div className={cn("absolute z-20", stat.position)}>
            <p className="font-display text-3xl font-bold leading-none text-white sm:text-5xl md:text-6xl">
                {count.toLocaleString()}
                {stat.suffix}
            </p>
            <p className="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-accent-300 sm:text-xs">
                {stat.label}
            </p>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  LegacyBanner                                                              */
/* -------------------------------------------------------------------------- */

export default function LegacyBanner() {
    const sectionRef = useRef<HTMLElement>(null);
    const [inView, setInView] = useState(false);

    useEffect(() => {
        const el = sectionRef.current;
        if (!el) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setInView(true);
                    observer.disconnect();
                }
            },
            { threshold: 0.3 },
        );
        observer.observe(el);
        return () => observer.disconnect();
    }, []);

    const yearClasses =
        "whitespace-nowrap font-display font-bold leading-none tracking-[-0.06em] text-[7.5rem] sm:text-[13rem] lg:text-[21rem]";

    return (
        <section
            ref={sectionRef}
            className="relative overflow-hidden bg-linear-to-b from-primary-900 to-primary-950"
        >
            <style>{`
                .legacy-ghost-left  { transform: translateX(-62%); animation: legacy-drift-left 14s ease-in-out infinite; }
                .legacy-ghost-right { transform: translateX(62%);  animation: legacy-drift-right 14s ease-in-out infinite; }
                @keyframes legacy-drift-left {
                    0%, 100% { transform: translateX(-62%); }
                    50%      { transform: translateX(-68%); }
                }
                @keyframes legacy-drift-right {
                    0%, 100% { transform: translateX(62%); }
                    50%      { transform: translateX(68%); }
                }
                @media (prefers-reduced-motion: reduce) {
                    .legacy-ghost-left, .legacy-ghost-right { animation: none; }
                }
            `}</style>

            {/* Main stage */}
            <div className="relative flex min-h-[460px] items-center justify-center sm:min-h-[600px] lg:min-h-[680px]">
                {/* Soft tech grid */}
                <div
                    aria-hidden="true"
                    className="absolute inset-0"
                    style={{
                        backgroundImage:
                            "linear-gradient(rgba(34,211,238,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.06) 1px, transparent 1px)",
                        backgroundSize: "48px 48px",
                        maskImage:
                            "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                        WebkitMaskImage:
                            "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                    }}
                />

                {/* Cyan glow behind the year */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute left-1/2 top-1/2 h-[380px] w-[760px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-30 blur-3xl"
                    style={{
                        background:
                            "radial-gradient(circle, rgba(6,182,212,0.7) 0%, transparent 70%)",
                    }}
                />

                {/* Ghost year: left (cyan tint) */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <span
                        className={cn(
                            yearClasses,
                            "legacy-ghost-left select-none text-accent-500/25",
                        )}
                    >
                        {ESTABLISHED_YEAR}
                    </span>
                </div>

                {/* Ghost year: right (navy tint) */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <span
                        className={cn(
                            yearClasses,
                            "legacy-ghost-right select-none text-primary-500/35",
                        )}
                    >
                        {ESTABLISHED_YEAR}
                    </span>
                </div>

                {/* Main year + "since" */}
                <div className="relative z-10">
                    <span className="absolute left-0 top-1/2 z-20 -translate-x-[38%] -translate-y-1/2 -rotate-3 font-serif text-4xl italic text-accent-300 drop-shadow-[0_2px_12px_rgba(6,182,212,0.6)] sm:text-7xl lg:text-8xl">
                        since
                    </span>
                    <h2
                        className={cn(
                            yearClasses,
                            "select-none bg-linear-to-b from-white via-white to-accent-200 bg-clip-text text-transparent",
                        )}
                        style={{
                            filter: "drop-shadow(0 8px 40px rgba(34,211,238,0.35))",
                        }}
                    >
                        {ESTABLISHED_YEAR}
                    </h2>
                </div>

                {/* Corner stats */}
                {stats.map((stat) => (
                    <CornerStat key={stat.label} stat={stat} start={inView} />
                ))}
            </div>

            {/* Trust ticker strip */}
            <div className="relative overflow-hidden border-t border-white/10 bg-primary-950/80 py-3">
                <div className="flex w-max animate-marquee items-center gap-10 whitespace-nowrap">
                    {[...tickerItems, ...tickerItems, ...tickerItems, ...tickerItems].map(
                        (item, i) => (
                            <span
                                key={i}
                                className="flex items-center gap-10 text-xs font-semibold uppercase tracking-[0.25em] text-white/70"
                            >
                                {item}
                                <span className="h-1.5 w-1.5 rounded-full bg-accent-400" />
                            </span>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}