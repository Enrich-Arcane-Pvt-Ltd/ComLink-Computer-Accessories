import { cn } from "@/utils/cn";
import { Quote, Star } from "lucide-react";

/* -------------------------------------------------------------------------- */
/*  Hard-coded sample testimonials (replace with real reviews later)          */
/* -------------------------------------------------------------------------- */

interface Testimonial {
    id: number;
    description: string;
    name: string;
    role: string;
    rating: number;
}

const testimonials: Testimonial[] = [
    {
        id: 1,
        description:
            "Ordered a mechanical keyboard and a gaming mouse. Both arrived well packed and work perfectly. My desk setup finally feels complete.",
        name: "Kasun Perera",
        role: "PC Gamer",
        rating: 5,
    },
    {
        id: 2,
        description:
            "The Build My PC tool made choosing parts so easy. I got every component from one place with no compatibility worries.",
        name: "Nethmi Silva",
        role: "Video Editor",
        rating: 5,
    },
    {
        id: 3,
        description:
            "Genuine products at fair prices. The SSD I bought cut my project load times in half. Highly recommended.",
        name: "Ravindu Fernando",
        role: "Software Engineer",
        rating: 5,
    },
    {
        id: 4,
        description:
            "Fast delivery and great support. They helped me pick the right monitor for design work and it was exactly what I needed.",
        name: "Ishara Jayasinghe",
        role: "Graphic Designer",
        rating: 4,
    },
    {
        id: 5,
        description:
            "Bought a headset and a webcam for my streams. The quality is amazing for the price, and the warranty gave me peace of mind.",
        name: "Dinesh Kumar",
        role: "Streamer",
        rating: 5,
    },
    {
        id: 6,
        description:
            "As a student on a budget, I found a solid laptop and accessories bundle here. Honest advice from the team too.",
        name: "Amaya Wickramasinghe",
        role: "University Student",
        rating: 5,
    },
    {
        id: 7,
        description:
            "We equipped our whole office with UPS units, printers and chairs. Smooth ordering and everything arrived on time.",
        name: "Sahan De Silva",
        role: "Office Manager",
        rating: 5,
    },
    {
        id: 8,
        description:
            "Great range of gaming gear. The controller and RGB mouse pad I picked up are top quality. I'll be back for my next upgrade.",
        name: "Tharushi Perera",
        role: "Esports Player",
        rating: 4,
    },
    {
        id: 9,
        description:
            "Upgraded my graphics card and power supply here. Clear product details made the decision easy, and checkout was quick.",
        name: "Malik Rajapaksa",
        role: "Home PC Builder",
        rating: 5,
    },
];

/* Three columns, three testimonials each. Visibility per breakpoint. */
const columns = [
    {
        items: testimonials.slice(0, 3),
        className: "animate-testimonial-scroll-1",
    },
    {
        items: testimonials.slice(3, 6),
        className: "hidden md:block animate-testimonial-scroll-2",
    },
    {
        items: testimonials.slice(6, 9),
        className: "hidden lg:block animate-testimonial-scroll-3",
    },
];

const getInitials = (name: string) =>
    name
        .split(" ")
        .map((part) => part[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();

/* -------------------------------------------------------------------------- */
/*  Card                                                                      */
/* -------------------------------------------------------------------------- */

function TestimonialCard({ item }: { item: Testimonial }) {
    return (
        <div className="mb-4 rounded-xl border border-border bg-card p-6 transition-colors duration-300 hover:border-accent">
            <div className="mb-5 flex items-center justify-between">
                <Quote className="h-5 w-5 text-muted" aria-hidden="true" />
                <div
                    className="flex items-center gap-0.5"
                    aria-label={`${item.rating} out of 5 stars`}
                >
                    {Array.from({ length: 5 }).map((_, i) => (
                        <Star
                            key={i}
                            className={cn(
                                "h-4 w-4",
                                i < item.rating
                                    ? "fill-accent text-accent"
                                    : "text-border",
                            )}
                        />
                    ))}
                </div>
            </div>

            <p className="mb-5 text-sm leading-relaxed text-fg/80">
                {item.description}
            </p>

            <div className="flex items-center gap-3">
                <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-full bg-accent text-xs font-bold text-accent-fg">
                    {getInitials(item.name)}
                </div>
                <div>
                    <p className="text-sm font-medium text-fg">{item.name}</p>
                    <p className="text-sm text-muted">{item.role}</p>
                </div>
            </div>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Section                                                                   */
/* -------------------------------------------------------------------------- */

export default function TestimonialSection() {
    return (
        <section className="bg-bg px-4 py-16">
            {/* Scoped keyframes + animation classes */}
            <style>{`
                @keyframes testimonial-scroll-up {
                    0% { transform: translateY(0); }
                    100% { transform: translateY(-50%); }
                }
                .animate-testimonial-scroll-1 { animation: testimonial-scroll-up 25s linear infinite; }
                .animate-testimonial-scroll-2 { animation: testimonial-scroll-up 30s linear infinite; }
                .animate-testimonial-scroll-3 { animation: testimonial-scroll-up 20s linear infinite; }
                #testimonial-columns:hover > div { animation-play-state: paused; }
                @media (prefers-reduced-motion: reduce) {
                    .animate-testimonial-scroll-1,
                    .animate-testimonial-scroll-2,
                    .animate-testimonial-scroll-3 { animation: none; }
                }
            `}</style>

            <div className="mx-auto flex max-w-6xl flex-col items-center">
                {/* Heading */}
                <div className="mb-12 text-center">
                    <h2 className="mb-4 text-4xl font-bold text-fg md:text-5xl">
                        What Our Customers Say
                    </h2>
                    <p className="mx-auto max-w-md text-sm text-muted">
                        Real stories from gamers, creators, and professionals
                        who upgraded their setups with us.
                    </p>
                </div>

                {/* Scrolling columns */}
                <div className="relative w-full overflow-hidden">
                    {/* Fade edges */}
                    <div className="pointer-events-none absolute left-0 right-0 top-0 z-10 h-32 bg-linear-to-b from-bg to-transparent" />
                    <div className="pointer-events-none absolute bottom-0 left-0 right-0 z-10 h-32 bg-linear-to-t from-bg to-transparent" />

                    <div
                        id="testimonial-columns"
                        className="grid h-[600px] grid-cols-1 gap-4 overflow-hidden md:grid-cols-2 lg:grid-cols-3"
                    >
                        {columns.map((col, colIndex) => (
                            <div key={colIndex} className={col.className}>
                                {/* Duplicated list = seamless infinite loop */}
                                {[...col.items, ...col.items].map(
                                    (item, index) => (
                                        <TestimonialCard
                                            key={`${colIndex}-${item.id}-${index}`}
                                            item={item}
                                        />
                                    ),
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}