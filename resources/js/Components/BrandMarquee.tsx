import { cn } from "@/utils/cn";
import { useEffect, useRef, useState, type ReactNode } from "react";

// Put each logo file in public/images/brands/ and match the file name here.
// Use transparent PNG or SVG. Coloured logos are shown as they are.
const brands = [
    { name: "Logitech", logo: "/images/brands/logitech.png" },
    { name: "Corsair", logo: "/images/brands/corsair.png" },
    { name: "ASUS", logo: "/images/brands/asus.png" },
    { name: "MSI", logo: "/images/brands/msi.png" },
    { name: "Razer", logo: "/images/brands/razer.png" },
    { name: "HyperX", logo: "/images/brands/hyperx.png" },
    { name: "Kingston", logo: "/images/brands/kingston.png" },
    { name: "Samsung", logo: "/images/brands/samsung.png" },
    { name: "Seagate", logo: "/images/brands/seagate.png" },
    { name: "TP-Link", logo: "/images/brands/tp-link.png" },
    { name: "Gigabyte", logo: "/images/brands/gigabyte.png" },
    { name: "Cooler Master", logo: "/images/brands/cooler-master.png" },
];

function useInView<T extends HTMLElement>(threshold = 0.15) {
    const ref = useRef<T>(null);
    const [inView, setInView] = useState(false);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setInView(true);
                    observer.disconnect();
                }
            },
            { threshold },
        );
        observer.observe(el);
        return () => observer.disconnect();
    }, [threshold]);

    return { ref, inView };
}

function Reveal({
    children,
    delay = 0,
    className,
}: {
    children: ReactNode;
    delay?: number;
    className?: string;
}) {
    const { ref, inView } = useInView<HTMLDivElement>();
    return (
        <div
            ref={ref}
            style={{ transitionDelay: `${delay}ms` }}
            className={cn(
                "transition-all duration-700",
                !inView && "motion-safe:translate-y-6 motion-safe:opacity-0",
                className,
            )}
        >
            {children}
        </div>
    );
}

function SectionHeader({
    label,
    title,
    subtitle,
    dark = false,
}: {
    label: string;
    title: string;
    subtitle?: string;
    dark?: boolean;
}) {
    return (
        <Reveal className="mx-auto mb-12 max-w-2xl text-center">
            <span
                className={cn(
                    "mb-3 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em]",
                    dark ? "text-accent-300" : "text-accent",
                )}
            >
                <span className={cn("h-px w-8", dark ? "bg-accent-300" : "bg-accent")} />
                {label}
                <span className={cn("h-px w-8", dark ? "bg-accent-300" : "bg-accent")} />
            </span>
            <h2
                className={cn(
                    "mb-3 text-3xl font-bold md:text-5xl",
                    dark ? "text-white" : "text-fg",
                )}
            >
                {title}
            </h2>
            {subtitle && (
                <p className={cn("text-sm md:text-base", dark ? "text-white/65" : "text-muted")}>
                    {subtitle}
                </p>
            )}
        </Reveal>
    );
}

function BrandLogo({ name, logo }: { name: string; logo: string }) {
    const [failed, setFailed] = useState(false);

    // If a logo file is missing, show the brand name instead of a broken image
    if (failed) {
        return (
            <span className="whitespace-nowrap font-display text-lg font-semibold tracking-[0.18em] text-muted hover:text-fg transition-colors">
                {name}
            </span>
        );
    }

    return (
        <img
            src={logo}
            alt={name}
            loading="lazy"
            draggable={false}
            onError={() => setFailed(true)}
            className="h-9 w-auto max-w-[9rem] object-contain md:h-10"
        />
    );
}

interface BrandMarqueeProps {
    label?: string;
    title?: string;
    subtitle?: string;
    className?: string;
}

export default function BrandMarquee({
    label = "Brands we trust",
    title = "Top brands, one store",
    subtitle = "We work with the names you already know and trust.",
    className = "",
}: BrandMarqueeProps) {
    // Repeat the list so one half of the track is always wider than the screen
    const row = Array.from(
        { length: Math.max(1, Math.ceil(10 / brands.length)) },
        () => brands,
    ).flat();
    const track = [...row, ...row]; // two identical halves = seamless loop

    return (
        <section className={cn("bg-card py-16 md:py-24", className)}>
            <style>{`
                @keyframes about-marquee {
                    0%   { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .about-marquee-track { animation: about-marquee 40s linear infinite; }
                .about-marquee:hover .about-marquee-track { animation-play-state: paused; }
                @media (prefers-reduced-motion: reduce) {
                    .about-marquee-track { animation: none; }
                }
            `}</style>

            <div className="container mx-auto px-4 md:px-6">
                <SectionHeader
                    label={label}
                    title={title}
                    subtitle={subtitle}
                />

                <Reveal>
                    <div className="about-marquee relative mx-auto max-w-5xl select-none overflow-hidden py-4">
                        {/* Fade edges */}
                        <div className="pointer-events-none absolute left-0 top-0 z-10 h-full w-16 bg-gradient-to-r from-card to-transparent md:w-32" />
                        <div className="pointer-events-none absolute right-0 top-0 z-10 h-full w-16 bg-gradient-to-l from-card to-transparent md:w-32" />

                        {/* Scrolling track */}
                        <div className="about-marquee-track flex w-max will-change-transform">
                            {track.map((brand, i) => (
                                <div
                                    key={`${brand.name}-${i}`}
                                    aria-hidden={i >= row.length}
                                    className="flex h-14 items-center px-6 md:px-10"
                                >
                                    <BrandLogo
                                        name={brand.name}
                                        logo={brand.logo}
                                    />
                                </div>
                            ))}
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>
    );
}
