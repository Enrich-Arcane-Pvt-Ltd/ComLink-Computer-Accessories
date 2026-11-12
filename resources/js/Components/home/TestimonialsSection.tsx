import { cn } from "@/utils/cn";
import { Quote, Star } from "lucide-react";

export interface TestimonialItem {
    id: string | number;
    name: string;
    location?: string;
    role?: string;
    avatar?: string | null;
    avatar_url?: string | null;
    rating: number;
    quote?: string;
    description?: string;
}

interface TestimonialsSectionProps {
    testimonials?: TestimonialItem[];
}

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

function TestimonialCard({ item }: { item: TestimonialItem }) {
    const text = item.quote || item.description || "";
    const roleOrLocation = item.location || item.role || "Verified Customer";
    const avatar = item.avatar || item.avatar_url;

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
                {text}
            </p>

            <div className="flex items-center gap-3">
                {avatar ? (
                    <img
                        src={avatar}
                        alt={item.name}
                        className="size-9 rounded-full object-cover border border-border"
                        onError={(e) => {
                            (e.target as HTMLElement).style.display = "none";
                        }}
                    />
                ) : (
                    <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-full bg-accent text-xs font-bold text-accent-fg">
                        {getInitials(item.name)}
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium text-fg">{item.name}</p>
                    <p className="truncate text-sm text-muted">{roleOrLocation}</p>
                </div>
            </div>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Section                                                                   */
/* -------------------------------------------------------------------------- */

export default function TestimonialsSection({
    testimonials = [],
}: TestimonialsSectionProps) {
    if (!testimonials || testimonials.length === 0) {
        return null;
    }

    // Distribute testimonials across 3 columns
    const col1: TestimonialItem[] = [];
    const col2: TestimonialItem[] = [];
    const col3: TestimonialItem[] = [];

    testimonials.forEach((item, index) => {
        if (index % 3 === 0) col1.push(item);
        else if (index % 3 === 1) col2.push(item);
        else col3.push(item);
    });

    if (col2.length === 0) col2.push(...col1);
    if (col3.length === 0) col3.push(...col1);

    // Ensure at least 3 items in each column so continuous scrolling is smooth
    const padItems = (items: TestimonialItem[], min = 3): TestimonialItem[] => {
        if (items.length === 0) return [];
        let res = [...items];
        while (res.length < min) {
            res = [...res, ...items];
        }
        return res;
    };

    const columns = [
        {
            items: padItems(col1),
            className: "animate-testimonial-scroll-1",
        },
        {
            items: padItems(col2),
            className: "hidden md:block animate-testimonial-scroll-2",
        },
        {
            items: padItems(col3),
            className: "hidden lg:block animate-testimonial-scroll-3",
        },
    ];

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
