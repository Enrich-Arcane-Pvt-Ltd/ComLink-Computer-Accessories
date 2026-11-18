import { useTheme } from "@/contexts/ThemeContext";
import { Link, router } from "@inertiajs/react";
import {
    ArrowRight,
    Facebook,
    Instagram,
    Mail,
    MapPin,
    Phone,
    Twitter,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";

// Seamless 400px-period sine wave path spanning -400 to 1600 (2000px wide for smooth translation)
function buildWavePath(baseY: number, amp: number, phaseShift = 0): string {
    const startX = -400 + phaseShift;
    const period = 400;
    const half = period / 2;
    let d = `M ${startX} ${baseY}`;
    for (let x = startX; x < 1600; x += period) {
        const x1 = x + half * 0.4;
        const x2 = x + half * 0.6;
        const xm = x + half;
        const x3 = xm + half * 0.4;
        const x4 = xm + half * 0.6;
        const xe = x + period;
        d += ` C ${x1} ${baseY - amp}, ${x2} ${baseY - amp}, ${xm} ${baseY}`;
        d += ` C ${x3} ${baseY + amp}, ${x4} ${baseY + amp}, ${xe} ${baseY}`;
    }
    d += ` L 2000 320 L -400 320 Z`;
    return d;
}

const WAVE_BACK_PATH = buildWavePath(162, 18, 0);
const WAVE_FRONT_PATH = buildWavePath(172, 15, 140);

export default function Footer() {
    const nowDate = new Date().getFullYear();
    const { theme } = useTheme();
    const [email, setEmail] = useState("");
    const [subscribing, setSubscribing] = useState(false);
    const [feedback, setFeedback] = useState<{ type: "success" | "error"; message: string } | null>(null);
    const [isWaveHovered, setIsWaveHovered] = useState(false);
    const waveBackRef = useRef<SVGPathElement | null>(null);
    const waveFrontRef = useRef<SVGPathElement | null>(null);
    const waveStateRef = useRef({
        x1: 0,
        x2: 0,
        yOffset: 0,
        speed1: 0.9,
        speed2: -0.55,
        hovered: false,
        mouseBias: 0,
    });

    useEffect(() => {
        waveStateRef.current.hovered = isWaveHovered;
    }, [isWaveHovered]);

    useEffect(() => {
        if (typeof window === "undefined") return;
        const reducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
        if (reducedMotion) return;

        let rafId = 0;
        const animate = () => {
            const st = waveStateRef.current;
            const targetSpeed1 = st.hovered ? 2.4 + st.mouseBias * 1.8 : 0.9;
            const targetSpeed2 = st.hovered ? -1.8 + st.mouseBias * 1.2 : -0.55;
            const targetY = st.hovered ? -18 : 0;

            st.speed1 += (targetSpeed1 - st.speed1) * 0.06;
            st.speed2 += (targetSpeed2 - st.speed2) * 0.06;
            st.yOffset += (targetY - st.yOffset) * 0.08;

            st.x1 = (st.x1 + st.speed1 + 400) % 400;
            st.x2 = (st.x2 + st.speed2 + 400) % 400;

            if (waveFrontRef.current) {
                waveFrontRef.current.setAttribute(
                    "transform",
                    `translate(${st.x1.toFixed(2)}, ${st.yOffset.toFixed(2)})`
                );
            }
            if (waveBackRef.current) {
                waveBackRef.current.setAttribute(
                    "transform",
                    `translate(${st.x2.toFixed(2)}, ${(st.yOffset * 0.85).toFixed(2)})`
                );
            }

            rafId = window.requestAnimationFrame(animate);
        };

        rafId = window.requestAnimationFrame(animate);
        return () => window.cancelAnimationFrame(rafId);
    }, []);

    const handleSubscribe = (e: React.FormEvent) => {
        e.preventDefault();
        if (!email.trim() || subscribing) return;

        setSubscribing(true);
        setFeedback(null);

        router.post(
            route("newsletter.subscribe"),
            { email: email.trim() },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEmail("");
                    setSubscribing(false);
                    const msg = "Thank you for subscribing to our newsletter!";
                    setFeedback({ type: "success", message: msg });
                    toast.success(msg);
                    setTimeout(() => setFeedback(null), 5000);
                },
                onError: (errors) => {
                    setSubscribing(false);
                    const msg = errors.email || "Failed to subscribe. Please try again.";
                    setFeedback({ type: "error", message: msg });
                    toast.error(msg);
                    setTimeout(() => setFeedback(null), 5000);
                },
            },
        );
    };

    const socials = [
        { label: "Facebook", icon: Facebook, href: "#" },
        { label: "Twitter", icon: Twitter, href: "#" },
        { label: "Instagram", icon: Instagram, href: "#" },
    ];

    const categories = [
        { label: "Laptops", slug: "laptops" },
        { label: "PC Components", slug: "pc-components" },
        { label: "Peripherals", slug: "peripherals" },
        { label: "Monitors & Displays", slug: "monitors-displays" },
        { label: "Gaming", slug: "gaming" },
        { label: "Software", slug: "software" },
    ];

    const linkClass =
        "text-primary-fg/70 hover:text-accent transition-colors duration-200";

    const headingClass =
        "font-display text-sm font-semibold tracking-wider text-primary-fg/50 mb-5";

    return (
        <footer className="relative overflow-hidden bg-primary text-primary-fg">
            <div className="container mx-auto px-4 md:px-6 pt-16">
                {/* Top row: brand | payments | newsletter */}
                <div className="grid grid-cols-1 gap-10 lg:grid-cols-3 lg:items-start pb-14">
                    {/* Brand */}
                    <div className="max-w-md space-y-2">
                        <Link href="/" className="inline-block -mt-6 -mb-4 md:-mt-10 md:-mb-8">
                            <img
                                src="/images/Comlink_logo.png"
                                alt="ComLink Logo"
                                className="h-24 md:h-36 w-auto object-contain"
                            />
                        </Link>
                        <p className="text-primary-fg/80 text-sm leading-relaxed">
                            Your one-stop shop for all computer and IT
                            accessories in Sri Lanka. Quality products,
                            great prices, and reliable service.
                        </p>
                        <div className="flex items-center gap-3">
                            {socials.map(({ label, icon: Icon, href }) => (
                                <a
                                    key={label}
                                    href={href}
                                    aria-label={label}
                                    className="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 text-white hover:border-accent hover:text-accent transition-colors"
                                >
                                    <Icon className="h-4 w-4" />
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Shop by Category + payment badges */}
                    <div className="lg:justify-self-center">
                        <h4 className={headingClass}>Shop by Category</h4>
                        <ul className="grid grid-cols-2 gap-x-10 gap-y-3 text-sm">
                            {categories.map(({ label, slug }) => (
                                <li key={slug}>
                                    <Link
                                        href={`${route("shop")}?category=${slug}`}
                                        className={linkClass}
                                    >
                                        {label}
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        <div className="mt-6 flex flex-wrap items-center gap-2">
                            <span className="h-7 px-2.5 flex items-center bg-white rounded text-[#1434CB] text-[11px] font-bold italic tracking-tight">
                                VISA
                            </span>
                            <span className="h-7 px-2 flex items-center bg-white rounded">
                                <span className="relative flex items-center">
                                    <span className="h-4 w-4 rounded-full bg-[#EB001B]" />
                                    <span className="h-4 w-4 rounded-full bg-[#F79E1B] -ml-2 mix-blend-multiply" />
                                </span>
                            </span>
                            <span className="h-7 px-2.5 flex items-center bg-white rounded text-[11px] font-bold">
                                <span className="text-[#003087]">Pay</span>
                                <span className="text-[#009CDE]">Pal</span>
                            </span>
                            <span className="h-7 px-2.5 flex items-center bg-white rounded text-[#016FD0] text-[10px] font-bold tracking-tight">
                                AMEX
                            </span>
                        </div>
                    </div>

                    {/* Newsletter */}
                    <form
                        onSubmit={handleSubscribe}
                        className="w-full max-w-md lg:justify-self-end"
                    >
                        <label
                            htmlFor="footer-newsletter"
                            className="block text-xs text-primary-fg/60 mb-2"
                        >
                            Stay up to date. Subscribe to our newsletter.
                        </label>
                        <div className="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 p-1.5 focus-within:border-accent transition-colors">
                            <input
                                id="footer-newsletter"
                                type="email"
                                required
                                disabled={subscribing}
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="Enter your email"
                                className="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm text-primary-fg placeholder:text-primary-fg/40 focus:outline-none disabled:opacity-50"
                            />
                            <button
                                type="submit"
                                disabled={subscribing}
                                className="inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-semibold text-black hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                {subscribing ? "Subscribing..." : "Subscribe"}
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                        {feedback && (
                            <p
                                className={`text-xs mt-2 font-medium ${
                                    feedback.type === "success"
                                        ? "text-emerald-400"
                                        : "text-red-400"
                                }`}
                            >
                                {feedback.message}
                            </p>
                        )}
                    </form>
                </div>

                {/* Link columns */}
                <div className="grid grid-cols-1 gap-10 border-t border-white/10 pt-12 pb-16 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Quick Links */}
                    <div>
                        <h4 className={headingClass}>Quick Links</h4>
                        <ul className="space-y-3 text-sm">
                            <li>
                                <Link href={route("home")} className={linkClass}>
                                    Home
                                </Link>
                            </li>
                            <li>
                                <Link href={route("shop")} className={linkClass}>
                                    Shop
                                </Link>
                            </li>
                            <li>
                                <Link href={route("about")} className={linkClass}>
                                    About Us
                                </Link>
                            </li>
                            <li>
                                <Link href={route("contact")} className={linkClass}>
                                    Contact Us
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Contact Us */}
                    <div>
                        <h4 className={headingClass}>Contact Us</h4>
                        <ul className="space-y-4 text-sm text-primary-fg/80">
                            <li className="flex items-start gap-3">
                                <MapPin className="h-5 w-5 shrink-0 mt-0.5" />
                                <span>368 Ocean Ave, Seaside, CA 12345</span>
                            </li>
                            <li className="flex items-center gap-3">
                                <Phone className="h-5 w-5 shrink-0" />
                                <span>+ (555) - 123-6868</span>
                            </li>
                            <li className="flex items-center gap-3">
                                <Mail className="h-5 w-5 shrink-0" />
                                <span>Milana@example.com</span>
                            </li>
                        </ul>
                    </div>

                    {/* Support & Company */}
                    <div>
                        <h4 className={headingClass}>Support & Company</h4>
                        <ul className="space-y-3 text-sm">
                            <li>
                                <Link href={route("faqs")} className={linkClass}>
                                    FAQs
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("testimonials")}
                                    className={linkClass}
                                >
                                    Customers Says
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Our Policies */}
                    <div>
                        <h4 className={headingClass}>Our Policies</h4>
                        <ul className="space-y-3 text-sm">
                            <li>
                                <Link
                                    href={route("shipping.show")}
                                    className={linkClass}
                                >
                                    Shipping & Delivery
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("refunds.show")}
                                    className={linkClass}
                                >
                                    Returns, Refunds & Cancellations
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("terms.show")}
                                    className={linkClass}
                                >
                                    Terms & Conditions
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("privacy.show")}
                                    className={linkClass}
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {/* Watermark + bottom bar */}
            <div
                className="relative min-h-[110px] sm:min-h-[130px] overflow-hidden"
                onMouseEnter={() => setIsWaveHovered(true)}
                onMouseLeave={() => {
                    setIsWaveHovered(false);
                    waveStateRef.current.mouseBias = 0;
                }}
                onMouseMove={(e) => {
                    const rect = e.currentTarget.getBoundingClientRect();
                    if (rect.width > 0) {
                        const normalized = (e.clientX - rect.left) / rect.width;
                        waveStateRef.current.mouseBias = (normalized - 0.5) * 2;
                    }
                }}
            >
                <div className="relative select-none pointer-events-none flex justify-center w-full -mb-2 sm:-mb-3">
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 1200 240"
                        preserveAspectRatio="xMidYMax meet"
                        className="w-full max-w-[1380px] h-auto block"
                    >
                        <defs>
                            <mask id="comlink-watermark-mask">
                                <text
                                    x="600"
                                    y="218"
                                    textAnchor="middle"
                                    fill="white"
                                    className="font-display font-extrabold tracking-tight"
                                    style={{ fontSize: "245px" }}
                                >
                                    ComLink
                                </text>
                            </mask>
                            <linearGradient id="comlink-wave-grad-back" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stopColor="#0284c7" stopOpacity="0.28" />
                                <stop offset="50%" stopColor="#06b6d4" stopOpacity="0.42" />
                                <stop offset="100%" stopColor="#0ea5e9" stopOpacity="0.28" />
                            </linearGradient>
                            <linearGradient id="comlink-wave-grad-front" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stopColor="#06b6d4" stopOpacity="0.45" />
                                <stop offset="50%" stopColor="#22d3ee" stopOpacity="0.62" />
                                <stop offset="100%" stopColor="#38bdf8" stopOpacity="0.45" />
                            </linearGradient>
                        </defs>

                        <g mask="url(#comlink-watermark-mask)">
                            {/* Subtle watermark base */}
                            <rect width="1200" height="240" fill="rgba(255, 255, 255, 0.06)" />

                            {/* Back flowing wave */}
                            <path
                                ref={waveBackRef}
                                d={WAVE_BACK_PATH}
                                fill="url(#comlink-wave-grad-back)"
                            />

                            {/* Front flowing wave */}
                            <path
                                ref={waveFrontRef}
                                d={WAVE_FRONT_PATH}
                                fill="url(#comlink-wave-grad-front)"
                            />
                        </g>
                    </svg>
                </div>

                <div className="absolute inset-x-0 bottom-8 sm:bottom-10 z-10">
                    <div className="container mx-auto px-4 md:px-6 flex flex-col items-center gap-1.5 text-center">
                        <p className="text-sm text-primary-fg/60">
                            Copyright &copy; {nowDate}
                            <span className="mx-3 sm:mx-4 text-primary-fg/40">|</span>
                            All Rights Reserved
                        </p>
                        <p className="text-xs text-primary-fg/40">
                            Developed by{" "}
                            <a
                                target="_blank"
                                rel="noreferrer"
                                href="https://www.enricharcane.com/"
                                className="font-bold hover:text-accent transition-colors"
                            >
                                Enrich Arcane
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    );
}