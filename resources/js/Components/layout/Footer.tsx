import { Link } from "@inertiajs/react";
import { Facebook, Instagram, Linkedin, Twitter, Youtube } from "lucide-react";
import React from "react";

// Function to handle scrolling with offset for anchor links (#)
const handleNavClick = (
    e: React.MouseEvent<HTMLAnchorElement, MouseEvent>,
    href: string,
) => {
    if (href.startsWith("#")) {
        e.preventDefault();
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);

        if (window.location.pathname !== "/") {
            window.location.href = `/${href}`;
        } else if (targetElement) {
            const targetPosition = targetElement.offsetTop - 50; // 50px offset
            window.scrollTo({
                top: targetPosition,
                behavior: "smooth",
            });
        }
    }
};

export default function Footer() {
    const nowDate = new Date().getFullYear();

    return (
        <footer className="bg-[var(--color-bg-alt)] border-t border-[var(--color-border)] pt-16 pb-8 transition-colors duration-200">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                    {/* Brand Column */}
                    <div className="space-y-4">
                        <Link href={"/"} className="flex items-center gap-2">
                            <img
                                className="max-h-[160px] h-auto object-contain self-center -mb-8 -mt-4"
                                src={"/images/logo-new.png"}
                                alt="site logo"
                            />
                        </Link>
                        <p className="text-[var(--color-text-muted)] text-sm leading-relaxed">
                            Learn Beyond Limits. A modern global learning
                            platform designed to make quality education
                            accessible, engaging, and future-ready.
                        </p>
                        <div className="flex space-x-4 pt-2">
                            <a
                                href="https://www.facebook.com/"
                                target="_blank"
                                rel="noreferrer"
                                className="text-[var(--color-text-muted)] hover:text-primary transition-colors"
                            >
                                <Facebook className="w-5 h-5" />
                            </a>
                            <a
                                href="https://twitter.com/"
                                target="_blank"
                                rel="noreferrer"
                                className="text-[var(--color-text-muted)] hover:text-primary transition-colors"
                            >
                                <Twitter className="w-5 h-5" />
                            </a>
                            <a
                                href="https://www.instagram.com/"
                                target="_blank"
                                rel="noreferrer"
                                className="text-[var(--color-text-muted)] hover:text-primary transition-colors"
                            >
                                <Instagram className="w-5 h-5" />
                            </a>
                            <a
                                href="https://www.linkedin.com/"
                                target="_blank"
                                rel="noreferrer"
                                className="text-[var(--color-text-muted)] hover:text-primary transition-colors"
                            >
                                <Linkedin className="w-5 h-5" />
                            </a>
                            <a
                                href="https://youtube.com/"
                                target="_blank"
                                rel="noreferrer"
                                className="text-[var(--color-text-muted)] hover:text-primary transition-colors"
                            >
                                <Youtube className="w-5 h-5" />
                            </a>
                        </div>

                        {/* <div className="pt-4">
                            <h4 className="text-sm font-semibold text-[var(--color-text-main)] mb-2">
                                Subscribe to Newsletter
                            </h4>
                            <form
                                className="flex gap-2"
                                onSubmit={(e) => e.preventDefault()}
                            >
                                <input
                                    type="email"
                                    placeholder="Email address"
                                    className="w-full px-3 py-2 bg-[var(--color-bg-base)] border border-[var(--color-border)] rounded-lg text-sm focus:outline-none focus:border-primary"
                                />
                                <button
                                    type="submit"
                                    className="px-3 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                                >
                                    Join
                                </button>
                            </form>
                        </div> */}
                    </div>

                    {/* Explore */}
                    <div>
                        <h3 className="font-semibold text-[var(--color-text-main)] mb-4">
                            Explore
                        </h3>
                        <ul className="space-y-3">
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Course Catalog
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Top Instructors
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Advertisements
                                </Link>
                            </li>
                            {/* <li>
                                <a
                                    href="#services"
                                    onClick={(e) =>
                                        handleNavClick(e, "#service")
                                    }
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Our Services
                                </a>
                            </li> */}
                        </ul>
                    </div>

                    {/* Company */}
                    <div>
                        <h3 className="font-semibold text-[var(--color-text-main)] mb-4">
                            Company
                        </h3>
                        <ul className="space-y-3">
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    About Us
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/be_member"
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Become an Instructor
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Support */}
                    <div>
                        <h3 className="font-semibold text-[var(--color-text-main)] mb-4">
                            Support
                        </h3>
                        <ul className="space-y-3">
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Contact Us
                                </Link>
                            </li>
                            {/* <li>
                                <a
                                    href="#faq"
                                    onClick={(e) => handleNavClick(e, "#faq")}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    FAQs
                                </a>
                            </li> */}
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Terms & Conditions
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={"/"}
                                    className="text-[var(--color-text-muted)] hover:text-primary text-sm transition-colors"
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="border-t border-[var(--color-border)] pt-8 flex justify-center items-center">
                    <p className="text-[var(--color-text-muted)] text-sm text-center">
                        &copy; {nowDate} <strong>ThinkPath Learning</strong>.
                        All rights reserved. Developed by{" "}
                        <a
                            target="_blank"
                            rel="noreferrer"
                            href="https://www.enricharcane.com/"
                            className="font-bold hover:text-primary text-[var(--color-text-main)] transition-colors inline-flex items-center gap-1"
                        >
                            Enrich Arcane
                        </a>{" "}
                        with ❤️ in Sri Lanka.
                    </p>
                </div>
            </div>
        </footer>
    );
}
