import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import {
    ArrowRight,
    BadgeCheck,
    Headphones,
    RotateCw,
    ShieldCheck,
    Truck,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Hard-coded content                                                        */
/* -------------------------------------------------------------------------- */

const LOGO_SRC = "/images/comlink-logo-3d.png"; // cropped, transparent PNG

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
/*  3D logo (stacked layers = extrusion, auto-rotate + drag to rotate)        */
/* -------------------------------------------------------------------------- */

const DEPTH = 40; // px thickness of the extruded logo
const MID_LAYERS = 14; // layers that form the "side walls"
const AUTO_SPEED = 0.45; // degrees per frame (~60fps)
const DEFAULT_TILT = 8; // resting X tilt in degrees
const LOGO_ASPECT = "487 / 177";

const layerBase: React.CSSProperties = {
    position: "absolute",
    inset: 0,
    width: "100%",
    height: "100%",
    pointerEvents: "none",
    userSelect: "none",
};

function Logo3D() {
    const objRef = useRef<HTMLDivElement>(null);
    const state = useRef({
        rotY: -25,
        rotX: DEFAULT_TILT,
        velY: AUTO_SPEED,
        dragging: false,
        lastX: 0,
        lastY: 0,
    });
    const [grabbing, setGrabbing] = useState(false);

    useEffect(() => {
        const reduceMotion = window.matchMedia(
            "(prefers-reduced-motion: reduce)",
        ).matches;
        const autoSpeed = reduceMotion ? 0 : AUTO_SPEED;

        let frame = 0;
        let last = performance.now();

        const tick = (now: number) => {
            const dt = Math.min((now - last) / 16.67, 3);
            last = now;
            const s = state.current;

            if (!s.dragging) {
                // ease velocity back to the auto-rotate speed (gives inertia)
                s.velY += (autoSpeed - s.velY) * 0.04 * dt;
                s.rotY += s.velY * dt;
                // ease the tilt back to its resting angle
                s.rotX += (DEFAULT_TILT - s.rotX) * 0.03 * dt;
            }

            if (objRef.current) {
                objRef.current.style.transform = `rotateX(${s.rotX}deg) rotateY(${s.rotY}deg)`;
            }
            frame = requestAnimationFrame(tick);
        };

        frame = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(frame);
    }, []);

    const onPointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
        const s = state.current;
        s.dragging = true;
        s.lastX = e.clientX;
        s.lastY = e.clientY;
        e.currentTarget.setPointerCapture(e.pointerId);
        setGrabbing(true);
    };

    const onPointerMove = (e: React.PointerEvent<HTMLDivElement>) => {
        const s = state.current;
        if (!s.dragging) return;
        const dx = e.clientX - s.lastX;
        const dy = e.clientY - s.lastY;
        s.lastX = e.clientX;
        s.lastY = e.clientY;
        s.rotY += dx * 0.6;
        s.velY = dx * 0.6; // remembered for inertia on release
        s.rotX = Math.max(-35, Math.min(35, s.rotX - dy * 0.4));
    };

    const endDrag = (e: React.PointerEvent<HTMLDivElement>) => {
        state.current.dragging = false;
        setGrabbing(false);
        if (e.currentTarget.hasPointerCapture(e.pointerId)) {
            e.currentTarget.releasePointerCapture(e.pointerId);
        }
    };

    const onKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (e.key === "ArrowLeft") state.current.velY = -6;
        if (e.key === "ArrowRight") state.current.velY = 6;
    };

    const midLayers = Array.from({ length: MID_LAYERS }, (_, i) => {
        const z = -DEPTH / 2 + (DEPTH * (i + 1)) / (MID_LAYERS + 1);
        return z;
    });

    return (
        <div
            role="img"
            tabIndex={0}
            aria-label="Rotating 3D ComLink logo. Drag, or use the left and right arrow keys, to rotate."
            onPointerDown={onPointerDown}
            onPointerMove={onPointerMove}
            onPointerUp={endDrag}
            onPointerCancel={endDrag}
            onKeyDown={onKeyDown}
            className={cn(
                "relative z-10 flex h-full w-full items-center justify-center outline-none focus-visible:ring-2 focus-visible:ring-accent",
                grabbing ? "cursor-grabbing" : "cursor-grab",
            )}
            style={{ perspective: "1200px", touchAction: "pan-y" }}
        >
            <div
                ref={objRef}
                className="relative w-[78%] max-w-[440px] will-change-transform"
                style={{
                    aspectRatio: LOGO_ASPECT,
                    transformStyle: "preserve-3d",
                }}
            >
                {/* Back face (readable when the logo is rotated 180°) */}
                <img
                    src={LOGO_SRC}
                    alt=""
                    draggable={false}
                    style={{
                        ...layerBase,
                        transform: `rotateY(180deg) translateZ(${DEPTH / 2}px)`,
                        backfaceVisibility: "hidden",
                    }}
                />

                {/* Side walls: darkened layers between front and back */}
                {midLayers.map((z) => (
                    <img
                        key={z}
                        src={LOGO_SRC}
                        alt=""
                        draggable={false}
                        style={{
                            ...layerBase,
                            transform: `translateZ(${z}px)`,
                            filter: "brightness(0.35) saturate(1.2)",
                        }}
                    />
                ))}

                {/* Front face */}
                <img
                    src={LOGO_SRC}
                    alt=""
                    draggable={false}
                    style={{
                        ...layerBase,
                        transform: `translateZ(${DEPTH / 2}px)`,
                        backfaceVisibility: "hidden",
                    }}
                />
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
        <section ref={sectionRef} className="bg-bg py-16 md:py-24">
            <div className="container mx-auto grid items-center gap-10 px-4 md:px-6 lg:grid-cols-2 lg:gap-16">
                {/* ------------- LEFT: 3D logo stage ------------- */}
                <div
                    className={cn(
                        "relative aspect-[4/3] w-full overflow-hidden rounded-2xl border border-border bg-linear-to-br from-white via-accent-50 to-primary-100 shadow-lg transition-all duration-700 sm:aspect-[5/4]",
                        !inView && "motion-safe:translate-y-6 motion-safe:opacity-0",
                    )}
                >
                    {/* Glow */}
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[70%] w-[70%] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-40 blur-3xl"
                        style={{
                            background:
                                "radial-gradient(circle, rgba(6,182,212,0.55) 0%, transparent 70%)",
                        }}
                    />

                    {/* Orbit rings */}
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 100 100"
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[92%] -translate-x-1/2 -translate-y-1/2 motion-safe:animate-[spin_50s_linear_infinite]"
                    >
                        <circle
                            cx="50"
                            cy="50"
                            r="48"
                            fill="none"
                            stroke="rgba(8,145,178,0.35)"
                            strokeWidth="0.35"
                            strokeDasharray="1.5 3"
                        />
                    </svg>
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 100 100"
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[74%] -translate-x-1/2 -translate-y-1/2 motion-safe:animate-[spin_35s_linear_infinite_reverse]"
                    >
                        <circle
                            cx="50"
                            cy="50"
                            r="48"
                            fill="none"
                            stroke="rgba(25,35,65,0.22)"
                            strokeWidth="0.35"
                            strokeDasharray="6 4"
                        />
                    </svg>

                    {/* Floor shadow */}
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute bottom-[14%] left-1/2 h-5 w-[55%] -translate-x-1/2 rounded-full bg-primary-950/25 blur-xl"
                    />

                    <Logo3D />

                    {/* Hint */}
                    <div className="pointer-events-none absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 rounded-full bg-white/85 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm backdrop-blur">
                        <RotateCw className="h-3.5 w-3.5" aria-hidden="true" />
                        Drag to rotate
                    </div>
                </div>

                {/* ------------- RIGHT: trust features ------------- */}
                <div>
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