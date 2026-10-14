import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";

/* -------------------------------------------------------------------------- */
/*  Hard-coded banners (replace with admin dashboard data later)              */
/* -------------------------------------------------------------------------- */

interface Banner {
    id: number;
    label: string;
    title: string;
    description: string;
    buttonText: string;
    href: string;
    image: string;
}

const banners: Banner[] = [
    {
        id: 1,
        label: "Custom PC Builder",
        title: "Build Your Dream PC",
        description:
            "Pick your processor, graphics card, memory and more. Made for your performance.",
        buttonText: "Build My PC",
        href: "/",
        image: "/images/banners/banner-build-pc.jpg",
    },
    {
        id: 2,
        label: "Gaming Zone",
        title: "Level Up Your Setup",
        description:
            "Keyboards, mice, headsets and controllers built for speed and precision.",
        buttonText: "Shop Gaming",
        href: "/",
        image: "/images/banners/banner-gaming.jpg",
    },
];

/* -------------------------------------------------------------------------- */
/*  BannerRow                                                                 */
/* -------------------------------------------------------------------------- */

export default function BannerRow() {
    return (
        <section className="py-8 md:py-12">
            <div className="container mx-auto px-4 md:px-6">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
                    {banners.map((banner) => (
                        <Link
                            key={banner.id}
                            href={banner.href}
                            className="group relative block min-h-[220px] overflow-hidden rounded-lg bg-gradient-to-br from-primary-700 to-primary-950 md:min-h-[280px]"
                        >
                            {/* Background image (gradient above shows if the image is missing) */}
                            <div
                                className="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105"
                                style={{
                                    backgroundImage: `url(${banner.image})`,
                                }}
                            />

                            {/* Dark overlay for text readability */}
                            <div className="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-transparent" />

                            {/* Content */}
                            <div className="relative z-10 flex h-full min-h-[220px] flex-col items-start justify-center gap-3 p-6 md:min-h-[280px] md:p-10">
                                <span className="text-xs font-semibold uppercase tracking-[0.2em] text-accent-300">
                                    {banner.label}
                                </span>

                                <h3 className="max-w-xs font-display text-2xl font-bold text-white md:text-3xl">
                                    {banner.title}
                                </h3>

                                <p className="max-w-xs text-sm text-white/80">
                                    {banner.description}
                                </p>

                                <span className="mt-2 inline-flex items-center gap-2 rounded-md bg-accent px-5 py-2.5 text-sm font-bold uppercase tracking-wider text-accent-fg transition-opacity group-hover:opacity-90">
                                    {banner.buttonText}
                                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </span>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}