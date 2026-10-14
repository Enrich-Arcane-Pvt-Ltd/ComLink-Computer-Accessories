import { cn } from "@/utils/cn";
import { Head, Link } from "@inertiajs/react";
import {
    ArrowRight,
    Check,
    Cpu,
    Eye,
    Gamepad2,
    Keyboard,
    Laptop,
    Linkedin,
    Mail,
    MapPin,
    Phone,
    Printer,
    Target,
    Wrench,
} from "lucide-react";
import {
    useEffect,
    useRef,
    useState,
    type ElementType,
    type MouseEvent,
    type ReactNode,
} from "react";

/* ========================================================================== */
/*  HARD-CODED CONTENT: replace with your real details                        */
/* ========================================================================== */

const team = [
    {
        name: "Dilshan Perera",
        role: "Founder & CEO",
        bio: "Started ComLink with one goal: honest tech advice at fair prices.",
        image: "", // e.g. "/images/team/dilshan.jpg" (leave empty for initials)
    },
    {
        name: "Nimali Fernando",
        role: "Operations Manager",
        bio: "Keeps stock, deliveries and branches running smoothly every day.",
        image: "",
    },
    {
        name: "Kavindu Silva",
        role: "Technical Lead",
        bio: "Builds, tests and tunes custom PCs, and knows every component inside out.",
        image: "",
    },
    {
        name: "Sachini Jayawardena",
        role: "Customer Care Lead",
        bio: "Makes sure every customer gets friendly help before and after the sale.",
        image: "",
    },
];

const services = [
    {
        icon: Laptop,
        title: "Laptops & Desktops",
        text: "Gaming, business and student laptops, plus ready-to-use desktop computers.",
    },
    {
        icon: Cpu,
        title: "PC Components",
        text: "Processors, graphics cards, motherboards, memory and storage for your build or upgrade.",
    },
    {
        icon: Keyboard,
        title: "Peripherals",
        text: "Keyboards, mice, headsets, monitors and webcams for work and play.",
    },
    {
        icon: Gamepad2,
        title: "Gaming Gear",
        text: "Consoles, controllers and accessories to complete your battle station.",
    },
    {
        icon: Wrench,
        title: "Custom PC Builds",
        text: "Use our Build My PC tool, or let our technicians assemble and test it for you.",
    },
    {
        icon: Printer,
        title: "Office & Power",
        text: "Printers, scanners, UPS units and ergonomic chairs for home and business.",
    },
];

// Put each logo file in  public/images/brands/  and match the file name here.
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

const reasons = [
    {
        title: "Genuine products",
        text: "Every item comes from authorized channels with a proper warranty.",
    },
    {
        title: "Honest advice",
        text: "We recommend what fits your budget and needs, not what earns the most.",
    },
    {
        title: "Fair pricing",
        text: "Competitive prices and regular offers, with no surprises at checkout.",
    },
    {
        title: "Real support",
        text: "Friendly after-sales help for setup, upgrades and warranty claims.",
    },
];

const journey = [
    {
        year: "2020",
        title: "Journey Start",
        text: "ComLink opened its doors with a small range of computer accessories and a big promise of honest service.",
    },
    {
        year: "2021",
        title: "Growing Range",
        text: "We expanded into PC components and peripherals and welcomed our first community of gamers and builders.",
    },
    {
        year: "2023",
        title: "Second Branch",
        text: "A second branch opened so we could serve more customers closer to home.",
    },
    {
        year: "2024",
        title: "Online Store",
        text: "Our online store launched, letting customers browse, compare and order from anywhere.",
    },
    {
        year: "2025",
        title: "Build My PC",
        text: "We introduced our custom PC builder, making it easy to pick compatible parts.",
    },
    {
        year: "2026",
        title: "A New Chapter",
        text: "A brand-new ComLink website and the start of our next phase of growth.",
    },
];

/* ========================================================================== */
/*  Shared helpers                                                            */
/* ========================================================================== */

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

