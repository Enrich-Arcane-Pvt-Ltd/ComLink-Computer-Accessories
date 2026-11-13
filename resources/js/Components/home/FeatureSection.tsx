import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import {
    ArrowRight,
    BadgeCheck,
    Headphones,
    ShieldCheck,
    Truck,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Hard-coded content                                                        */
/* -------------------------------------------------------------------------- */

const IMG_1 = "/images/home-choose/img1.jfif";
const IMG_2 = "/images/home-choose/img2.jfif";

const features = [
    {
        icon: ShieldCheck,
        title: "100% Genuine Products",
        description:
            "Authentic hardware sourced from authorized distributors and trusted brands.",
    },
    {
        icon: BadgeCheck,
        title: "Warranty on Every Item",
        description:
            "Manufacturer-backed warranty with quick claims and hassle-free replacements.",
    },
    {
        icon: Truck,
        title: "Fast & Safe Delivery",
        description:
            "Secure packaging and quick shipping so your gear arrives in perfect condition.",
    },
    {
        icon: Headphones,
        title: "Expert Tech Support",
        description:
            "Friendly specialists to help you choose, build, and troubleshoot your setup.",
    },
];

/* -------------------------------------------------------------------------- */
/*  Vector background (pure SVG – crisp at any size, each element animates)   */
/* -------------------------------------------------------------------------- */

const topDots = Array.from({ length: 6 }, (_, i) => 338 + i * 19);
const bottomDots = Array.from({ length: 6 }, (_, i) => 75 + i * 19);
const hatchLines = Array.from({ length: 16 }, (_, i) => 200 + i * 11);

// every animated group scales/rotates around its own center
const fillBox: React.CSSProperties = {
    transformBox: "fill-box",
    transformOrigin: "center",
};

function VectorBackground() {
    return (
        <svg
            aria-hidden="true"
            viewBox="60 80 380 380"
            className="h-full w-full overflow-visible"
        >
            <defs>
                <linearGradient id="cl-purple" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stopColor="#3a0a8a" />
                    <stop offset="100%" stopColor="#5a00e6" />
                </linearGradient>
                <linearGradient id="cl-cyan" x1="0.2" y1="0" x2="0.8" y2="1">
                    <stop offset="0%" stopColor="#00d8ff" />
                    <stop offset="100%" stopColor="#1a4dff" />
                </linearGradient>
                <linearGradient id="cl-mid" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stopColor="#1f6bff" />
                    <stop offset="100%" stopColor="#5a1ee0" />
                </linearGradient>
                <clipPath id="cl-hatch-clip">
                    <path d="M215 330 C260 360 330 330 372 322 C360 380 320 425 262 432 C235 410 215 370 215 330 Z" />
                </clipPath>
            </defs>

            {/* Purple blob */}
            <g
                className="cl-anim"
                style={{
                    ...fillBox,
                    animation: "cl-float-a 11s ease-in-out infinite",
                }}
            >
                <path
                    fill="url(#cl-purple)"
                    d="M130 135 C160 128 175 170 215 215 C260 265 360 305 358 345 C356 385 280 400 210 392 C120 385 78 320 82 240 C85 185 100 140 130 135 Z"
                />
            </g>

            {/* Cyan blob */}
            <g
                className="cl-anim"
                style={{
                    ...fillBox,
                    animation: "cl-float-b 13s ease-in-out infinite",
                }}
            >
                <path
                    fill="url(#cl-cyan)"
                    d="M335 98 C385 100 415 160 412 230 C410 290 365 322 330 336 C290 350 205 330 162 278 C130 238 215 208 260 165 C290 132 305 98 335 98 Z"
                />
            </g>

            {/* Overlap blob */}
            <g
                className="cl-anim"
                style={{
                    ...fillBox,
                    animation: "cl-float-c 9s ease-in-out infinite",
                }}
            >
                <path
                    fill="url(#cl-mid)"
                    opacity="0.78"
                    d="M150 205 C200 195 262 250 340 290 C366 305 360 342 330 344 C260 352 160 332 135 282 C120 245 125 215 150 205 Z"
                />
            </g>

            {/* Thin outline arcs */}
            <g
                className="cl-anim"
                style={{
                    ...fillBox,
                    animation: "cl-drift 16s ease-in-out infinite",
                }}
                fill="none"
                stroke="#1a2340"
                strokeWidth="1.4"
                strokeLinecap="round"
            >
                <path d="M275 108 C300 62 395 62 420 150 C430 190 422 240 402 270" />
                <path d="M160 255 C150 215 230 195 255 205" />
                <path d="M168 282 C205 322 300 322 352 300" />
            </g>

            {/* Dotted rows */}
            <g fill="#1a2340">
                {topDots.map((x, i) => (
                    <circle
                        key={`t${x}`}
                        className="cl-anim"
                        cx={x}
                        cy="150"
                        r="2.4"
                        style={{
                            ...fillBox,
                            animation: `cl-dot 2.8s ease-in-out ${i * 0.18}s infinite`,
                        }}
                    />
                ))}
                {bottomDots.map((x, i) => (
                    <circle
                        key={`b${x}`}
                        className="cl-anim"
                        cx={x}
                        cy="340"
                        r="2.4"
                        style={{
                            ...fillBox,
                            animation: `cl-dot 2.8s ease-in-out ${i * 0.18 + 0.9}s infinite`,
                        }}
                    />
                ))}
            </g>

            {/* Diagonal stripes */}
            <g clipPath="url(#cl-hatch-clip)">
                <g
                    className="cl-anim"
                    stroke="#1a2340"
                    strokeWidth="1.3"
                    style={{ animation: "cl-hatch 6s ease-in-out infinite" }}
                >
                    {hatchLines.map((x) => (
                        <line
                            key={x}
                            x1={x}
                            y1="440"
                            x2={x + 110}
                            y2="320"
                        />
                    ))}
                </g>
            </g>
        </svg>
    );
}

/* -------------------------------------------------------------------------- */
/*  Image showcase (two equal cards + animated vector background)             */
/* -------------------------------------------------------------------------- */

function ImageShowcase() {
    return (
        <div className="relative mx-auto aspect-[5/4] w-full max-w-[620px]">
            {/* Scoped keyframes */}
            <style>{`
                @keyframes cl-float-a {
                    0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
                    50%      { transform: translate(-10px, 12px) rotate(-4deg) scale(1.04); }
                }
                @keyframes cl-float-b {
                    0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
                    50%      { transform: translate(12px, -12px) rotate(4deg) scale(1.05); }
                }
                @keyframes cl-float-c {
                    0%, 100% { transform: translate(0, 0) scale(1); }
                    50%      { transform: translate(8px, 6px) scale(1.06); }
                }
                @keyframes cl-drift {
                    0%, 100% { transform: rotate(0deg) translate(0, 0); }
                    50%      { transform: rotate(3deg) translate(4px, -4px); }
                }
                @keyframes cl-dot {
                    0%, 100% { transform: scale(1); opacity: .45; }
                    50%      { transform: scale(1.9); opacity: 1; }
                }
                @keyframes cl-hatch {
                    0%, 100% { transform: translate(0, 0); }
                    50%      { transform: translate(7px, -7px); }
                }
                @keyframes cl-card-a {
                    0%, 100% { transform: translateY(0) rotateY(-18deg) rotateX(3deg); }
                    50%      { transform: translateY(-14px) rotateY(18deg) rotateX(-3deg); }
                }
                @keyframes cl-card-b {
                    0%, 100% { transform: translateY(0) rotateY(18deg) rotateX(-3deg); }
                    50%      { transform: translateY(14px) rotateY(-18deg) rotateX(3deg); }
                }
                @media (prefers-reduced-motion: reduce) {
                    .cl-anim { animation: none !important; }
                }
            `}</style>

            {/* Vector background – centered, covers the whole left side */}
            <div
                aria-hidden="true"
                className="pointer-events-none absolute left-1/2 top-1/2 aspect-square h-[150%] -translate-x-1/2 -translate-y-1/2 select-none"
            >
                <VectorBackground />
            </div>

            {/* Card 1 – higher, left */}
            <div
                className="absolute left-[4%] top-[3%] w-[46%]"
                style={{ perspective: "1000px" }}
            >
                <div
                    className="cl-anim aspect-[3/4] w-full overflow-hidden rounded-2xl border-4 border-white shadow-2xl will-change-transform"
                    style={{
                        transformStyle: "preserve-3d",
                        animation: "cl-card-a 9s ease-in-out infinite",
                    }}
                >
                    <img
                        src={IMG_1}
                        alt="Custom PC build"
                        draggable={false}
                        className="h-full w-full object-cover"
                    />
                </div>
            </div>

            {/* Card 2 – lower, right */}
            <div
                className="absolute bottom-[3%] right-[4%] w-[46%]"
                style={{ perspective: "1000px" }}
            >
                <div
                    className="cl-anim aspect-[3/4] w-full overflow-hidden rounded-2xl border-4 border-white shadow-2xl will-change-transform"
                    style={{
                        transformStyle: "preserve-3d",
                        animation: "cl-card-b 9s ease-in-out infinite",
                    }}
                >
                    <img
                        src={IMG_2}
                        alt="Gaming PC setup"
                        draggable={false}
                        className="h-full w-full object-cover"
                    />
                </div>
            </div>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  FeatureSection                                                            */
/* -------------------------------------------------------------------------- */

export default function FeatureSection() {
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
            { threshold: 0.2 },
        );
        observer.observe(el);
        return () => observer.disconnect();
    }, []);

    return (
        <section
            ref={sectionRef}
            className="overflow-hidden bg-bg py-16 md:py-24"
        >
            <div className="container mx-auto grid items-center gap-10 px-4 md:px-6 lg:grid-cols-2 lg:gap-16">
                {/* ------------- LEFT: image showcase ------------- */}
                <div
                    className={cn(
                        "transition-all duration-700",
                        !inView && "motion-safe:translate-y-6 motion-safe:opacity-0",
                    )}
                >
                    <ImageShowcase />
                </div>

                {/* ------------- RIGHT: trust features ------------- */}
                <div className="relative z-10">
                    <span className="mb-3 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em] text-accent">
                        <span className="h-px w-8 bg-accent" />
                        Why choose ComLink
                    </span>

                    <h2 className="mb-3 text-3xl font-bold text-fg md:text-4xl">
                        Shop with confidence
                    </h2>
                    <p className="mb-8 max-w-lg text-sm text-muted md:text-base">
                        Connecting your tech means more than selling it. Here
                        is what you can count on every time you order.
                    </p>

                    <div className="space-y-2">
                        {features.map((feature, i) => {
                            const Icon = feature.icon;
                            return (
                                <div
                                    key={feature.title}
                                    style={{ transitionDelay: `${i * 120}ms` }}
                                    className={cn(
                                        "group flex items-start gap-5 rounded-xl border border-transparent p-4 transition-all duration-500 hover:-translate-y-0.5 hover:border-border hover:bg-card hover:shadow-md",
                                        !inView &&
                                            "motion-safe:translate-y-4 motion-safe:opacity-0",
                                    )}
                                >
                                    <div className="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-accent/15 text-accent transition-colors duration-300 group-hover:bg-accent group-hover:text-accent-fg">
                                        <Icon
                                            className="h-6 w-6"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <h3 className="text-base font-semibold text-fg">
                                            {feature.title}
                                        </h3>
                                        <p className="text-sm leading-relaxed text-muted">
                                            {feature.description}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    <Link
                        href="/about"
                        className="mt-6 inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-accent transition-opacity hover:opacity-80"
                    >
                        Learn more about us
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>
    );
}