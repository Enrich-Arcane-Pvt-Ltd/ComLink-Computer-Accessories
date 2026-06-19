import { useTheme } from "@/contexts/ThemeContext";
import { Link } from "@inertiajs/react";
import {
    Facebook,
    Instagram,
    Mail,
    MapPin,
    Phone,
    Twitter,
} from "lucide-react";

export default function Footer() {
    const nowDate = new Date().getFullYear();
    const { theme } = useTheme();

    return (
        <footer className="bg-primary text-primary-fg pt-16 pb-8">
            <div className="container mx-auto px-4 md:px-6">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8 mb-16">
                    {/* Brand Info */}
                    <div className="space-y-6">
                        {/* Logo */}
                        <Link href="/" className="inline-block ">
                            <img
                                src={
                                    theme === "dark"
                                        ? "/images/logo-white.png"
                                        : "/images/logo.png"
                                }
                                alt="Vicky Store Logo"
                                className="h-8 md:h-12 w-auto object-contain transition-transform duration-300 hover:scale-105"
                            />
                        </Link>
                        <p className="text-primary-fg/80 text-sm leading-relaxed max-w-xs">
                            Lorem ipsum dolor sit amet, consectetur adipiscing
                            elit. Suspendisse tristique ullamcorper ex, vitae
                            consequat nisl ultrices a.
                        </p>
                        <div className="flex items-center gap-4 pt-2">
                            <a
                                href="#"
                                className="text-white hover:text-accent transition-colors"
                            >
                                <Facebook className="h-5 w-5" />
                            </a>
                            <a
                                href="#"
                                className="text-white hover:text-accent transition-colors"
                            >
                                <Twitter className="h-5 w-5" />
                            </a>
                            <a
                                href="#"
                                className="text-white hover:text-accent transition-colors"
                            >
                                <Instagram className="h-5 w-5" />
                            </a>
                        </div>
                    </div>

                    {/* Contact Us */}
                    <div className="space-y-6">
                        <h4 className="font-display text-lg font-semibold tracking-wider text-white">
                            Contact Us
                        </h4>
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

                    {/* Company Links / Customer Support */}
                    <div className="space-y-6">
                        <h4 className="font-display text-lg font-semibold tracking-wider text-white">
                            Support & Company
                        </h4>
                        <ul className="space-y-4 text-sm text-primary-fg/80">
                            <li>
                                <Link
                                    href={route("about")}
                                    className="hover:text-white transition-colors inline-flex items-center gap-2"
                                >
                                    About Us
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("faqs")}
                                    className="hover:text-white transition-colors inline-flex items-center gap-2"
                                >
                                    FAQs
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("contact")}
                                    className="hover:text-white transition-colors inline-flex items-center gap-2"
                                >
                                    Contact Us
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("testimonials")}
                                    className="hover:text-white transition-colors inline-flex items-center gap-2"
                                >
                                    Customers Says
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Policies */}
                    <div className="space-y-6">
                        <h4 className="font-display text-lg font-semibold tracking-wider text-white">
                            Our Policies
                        </h4>
                        <ul className="space-y-3 text-sm text-primary-fg/80">
                            <li>
                                <Link
                                    href={route("shipping.show")}
                                    className="hover:text-white transition-colors"
                                >
                                    Shipping & Delivery
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("refunds.show")}
                                    className="hover:text-white transition-colors"
                                >
                                    Returns, Refunds & Cancellations
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("terms.show")}
                                    className="hover:text-white transition-colors"
                                >
                                    Terms & Conditions
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("privacy.show")}
                                    className="hover:text-white transition-colors"
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Bottom Bar */}
                <div className="pt-4 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div className="">
                        <p className="text-sm text-primary-fg/60">
                            &copy; {nowDate}{" "}
                            <strong className="hover:text-accent-500">
                                Vicky Store
                            </strong>
                            . All Rights Reserved.
                        </p>
                        <p className="text-xs text-primary-fg/40">
                            {" "}
                            Developed by{" "}
                            <a
                                target="_blank"
                                rel="noreferrer"
                                href="https://www.enricharcane.com/"
                                className="font-bold hover:text-accent-500 transition-colors inline-flex items-center gap-1"
                            >
                                Enrich Arcane
                            </a>{" "}
                            with ❤️
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
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
            </div>
        </footer>
    );
}