/** Dark section backdrop: faint tech grid + cyan glow */
function TechBackdrop() {
    return (
        <>
            <div
                aria-hidden="true"
                className="absolute inset-0"
                style={{
                    backgroundImage:
                        "linear-gradient(rgba(34,211,238,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.06) 1px, transparent 1px)",
                    backgroundSize: "44px 44px",
                    maskImage:
                        "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                    WebkitMaskImage:
                        "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                }}
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute left-1/2 top-1/2 h-[420px] w-[760px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-25 blur-3xl"
                style={{
                    background:
                        "radial-gradient(circle, rgba(6,182,212,0.65) 0%, transparent 70%)",
                }}
            />
        </>
    );
}

/** Card with a cyan spotlight that follows the cursor (light or dark look) */
function SpotlightCard({
    children,
    className,
    dark = false,
}: {
    children: ReactNode;
    className?: string;
    dark?: boolean;
}) {
    const onMove = (e: MouseEvent<HTMLDivElement>) => {
        const rect = e.currentTarget.getBoundingClientRect();
        e.currentTarget.style.setProperty("--mx", `${e.clientX - rect.left}px`);
        e.currentTarget.style.setProperty("--my", `${e.clientY - rect.top}px`);
    };

    return (
        <div
            onMouseMove={onMove}
            className={cn(
                "group relative h-full overflow-hidden rounded-2xl border p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg",
                dark
                    ? "border-white/10 bg-white/5 backdrop-blur-sm hover:border-accent-400/60"
                    : "border-border bg-card hover:border-accent/60",
                className,
            )}
        >
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                style={{
                    background:
                        "radial-gradient(320px circle at var(--mx, 50%) var(--my, 50%), rgba(34,211,238,0.15), transparent 60%)",
                }}
            />
            <div className="relative">{children}</div>
        </div>
    );
}

const getInitials = (name: string) =>
    name
        .split(" ")
        .map((p) => p[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();

/* ========================================================================== */
/*  1. Hero                                                                   */
/* ========================================================================== */

function AboutHero() {
    return (
        <section className="relative overflow-hidden bg-primary-950 px-4 py-20 text-center md:py-28">
            <div
                aria-hidden="true"
                className="absolute inset-0"
                style={{
                    backgroundImage:
                        "linear-gradient(rgba(34,211,238,0.07) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.07) 1px, transparent 1px)",
                    backgroundSize: "44px 44px",
                    maskImage:
                        "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                    WebkitMaskImage:
                        "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                }}
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute left-1/2 top-1/2 h-[360px] w-[700px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-30 blur-3xl"
                style={{
                    background:
                        "radial-gradient(circle, rgba(6,182,212,0.65) 0%, transparent 70%)",
                }}
            />

            <div className="relative mx-auto max-w-3xl">
                <nav
                    aria-label="Breadcrumb"
                    className="mb-6 flex items-center justify-center gap-2 text-xs uppercase tracking-widest text-white/60"
                >
                    <Link href="/" className="transition-colors hover:text-accent-300">
                        Home
                    </Link>
                    <span>/</span>
                    <span className="text-accent-300">About us</span>
                </nav>

                <h1 className="mb-4 text-5xl font-bold text-white md:text-7xl">
                    About <span className="text-accent-400">ComLink</span>
                </h1>
                <p className="mx-auto max-w-xl text-sm text-white/70 md:text-base">
                    Connecting your tech. We bring people the right computers,
                    parts and accessories, backed by genuine warranty and
                    honest advice.
                </p>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  2. Who We Are + team                                                      */
/* ========================================================================== */

function TeamCard({ member }: { member: (typeof team)[number] }) {
    return (
        <div
            tabIndex={0}
            className="group relative aspect-[3/4] w-full cursor-default overflow-hidden rounded-3xl bg-primary-900 outline-none transition-transform duration-300 hover:-translate-y-1 focus-visible:ring-2 focus-visible:ring-accent"
        >
            {member.image ? (
                <img
                    src={member.image}
                    alt={member.name}
                    className="h-full w-full object-cover"
                />
            ) : (
                <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-primary-600 to-primary-950">
                    <span className="font-display text-7xl font-bold text-white/15">
                        {getInitials(member.name)}
                    </span>
                </div>
            )}

            {/* Resting overlay (fades out on hover) */}
            <div className="pointer-events-none absolute inset-0 bg-linear-to-b from-transparent via-primary-950/40 to-primary-950 opacity-80 transition-opacity duration-300 group-hover:opacity-0 group-focus-within:opacity-0" />
            {/* Hover overlay (fades in) */}
            <div className="pointer-events-none absolute inset-0 bg-linear-to-b from-transparent via-black/70 to-primary-950 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-focus-within:opacity-100" />

            <div className="absolute bottom-4 left-5 right-5 flex flex-col">
                <h3 className="font-sans text-xl font-semibold normal-case tracking-normal text-white">
                    {member.name}
                </h3>
                <p className="text-sm text-accent-300">{member.role}</p>

                <div className="grid grid-rows-[0fr] transition-all duration-300 group-hover:grid-rows-[1fr] group-focus-within:grid-rows-[1fr]">
                    <div className="overflow-hidden">
                        <p className="pt-3 text-sm text-white/85">{member.bio}</p>
                        <div className="flex items-center gap-2 pt-3">
                            <a
                                href="#"
                                aria-label={`${member.name} on LinkedIn`}
                                className="flex h-8 w-8 items-center justify-center rounded-full border border-white/60 text-white transition-colors hover:bg-white hover:text-black"
                            >
                                <Linkedin className="h-4 w-4" />
                            </a>
                            <a
                                href="#"
                                aria-label={`Email ${member.name}`}
                                className="flex h-8 w-8 items-center justify-center rounded-full border border-white/60 text-white transition-colors hover:bg-white hover:text-black"
                            >
                                <Mail className="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function WhoWeAre() {
    const points = [
        "Genuine products with proper warranty",
        "Honest, no-pressure advice",
        "Gamers, creators, students and businesses served",
    ];

    return (
        <section className="relative overflow-hidden bg-bg py-16 md:py-24">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -left-32 top-10 h-80 w-80 rounded-full bg-accent/10 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-24 bottom-20 h-96 w-96 rounded-full bg-primary-400/10 blur-3xl"
            />
            <div className="container relative mx-auto px-4 md:px-6">
                <SectionHeader
                    label="Who we are"
                    title="Tech people, for tech people"
                />

                <div className="mb-20 grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                    <Reveal className="space-y-4 text-sm leading-relaxed text-muted md:text-base">
                        <p>
                            <strong className="text-fg">ComLink</strong> started
                            with a simple idea: everyone deserves reliable tech
                            without confusing choices or inflated prices. What
                            began as a small accessories counter has grown into
                            a trusted computer store for gamers, creators,
                            students and businesses.
                        </p>
                        <p>
                            Today we stock laptops, PC components, peripherals
                            and gaming gear from brands we trust, and we back
                            every sale with a genuine warranty and honest
                            advice. Our name says it all: we{" "}
                            <strong className="text-fg">connect</strong> people
                            with the right technology, and with a team that
                            cares about getting it right.
                        </p>
                    </Reveal>

                    <Reveal delay={150}>
                        <SpotlightCard className="p-8">
                            <p className="mb-5 font-display text-2xl font-bold text-fg">
                                Connecting your tech
                            </p>
                            <ul className="space-y-3">
                                {points.map((point) => (
                                    <li
                                        key={point}
                                        className="flex items-start gap-3 text-sm text-fg"
                                    >
                                        <span className="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-accent text-accent-fg">
                                            <Check className="h-3 w-3" />
                                        </span>
                                        {point}
                                    </li>
                                ))}
                            </ul>
                        </SpotlightCard>
                    </Reveal>
                </div>

                <Reveal className="mb-8 text-center">
                    <h3 className="text-2xl font-bold text-fg md:text-3xl">
                        Meet the team
                    </h3>
                </Reveal>

                <div className="mx-auto grid max-w-6xl grid-cols-1 justify-items-center gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {team.map((member, i) => (
                        <Reveal key={member.name} delay={i * 100} className="w-full max-w-[290px]">
                            <TeamCard member={member} />
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  3. Mission & Vision (dark band)                                           */
/* ========================================================================== */

function MissionVision() {
    const items: { icon: ElementType; label: string; text: string; no: string }[] = [
        {
            icon: Target,
            no: "01",
            label: "Our mission",
            text: "To make quality technology simple and accessible by offering genuine products, honest advice and service people can rely on, every single day.",
        },
        {
            icon: Eye,
            no: "02",
            label: "Our vision",
            text: "To become the most trusted name in computer accessories, known for connecting every customer with the right tech and building long-term relationships.",
        },
    ];

    return (
        <section className="relative overflow-hidden bg-primary-950 py-16 md:py-24">
            <TechBackdrop />
            <div className="container relative mx-auto px-4 md:px-6">
                <SectionHeader
                    dark
                    label="What drives us"
                    title="Our purpose"
                    subtitle="The promise behind every product we sell."
                />
                <div className="grid gap-6 md:grid-cols-2">
                    {items.map((item, i) => {
                        const Icon = item.icon;
                        return (
                            <Reveal key={item.label} delay={i * 150}>
                                <SpotlightCard dark className="p-8 md:p-10">
                                    <span
                                        aria-hidden="true"
                                        className="pointer-events-none absolute -right-2 -top-6 select-none font-display text-9xl font-bold text-white/5"
                                    >
                                        {item.no}
                                    </span>
                                    <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-accent-400/15 text-accent-300 transition-colors duration-300 group-hover:bg-accent-400 group-hover:text-primary-950">
                                        <Icon className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-3 text-2xl font-bold text-white">
                                        {item.label}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-white/70 md:text-base">
                                        {item.text}
                                    </p>
                                </SpotlightCard>
                            </Reveal>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  4. What we do                                                             */
/* ========================================================================== */

function WhatWeDo() {
    return (
        <section
            className="bg-bg py-16 md:py-24"
            style={{
                backgroundImage:
                    "radial-gradient(rgba(100,116,139,0.22) 1px, transparent 1px)",
                backgroundSize: "22px 22px",
            }}
        >
            <div className="container mx-auto px-4 md:px-6">
                <SectionHeader
                    label="What we do"
                    title="Everything for your setup"
                    subtitle="From a single cable to a full custom build, we cover the whole range."
                />
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {services.map((service, i) => {
                        const Icon = service.icon;
                        return (
                            <Reveal key={service.title} delay={(i % 3) * 100}>
                                <SpotlightCard>
                                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-accent/15 text-accent transition-colors duration-300 group-hover:bg-accent group-hover:text-accent-fg">
                                        <Icon className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-fg">
                                        {service.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-muted">
                                        {service.text}
                                    </p>
                                </SpotlightCard>
                            </Reveal>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  5. Brands: scrolling logo marquee                                         */
/* ========================================================================== */

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

function BrandMarquee() {
    // Repeat the list so one half of the track is always wider than the screen
    const row = Array.from(
        { length: Math.max(1, Math.ceil(10 / brands.length)) },
        () => brands,
    ).flat();
    const track = [...row, ...row]; // two identical halves = seamless loop

    return (
        <section className="bg-card py-16 md:py-24">
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
                    label="Brands we trust"
                    title="Top brands, one store"
                    subtitle="We work with the names you already know and trust."
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

/* ========================================================================== */
/*  6. Why choose us (dark gradient band)                                     */
/* ========================================================================== */

function WhyChooseUs() {
    return (
        <section className="relative overflow-hidden bg-linear-to-br from-primary-900 via-primary-800 to-accent-900 py-16 md:py-24">
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-accent-400/20 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-accent-500/15 blur-3xl"
            />
            <div className="container relative mx-auto px-4 md:px-6">
                <SectionHeader
                    dark
                    label="Why choose us"
                    title="Four reasons customers stay"
                />
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {reasons.map((reason, i) => (
                        <Reveal key={reason.title} delay={i * 100}>
                            <SpotlightCard dark className="pt-8">
                                <span
                                    className="mb-4 block font-display text-6xl font-bold leading-none text-transparent transition-all duration-300 group-hover:[-webkit-text-stroke-color:#67e8f9]"
                                    style={{
                                        WebkitTextStroke: "1.5px rgba(255,255,255,0.28)",
                                    }}
                                >
                                    {String(i + 1).padStart(2, "0")}
                                </span>
                                <h3 className="mb-2 text-lg font-semibold text-white">
                                    {reason.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-white/70">
                                    {reason.text}
                                </p>
                            </SpotlightCard>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  7. Our journey: scroll-driven timeline                                    */
/* ========================================================================== */

function Journey() {
    const containerRef = useRef<HTMLDivElement>(null);
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        const update = () => {
            const el = containerRef.current;
            if (!el) return;
            const rect = el.getBoundingClientRect();
            const p = (window.innerHeight * 0.65 - rect.top) / rect.height;
            setProgress(Math.max(0, Math.min(1, p)));
        };
        update();
        window.addEventListener("scroll", update, { passive: true });
        window.addEventListener("resize", update);
        return () => {
            window.removeEventListener("scroll", update);
            window.removeEventListener("resize", update);
        };
    }, []);

    return (
        <section className="bg-card py-16 md:py-24">
            <div className="container mx-auto px-4 md:px-6">
                <SectionHeader
                    label="Our journey"
                    title="How we got here"
                    subtitle="A few milestones from the ComLink story so far."
                />

                <div ref={containerRef} className="relative mx-auto max-w-5xl">
                    {/* Base line */}
                    <div className="absolute bottom-0 left-4 top-0 w-0.5 -translate-x-1/2 bg-border md:left-1/2" />
                    {/* Progress line (fills as you scroll) */}
                    <div
                        className="absolute left-4 top-0 w-0.5 -translate-x-1/2 bg-linear-to-b from-accent-300 via-accent to-primary-400 md:left-1/2"
                        style={{ height: `${progress * 100}%` }}
                    />

                    <div className="space-y-10 md:space-y-14">
                        {journey.map((item, i) => {
                            const cardLeft = i % 2 === 0;
                            const active = progress >= (i + 0.35) / journey.length;

                            return (
                                <div
                                    key={item.year}
                                    className="relative grid grid-cols-[2rem_1fr] gap-x-3 md:grid-cols-[1fr_3rem_1fr] md:gap-x-0"
                                >
                                    {/* Dot on the line */}
                                    <div className="relative z-10 col-start-1 row-span-2 row-start-1 flex justify-center pt-1.5 md:col-start-2 md:row-span-1 md:items-center md:pt-0">
                                        <span
                                            className={cn(
                                                "block h-4 w-4 rounded-full border-2 transition-all duration-500",
                                                active
                                                    ? "border-accent bg-accent shadow-[0_0_0_6px_rgba(6,182,212,0.22)]"
                                                    : "border-border bg-bg",
                                            )}
                                        />
                                    </div>

                                    {/* Date pill (opposite side of the card) */}
                                    <div
                                        className={cn(
                                            "col-start-2 row-start-1 mb-3 md:row-start-1 md:mb-0 md:flex md:items-center",
                                            cardLeft
                                                ? "md:col-start-3 md:justify-start md:pl-6"
                                                : "md:col-start-1 md:justify-end md:pr-6",
                                        )}
                                    >
                                        <span className="inline-block rounded-full bg-linear-to-r from-accent-600 to-primary-500 px-5 py-1.5 text-sm font-semibold text-white shadow-md">
                                            {item.year}
                                        </span>
                                    </div>

                                    {/* Card */}
                                    <div
                                        className={cn(
                                            "col-start-2 row-start-2 md:row-start-1",
                                            cardLeft
                                                ? "md:col-start-1 md:pr-6"
                                                : "md:col-start-3 md:pl-6",
                                        )}
                                    >
                                        <Reveal>
                                            <div className="rounded-2xl border border-border bg-bg p-5 shadow-sm transition-all duration-300 hover:border-accent/50 hover:shadow-lg">
                                                <h3 className="mb-2 text-lg font-semibold text-fg">
                                                    {item.title}
                                                </h3>
                                                <p className="text-sm leading-relaxed text-muted">
                                                    {item.text}
                                                </p>
                                            </div>
                                        </Reveal>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </section>
    );
}

/* ========================================================================== */
/*  8. Let's connect (dark band)                                              */
/* ========================================================================== */

function ConnectCTA() {
    const contacts = [
        { icon: Mail, text: "info@comlink.lk" },
        { icon: Phone, text: "+94 77 123 4567" },
        { icon: MapPin, text: "Visit one of our branches" },
    ];

    return (
        <section className="relative overflow-hidden bg-primary-950 px-4 py-20 text-center text-white md:px-6 md:py-28">
            <TechBackdrop />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-accent-400/25 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-accent-500/20 blur-3xl"
            />

            <Reveal className="relative mx-auto max-w-2xl">
                <h2 className="mb-4 text-3xl font-bold md:text-5xl">
                    Let's connect
                </h2>
                <p className="mb-8 text-sm text-white/75 md:text-base">
                    Browse our range online, or talk to our team. We'll help
                    you find the right gear for your setup.
                </p>

                <div className="mb-10 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        href="/shop"
                        className="inline-flex items-center gap-2 rounded-md bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-accent-fg transition-opacity hover:opacity-90"
                    >
                        Shop now
                        <ArrowRight className="h-4 w-4" />
                    </Link>
                    <Link
                        href="/contact"
                        className="inline-flex items-center gap-2 rounded-md border border-white/40 px-6 py-3 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-white hover:text-primary-950"
                    >
                        Contact us
                    </Link>
                </div>

                <div className="flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-white/80">
                    {contacts.map(({ icon: Icon, text }) => (
                        <span key={text} className="inline-flex items-center gap-2">
                            <Icon className="h-4 w-4 text-accent-300" />
                            {text}
                        </span>
                    ))}
                </div>
            </Reveal>
        </section>
    );
}

/* ========================================================================== */
/*  Page                                                                      */
/* ========================================================================== */

export default function About() {
    return (
        <>
            <Head title="About Us | ComLink" />
            <AboutHero />
            <WhoWeAre />
            <MissionVision />
            <WhatWeDo />
            <BrandMarquee />
            <WhyChooseUs />
            <Journey />
            <ConnectCTA />
        </>
    );
}