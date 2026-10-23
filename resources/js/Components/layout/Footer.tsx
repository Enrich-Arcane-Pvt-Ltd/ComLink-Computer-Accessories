import { useTheme } from "@/contexts/ThemeContext";
import { Link } from "@inertiajs/react";
import {
    ArrowRight,
    Facebook,
    Instagram,
    Mail,
    MapPin,
    Phone,
    Twitter,
} from "lucide-react";
import { useState } from "react";

export default function Footer() {
    const nowDate = new Date().getFullYear();
    const { theme } = useTheme();
    const [email, setEmail] = useState("");

    const handleSubscribe = (e: React.FormEvent) => {
        e.preventDefault();
        // TODO: wire to your newsletter endpoint, e.g.
        // router.post(route("newsletter.subscribe"), { email });
        setEmail("");
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
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="Enter your email"
                                className="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm text-primary-fg placeholder:text-primary-fg/40 focus:outline-none"
                            />
                            <button
                                type="submit"
                                className="inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-semibold text-black hover:opacity-90 transition-opacity"
                            >
                                Subscribe
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
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
            <div className="relative">
                <div
                    aria-hidden="true"
                    className="pointer-events-none select-none text-center font-display font-extrabold leading-none tracking-tight text-white/[0.06] text-[22vw] lg:text-[17rem] -mb-[0.18em]"
                    style={{
                        WebkitMaskImage:
                            "linear-gradient(to bottom, black 35%, transparent 95%)",
                        maskImage:
                            "linear-gradient(to bottom, black 35%, transparent 95%)",
                    }}
                >
                    ComLink
                </div>

                <div className="absolute inset-x-0 bottom-0 pb-6">
                    <div className="container mx-auto px-4 md:px-6 flex flex-col items-center gap-2 text-center">
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
                        <div className="flex items-center gap-4 text-xs text-primary-fg/60">
                            <Link
                                href={route("privacy.show")}
                                className="hover:text-accent transition-colors"
                            >
                                Privacy Policy
                            </Link>
                            <Link
                                href={route("terms.show")}
                                className="hover:text-accent transition-colors"
                            >
                                Terms of Use
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}