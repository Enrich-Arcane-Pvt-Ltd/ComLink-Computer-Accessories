import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head, Link } from "@inertiajs/react";
import {
    ArrowRight,
    Cpu,
    Eye,
    Gamepad2,
    Keyboard,
    Laptop,
    Linkedin,
    Mail,
    MapPin,
    MessagesSquare,
    Phone,
    Printer,
    ShieldCheck,
    Target,
    Users,
    Wrench,
} from "lucide-react";
import {
    useEffect,
    useMemo,
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
    { name: "Logitech", logo: "/storage/images/brands/logitech.png" },
    { name: "Corsair", logo: "/storage/images/brands/corsair.png" },
    { name: "ASUS", logo: "/storage/images/brands/asus.png" },
    { name: "MSI", logo: "/storage/images/brands/msi.png" },
    { name: "Razer", logo: "/storage/images/brands/razer.png" },
    { name: "HyperX", logo: "/storage/images/brands/hyperx.png" },
    { name: "Kingston", logo: "/storage/images/brands/kingston.png" },
    { name: "Samsung", logo: "/storage/images/brands/samsung.png" },
    { name: "Seagate", logo: "/storage/images/brands/seagate.png" },
    { name: "TP-Link", logo: "/storage/images/brands/tp-link.png" },
    { name: "Gigabyte", logo: "/storage/images/brands/gigabyte.png" },
    { name: "Cooler Master", logo: "/storage/images/brands/cooler-master.png" },
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

/** Animated glowing-grid background: blue grid that lights up, travelling light pulses and twinkling intersections */
function GridGlowBackdrop() {
    const CELL = 70; // grid cell size in px (all lines below snap to multiples of this)

    // Horizontal pulses: grid row, direction, duration (s), delay (s), length (px)
    const hRuns = [
        { row: 1, rtl: false, dur: 7, delay: 0, len: 240 },
        { row: 3, rtl: true, dur: 9, delay: 1.2, len: 180 },
        { row: 5, rtl: false, dur: 8, delay: 2.6, len: 280 },
        { row: 7, rtl: true, dur: 10, delay: 0.7, len: 200 },
    ];
    // Vertical pulses: grid column, direction, duration (s), delay (s), length (px)
    const vRuns = [
        { col: 2, up: false, dur: 8, delay: 0.5, len: 180 },
        { col: 5, up: true, dur: 10, delay: 2, len: 220 },
        { col: 8, up: false, dur: 7, delay: 3, len: 200 },
        { col: 11, up: true, dur: 9, delay: 0, len: 240 },
        { col: 14, up: false, dur: 11, delay: 1.6, len: 190 },
        { col: 17, up: true, dur: 8, delay: 2.8, len: 210 },
    ];
    // Glowing grid intersections (col, row)
    const nodes = [
        { col: 2, row: 1, delay: 0 },
        { col: 5, row: 3, delay: 0.7 },
        { col: 8, row: 5, delay: 1.4 },
        { col: 11, row: 3, delay: 2.1 },
        { col: 14, row: 1, delay: 0.4 },
        { col: 17, row: 5, delay: 1.8 },
        { col: 5, row: 7, delay: 2.6 },
        { col: 14, row: 7, delay: 1.1 },
    ];

    const gridImage = (alpha: number) =>
        `linear-gradient(rgba(56,189,248,${alpha}) 1px, transparent 1px), linear-gradient(90deg, rgba(56,189,248,${alpha}) 1px, transparent 1px)`;

    return (
        <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
            <style>{`
                @keyframes gg-run-x     { from { transform: translateX(-120%); } to { transform: translateX(110vw); } }
                @keyframes gg-run-x-rev { from { transform: translateX(110vw); }  to { transform: translateX(-120%); } }
                @keyframes gg-run-y     { from { transform: translateY(-120%); } to { transform: translateY(110vh); } }
                @keyframes gg-run-y-rev { from { transform: translateY(110vh); }  to { transform: translateY(-120%); } }
                @keyframes gg-node {
                    0%, 100% { opacity: .2; transform: translate(-50%, -50%) scale(1); }
                    50%      { opacity: 1;  transform: translate(-50%, -50%) scale(2); }
                }
                @keyframes gg-spot {
                    0%   { -webkit-mask-position: -40% 50%; mask-position: -40% 50%; }
                    100% { -webkit-mask-position: 140% 50%; mask-position: 140% 50%; }
                }
                @keyframes gg-breathe {
                    0%, 100% { opacity: .5;  transform: translate(-50%, -50%) scale(1); }
                    50%      { opacity: .85; transform: translate(-50%, -50%) scale(1.12); }
                }
                @keyframes gg-pulse-grid {
                    0%, 100% { opacity: .6; }
                    50%      { opacity: 1; }
                }
                .gg-spot { animation: gg-spot 10s ease-in-out infinite alternate; }
                .gg-breathe { animation: gg-breathe 6s ease-in-out infinite; }
                .gg-pulse-grid { animation: gg-pulse-grid 5s ease-in-out infinite; }
                @media (prefers-reduced-motion: reduce) {
                    .gg-spot, .gg-breathe, .gg-pulse-grid { animation: none; }
                    .gg-run { animation: none !important; opacity: 0 !important; }
                    .gg-node { animation: none !important; opacity: .6 !important; }
                }
            `}</style>

            {/* Centre glow (the bright middle of the reference) */}
            <div
                className="gg-breathe absolute left-1/2 top-1/2 h-[460px] w-[820px] rounded-full blur-3xl"
                style={{
                    background:
                        "radial-gradient(circle, rgba(37,99,235,0.55) 0%, rgba(6,182,212,0.25) 45%, transparent 72%)",
                }}
            />

            {/* Base grid: dim lines, brightest in the middle, fading to the edges */}
            <div
                className="gg-pulse-grid absolute inset-0"
                style={{
                    backgroundImage: gridImage(0.22),
                    backgroundSize: `${CELL}px ${CELL}px`,
                    maskImage: "radial-gradient(ellipse at center, black 15%, transparent 75%)",
                    WebkitMaskImage: "radial-gradient(ellipse at center, black 15%, transparent 75%)",
                }}
            />

            {/* Bright grid revealed by a soft spotlight that drifts left to right */}
            <div
                className="gg-spot absolute inset-0"
                style={{
                    backgroundImage: gridImage(0.85),
                    backgroundSize: `${CELL}px ${CELL}px`,
                    maskImage: "radial-gradient(circle at center, black 0%, transparent 62%)",
                    WebkitMaskImage: "radial-gradient(circle at center, black 0%, transparent 62%)",
                    maskSize: "46% 190%",
                    WebkitMaskSize: "46% 190%",
                    maskRepeat: "no-repeat",
                    WebkitMaskRepeat: "no-repeat",
                    maskPosition: "-40% 50%",
                    WebkitMaskPosition: "-40% 50%",
                }}
            />

            {/* Light pulses running along the horizontal grid lines */}
            {hRuns.map((l, i) => (
                <div
                    key={`h-${i}`}
                    className="gg-run absolute left-0 h-[2px] rounded-full"
                    style={{
                        top: l.row * CELL - 0.5,
                        width: l.len,
                        background: l.rtl
                            ? "linear-gradient(270deg, transparent, rgba(34,211,238,0.95), #fff)"
                            : "linear-gradient(90deg, transparent, rgba(34,211,238,0.95), #fff)",
                        boxShadow: "0 0 14px rgba(34,211,238,0.85)",
                        animation: `${l.rtl ? "gg-run-x-rev" : "gg-run-x"} ${l.dur}s linear ${l.delay}s infinite`,
                    }}
                />
            ))}

            {/* Light pulses running along the vertical grid lines */}
            {vRuns.map((l, i) => (
                <div
                    key={`v-${i}`}
                    className="gg-run absolute top-0 w-[2px] rounded-full"
                    style={{
                        left: l.col * CELL - 0.5,
                        height: l.len,
                        background: l.up
                            ? "linear-gradient(0deg, transparent, rgba(59,130,246,0.95), #fff)"
                            : "linear-gradient(180deg, transparent, rgba(59,130,246,0.95), #fff)",
                        boxShadow: "0 0 14px rgba(59,130,246,0.85)",
                        animation: `${l.up ? "gg-run-y-rev" : "gg-run-y"} ${l.dur}s linear ${l.delay}s infinite`,
                    }}
                />
            ))}

            {/* Twinkling intersections */}
            {nodes.map((n, i) => (
                <span
                    key={`n-${i}`}
                    className="gg-node absolute h-1.5 w-1.5 rounded-full bg-accent-300"
                    style={{
                        left: n.col * CELL,
                        top: n.row * CELL,
                        boxShadow: "0 0 12px 3px rgba(34,211,238,0.9)",
                        animation: `gg-node 3.4s ease-in-out ${n.delay}s infinite`,
                    }}
                />
            ))}

            {/* Edge vignette (dark corners) + soft centre dim so the text stays readable */}
            <div
                className="absolute inset-0"
                style={{
                    background:
                        "radial-gradient(ellipse at center, rgba(2,6,23,0.45) 0%, transparent 55%), radial-gradient(ellipse at center, transparent 55%, rgba(2,6,23,0.7) 100%)",
                }}
            />
        </div>
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

/** Hero background: back-wall grid + perspective floor grid (no corner dots), with a moving floor and light pulses */
function PerspectiveGridBackdrop() {
    const WALL = 44; // wall cell size (px)
    const FLOOR = 40; // floor cell size (px, in the tilted plane)

    // Light pulses that run toward the viewer along floor lines: column index, duration (s), delay (s)
    const pulses = [
        { col: 26, dur: 5, delay: 0 },
        { col: 31, dur: 6.5, delay: 1.6 },
        { col: 35, dur: 5.5, delay: 3.1 },
        { col: 38, dur: 7, delay: 0.8 },
        { col: 42, dur: 6, delay: 2.4 },
        { col: 47, dur: 5.2, delay: 4 },
        { col: 51, dur: 6.8, delay: 1.1 },
    ];

    const line = (rgb: string, alpha: number) =>
        `linear-gradient(rgba(${rgb},${alpha}) 1px, transparent 1px), linear-gradient(90deg, rgba(${rgb},${alpha}) 1px, transparent 1px)`;

    return (
        <div aria-hidden="true" className="pointer-events-none absolute inset-0 overflow-hidden">
            <style>{`
                @keyframes hg-floor { from { background-position: 0 0; } to { background-position: 0 ${FLOOR}px; } }
                @keyframes hg-pulse {
                    0%   { transform: translateY(-100%); opacity: 0; }
                    15%  { opacity: 1; }
                    100% { transform: translateY(360px); opacity: 1; }
                }
                @keyframes hg-wall { 0%, 100% { opacity: .55; } 50% { opacity: 1; } }
                @keyframes hg-glow { 0%, 100% { opacity: .65; } 50% { opacity: 1; } }
                .hg-floor { animation: hg-floor 2.6s linear infinite; }
                .hg-pulse { animation-name: hg-pulse; animation-timing-function: ease-in; animation-iteration-count: infinite; }
                .hg-wall { animation: hg-wall 6s ease-in-out infinite; }
                .hg-glow { animation: hg-glow 5s ease-in-out infinite; }
                @media (prefers-reduced-motion: reduce) {
                    .hg-floor, .hg-wall, .hg-glow { animation: none; }
                    .hg-pulse { animation: none; opacity: 0; }
                }
            `}</style>

            {/* Back wall grid (flat, dim, fades towards the top) */}
            <div
                className="hg-wall absolute inset-0"
                style={{
                    backgroundImage: line("56,189,248", 0.2),
                    backgroundSize: `${WALL}px ${WALL}px`,
                    maskImage: "linear-gradient(to bottom, transparent 0%, black 40%, black 100%)",
                    WebkitMaskImage: "linear-gradient(to bottom, transparent 0%, black 40%, black 100%)",
                }}
            />

            {/* Perspective floor */}
            <div
                className="absolute inset-x-0 bottom-0 h-[48%] overflow-hidden bg-primary-950"
                style={{
                    perspective: "480px",
                    perspectiveOrigin: "50% 0%",
                    maskImage: "linear-gradient(to bottom, transparent 0%, black 30%)",
                    WebkitMaskImage: "linear-gradient(to bottom, transparent 0%, black 30%)",
                }}
            >
                <div
                    className="hg-floor absolute left-1/2 top-0"
                    style={{
                        width: 3000,
                        height: 320,
                        marginLeft: -1500,
                        transformOrigin: "50% 0%",
                        transform: "rotateX(65deg)",
                        backgroundImage: line("59,130,246", 0.7),
                        backgroundSize: `${FLOOR}px ${FLOOR}px`,
                    }}
                >
                    {pulses.map((p, i) => (
                        <span
                            key={i}
                            className="hg-pulse absolute top-0 block w-[2px] rounded-full"
                            style={{
                                left: p.col * FLOOR - 0.5,
                                height: 90,
                                background:
                                    "linear-gradient(180deg, transparent, rgba(34,211,238,0.95), #fff)",
                                boxShadow: "0 0 14px 2px rgba(34,211,238,0.8)",
                                animationDuration: `${p.dur}s`,
                                animationDelay: `${p.delay}s`,
                            }}
                        />
                    ))}
                </div>
            </div>

            {/* Horizon glow line */}
            <div
                className="hg-glow absolute inset-x-0 h-px"
                style={{
                    bottom: "48%",
                    background:
                        "linear-gradient(90deg, transparent, rgba(34,211,238,0.7), transparent)",
                    boxShadow: "0 0 24px 6px rgba(34,211,238,0.35)",
                }}
            />

            {/* Blue glow rising from the bottom edge */}
            <div
                className="absolute inset-0"
                style={{
                    background:
                        "linear-gradient(to top, rgba(37,99,235,0.5) 0%, rgba(37,99,235,0.12) 22%, transparent 45%)",
                }}
            />

            {/* Dark vignette: dark corners + dim centre so the heading stays readable */}
            <div
                className="absolute inset-0"
                style={{
                    background:
                        "radial-gradient(ellipse at center, rgba(2,6,23,0.55) 0%, transparent 60%), radial-gradient(ellipse at center, transparent 55%, rgba(2,6,23,0.65) 100%)",
                }}
            />
        </div>
    );
}

/* ========================================================================== */
/*  1. Hero                                                                   */
/* ========================================================================== */

function AboutHero() {
    return (
        <section className="relative overflow-hidden bg-primary-950 px-4 py-20 text-center md:py-28">
            <PerspectiveGridBackdrop />

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
    const [videoReady, setVideoReady] = useState(false);

    const points = [
        {
            icon: ShieldCheck,
            title: "Genuine products",
            text: "Proper warranty on everything we sell.",
        },
        {
            icon: MessagesSquare,
            title: "Honest advice",
            text: "No-pressure recommendations that fit your budget.",
        },
        {
            icon: Users,
            title: "Built for everyone",
            text: "Gamers, creators, students and businesses served.",
        },
    ];

    const stats = [
        { value: "2020", label: "Since" },
        { value: "2", label: "Branches" },
        { value: "12+", label: "Brands" },
    ];

    return (
        <section className="relative overflow-hidden bg-primary-950 py-20 md:py-28">
            <Head>
                <link
                    rel="preload"
                    as="video"
                    href="/videos/about_video.mp4"
                    type="video/mp4"
                />
            </Head>

            {/* Ambient background video layer */}
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 overflow-hidden bg-gradient-to-br from-primary-950 via-primary-900/50 to-primary-950"
            >
                <video
                    autoPlay
                    loop
                    muted
                    playsInline
                    preload="auto"
                    onLoadedData={() => setVideoReady(true)}
                    onCanPlay={() => setVideoReady(true)}
                    className={cn(
                        "h-full w-full object-cover object-center contrast-105 saturate-110 transition-opacity duration-700",
                        videoReady ? "opacity-65" : "opacity-0",
                    )}
                >
                    <source src="/videos/about_video.mp4" type="video/mp4" />
                </video>
                {/* Cinematic dark wash for crisp readability in both light & dark themes */}
                <div className="absolute inset-0 bg-primary-950/45" />
                {/* Smooth dark top & bottom edge fades (no white fog in light mode) */}
                <div className="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-primary-950/90 via-primary-950/40 to-transparent" />
                <div className="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-primary-950/90 via-primary-950/40 to-transparent" />
            </div>

            {/* Subtle accent glows */}
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -left-32 top-10 h-80 w-80 rounded-full bg-accent/10 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-24 bottom-20 h-96 w-96 rounded-full bg-primary-400/10 blur-3xl"
            />

            <div className="container relative z-10 mx-auto px-4 md:px-6">
                <SectionHeader
                    dark
                    label="Who we are"
                    title="Tech people, for tech people"
                />

                <div className="mb-4 grid items-stretch gap-6 lg:grid-cols-[1.15fr_1fr] lg:gap-8">
                    {/* LEFT: story card */}
                    <Reveal className="h-full">
                        <div className="relative h-full overflow-hidden rounded-3xl border border-white/15 bg-primary-950/70 p-7 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-10">
                            {/* corner brackets */}
                            <span
                                aria-hidden="true"
                                className="absolute left-4 top-4 h-6 w-6 border-l-2 border-t-2 border-accent-400"
                            />
                            <span
                                aria-hidden="true"
                                className="absolute bottom-4 right-4 h-6 w-6 border-b-2 border-r-2 border-accent-400"
                            />
                            {/* glow */}
                            <div
                                aria-hidden="true"
                                className="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-accent-400/20 blur-3xl"
                            />

                            <div className="relative">
                                <span className="mb-5 inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-accent-300">
                                    Our story
                                </span>

                                <p className="mb-4 text-xl font-semibold leading-snug text-white md:text-2xl">
                                    Everyone deserves reliable tech{" "}
                                    <span className="text-accent-300">
                                        without confusing choices or inflated
                                        prices.
                                    </span>
                                </p>

                                <p className="mb-4 text-sm leading-relaxed text-white/70 md:text-base">
                                    <strong className="text-white">ComLink</strong>{" "}
                                    began as a small accessories counter and has
                                    grown into a trusted computer store for
                                    gamers, creators, students and businesses.
                                </p>
                                <p className="text-sm leading-relaxed text-white/70 md:text-base">
                                    Today we stock laptops, PC components,
                                    peripherals and gaming gear from brands we
                                    trust, backed by a genuine warranty and
                                    honest advice. We{" "}
                                    <strong className="text-white">connect</strong>{" "}
                                    people with the right technology, and with a
                                    team that cares about getting it right.
                                </p>

                                {/* stats */}
                                <div className="mt-8 grid grid-cols-3 gap-3 border-t border-white/10 pt-6">
                                    {stats.map((s) => (
                                        <div key={s.label}>
                                            <p className="font-display text-3xl font-bold text-accent-300 md:text-4xl">
                                                {s.value}
                                            </p>
                                            <p className="text-xs uppercase tracking-widest text-white/50">
                                                {s.label}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </Reveal>

                    {/* RIGHT: connecting your tech */}
                    <Reveal delay={150} className="h-full">
                        <SpotlightCard
                            dark
                            className="flex flex-col justify-center rounded-3xl border-white/15 bg-primary-950/70 p-7 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-10"
                        >
                            <p className="mb-1 text-xs font-semibold uppercase tracking-[0.3em] text-accent-300">
                                Our promise
                            </p>
                            <p className="mb-8 font-display text-3xl font-bold text-white">
                                Connecting your tech
                            </p>

                            <ul className="relative space-y-7">
                                {/* connector line */}
                                <span
                                    aria-hidden="true"
                                    className="absolute bottom-5 left-5 top-5 w-px -translate-x-1/2 bg-gradient-to-b from-accent-400/70 via-accent-400/30 to-transparent"
                                />
                                {points.map(({ icon: Icon, title, text }) => (
                                    <li
                                        key={title}
                                        className="group/item relative flex items-start gap-4"
                                    >
                                        <span className="relative z-10 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-accent-400/40 bg-primary-950 text-accent-300 transition-all duration-300 group-hover/item:bg-accent-400 group-hover/item:text-primary-950 group-hover/item:shadow-[0_0_20px_rgba(34,211,238,0.5)]">
                                            <Icon className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h4 className="text-base font-semibold text-white">
                                                {title}
                                            </h4>
                                            <p className="text-sm text-white/65">
                                                {text}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </SpotlightCard>
                    </Reveal>
                </div>

                {/* <Reveal className="mb-8 text-center">
                    <h3 className="text-2xl font-bold text-fg md:text-3xl">
                        Meet the team
                    </h3>
                </Reveal> */}

                {/* <div className="mx-auto grid max-w-6xl grid-cols-1 justify-items-center gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {team.map((member, i) => (
                        <Reveal key={member.name} delay={i * 100} className="w-full max-w-[290px]">
                            <TeamCard member={member} />
                        </Reveal>
                    ))}
                </div> */}

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
/*  4. What we do (3 blended background videos)                               */
/* ========================================================================== */

const bgVideos = [
    { key: "left", src: "/videos/left.mp4" },
    { key: "center", src: "/videos/center.mp4" },
    { key: "right", src: "/videos/right.mp4" },
];

/**
 * How many background videos fit the current screen.
 *   < 768px   -> 1 (center)
 *   768-1023  -> 2 (left + center)
 *   >= 1024   -> 3 (left + center + right)
 * Starts at 1 so phones never download the extra videos.
 */
function useBgVideoCount() {
    const [count, setCount] = useState(1);

    useEffect(() => {
        const lg = window.matchMedia("(min-width: 1024px)");
        const md = window.matchMedia("(min-width: 768px)");
        const update = () => setCount(lg.matches ? 3 : md.matches ? 2 : 1);
        update();
        lg.addEventListener("change", update);
        md.addEventListener("change", update);
        return () => {
            lg.removeEventListener("change", update);
            md.removeEventListener("change", update);
        };
    }, []);

    return count;
}

/** Full-width video strip. Neighbouring videos overlap and cross-fade, so there is no visible seam. */
function VideoBackdrop() {
    const count = useBgVideoCount();

    // Which videos to show: [left, center, right]
    const shown =
        count === 3
            ? [bgVideos[0], bgVideos[1], bgVideos[2]]
            : count === 2
              ? [bgVideos[0], bgVideos[1]]
              : [bgVideos[1]];

    const n = shown.length;
    const OVERLAP = 6; // % of section width that neighbours overlap (increase for a softer blend)
    const itemWidth = (100 + (n - 1) * OVERLAP) / n; // % of section width per video
    const fade = n > 1 ? (OVERLAP / itemWidth) * 100 : 0; // fade size as % of one video

    return (
        <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 flex overflow-hidden bg-primary-950"
        >
            {shown.map((video, i) => {
                const first = i === 0;
                const last = i === n - 1;
                const mask =
                    n === 1
                        ? undefined
                        : `linear-gradient(to right, ${first ? "black" : "transparent"} 0%, black ${
                              first ? 0 : fade
                          }%, black ${last ? 100 : 100 - fade}%, ${last ? "black" : "transparent"} 100%)`;

                return (
                    <div
                        key={video.key}
                        className="relative h-full min-w-0"
                        style={{
                            flex: "1 1 0%",
                            marginLeft: i > 0 ? `-${OVERLAP}%` : 0,
                            maskImage: mask,
                            WebkitMaskImage: mask,
                        }}
                    >
                        <video
                            autoPlay
                            loop
                            muted
                            playsInline
                            preload="auto"
                            className="h-full w-full object-cover object-center opacity-80 motion-reduce:hidden"
                        >
                            <source src={video.src} type="video/mp4" />
                        </video>
                    </div>
                );
            })}

            {/* Readability tint over the whole strip */}
            <div className="absolute inset-0 bg-primary-950/55" />
            {/* Fade into the sections above and below */}
            <div className="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-primary-950 to-transparent" />
            <div className="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-primary-950/80 to-transparent" />
        </div>
    );
}

function WhatWeDo() {
    return (
        <section className="relative overflow-hidden bg-primary-950 py-16 md:py-24">
            <VideoBackdrop />

            <div className="container relative z-10 mx-auto px-4 md:px-6">
                <SectionHeader
                    dark
                    label="What we do"
                    title="Everything for your setup"
                    subtitle="From a single cable to a full custom build, we cover the whole range."
                />
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {services.map((service, i) => {
                        const Icon = service.icon;
                        return (
                            <Reveal key={service.title} delay={(i % 3) * 100}>
                                <SpotlightCard
                                    dark
                                    className="bg-primary-950/60 backdrop-blur-md"
                                >
                                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-accent-400/15 text-accent-300 transition-colors duration-300 group-hover:bg-accent-400 group-hover:text-primary-950">
                                        <Icon className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-white">
                                        {service.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-white/70">
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

interface BrandItem {
    id?: number;
    name: string;
    slug?: string;
    logo?: string | null;
    url?: string | null;
}

function resolveBrandLogoSrc(logo?: string | null): string | null {
    if (!logo || typeof logo !== "string") return null;
    const clean = logo.trim();
    if (clean === "" || clean.startsWith("data:image/svg")) {
        return null;
    }
    if (
        clean.startsWith("http://") ||
        clean.startsWith("https://") ||
        clean.startsWith("/")
    ) {
        return clean;
    }
    return `/storage/${clean}`;
}

function BrandMarquee({ brands: propBrands = [] }: { brands?: BrandItem[] }) {
    const [failedBrandNames, setFailedBrandNames] = useState<Set<string>>(new Set());

    // Only brands with valid, non-empty, non-avatar image paths
    const candidateBrands = useMemo(() => {
        const source =
            propBrands && propBrands.length > 0 ? propBrands : brands;
        return source.filter((b) => resolveBrandLogoSrc(b.logo) !== null);
    }, [propBrands]);

    // Remove any brands whose image fails to load in browser
    const validBrands = useMemo(() => {
        return candidateBrands.filter((b) => !failedBrandNames.has(b.name));
    }, [candidateBrands, failedBrandNames]);

    if (!validBrands || validBrands.length === 0) {
        return null;
    }

    const handleImageError = (brandName: string) => {
        setFailedBrandNames((prev) => {
            const next = new Set(prev);
            next.add(brandName);
            return next;
        });
    };

    // Repeat the list so one half of the track is always wider than the screen
    const repeatCount = Math.max(1, Math.ceil(12 / validBrands.length));
    const row = Array.from(
        { length: repeatCount },
        () => validBrands,
    ).flat();
    const track = [...row, ...row]; // two identical halves = seamless loop

    return (
        <section className="bg-card py-16 md:py-24">
            <style>{`
                @keyframes about-marquee {
                    0%   { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .about-marquee-track { animation: about-marquee 35s linear infinite; }
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
                    <div className="about-marquee relative mx-auto max-w-6xl select-none overflow-hidden py-6">
                        {/* Fade edges */}
                        <div className="pointer-events-none absolute left-0 top-0 z-10 h-full w-20 bg-gradient-to-r from-card to-transparent md:w-36" />
                        <div className="pointer-events-none absolute right-0 top-0 z-10 h-full w-20 bg-gradient-to-l from-card to-transparent md:w-36" />

                        {/* Scrolling track */}
                        <div className="about-marquee-track flex w-max will-change-transform items-center">
                            {track.map((brand, i) => {
                                const src = resolveBrandLogoSrc(brand.logo);
                                if (!src) return null;
                                return (
                                    <div
                                        key={`${brand.name}-${i}`}
                                        aria-hidden={i >= row.length}
                                        className="flex h-20 md:h-24 shrink-0 items-center px-6 md:px-10"
                                    >
                                        <img
                                            src={src}
                                            alt={brand.name}
                                            loading="lazy"
                                            draggable={false}
                                            onError={() => handleImageError(brand.name)}
                                            className="h-12 md:h-16 w-auto max-w-[10rem] md:max-w-[13rem] object-contain drop-shadow-sm dark:brightness-105 transition-transform duration-300 hover:scale-110"
                                        />
                                    </div>
                                );
                            })}
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
/*  8. Let's connect (dark band, animated glowing-grid background)                 */
/* ========================================================================== */

function ConnectCTA() {
    const contacts = [
        { icon: Mail, text: "info@comlink.lk" },
        { icon: Phone, text: "+94 77 123 4567" },
        { icon: MapPin, text: "Visit one of our branches" },
    ];

    return (
        <section className="relative overflow-hidden bg-primary-950 px-4 py-20 text-center text-white md:px-6 md:py-28">
            <GridGlowBackdrop />
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

export default function About({
    brands = [],
}: {
    title?: string;
    brands?: BrandItem[];
}) {
    return (
        <AppLayout>
            <Head title="About Us" />
            <AboutHero />
            <WhoWeAre />
            <MissionVision />
            <WhatWeDo />
            <BrandMarquee brands={brands} />
            <WhyChooseUs />
            {/* <Journey /> */}
            <ConnectCTA />
        </AppLayout>
    );
}