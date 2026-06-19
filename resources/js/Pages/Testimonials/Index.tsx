import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronDown, ChevronUp, Star } from "lucide-react";
import { useState } from "react";

interface TestimonialItem {
    id: string;
    name: string;
    location: string;
    avatar: string;
    rating: number;
    quote: string;
}

interface TestimonialsProps {
    testimonials: TestimonialItem[];
    title?: string;
}

export default function Testimonials({
    testimonials = [],
    title = "Reviews",
}: TestimonialsProps) {
    // පාරකට ලෝඩ් කරන්න ඕන ගණන (9)
    const ITEMS_PER_PAGE = 9;
    const [visibleCount, setVisibleCount] = useState(ITEMS_PER_PAGE);

    // දැනට පෙන්විය යුතු ටෙස්ටිමෝනියල් ටික පමණක් කපා ගැනීම (Slice)
    const visibleTestimonials = testimonials.slice(0, visibleCount);

    // Dynamic ලෙස මුළු reviews වල average එක සෙවීම
    const averageRating =
        testimonials.length > 0
            ? testimonials.reduce((acc, curr) => acc + curr.rating, 0) /
              testimonials.length
            : 0;

    // See More click කරපු ගමන් තව 9ක් එකතු කිරීම
    const handleSeeMore = () => {
        setVisibleCount((prevCount) => prevCount + ITEMS_PER_PAGE);
    };

    // See Less click කරපු ගමන් ආපහු මුල් ගණනට (9) අඩු කිරීම
    const handleSeeLess = () => {
        setVisibleCount(ITEMS_PER_PAGE);
        // පිටුව උඩට Smooth Scroll එකක් දාන්න පුළුවන් අවශ්‍ය නම්:
        window.scrollTo({ top: 300, behavior: "smooth" });
    };

    return (
        <AppLayout>
            <Head title={title} />

            <div className="bg-bg min-h-screen py-16">
                <div className="container mx-auto px-4 md:px-6">
                    {/* Header Section */}
                    <div className="text-center mb-16">
                        <h1 className="font-display text-4xl font-bold text-fg mb-4 uppercase tracking-wide">
                            {title}
                        </h1>
                        <div className="flex items-center justify-center gap-4 mb-4">
                            <div className="flex text-yellow-500">
                                {[...Array(5)].map((_, i) => (
                                    <Star
                                        key={i}
                                        className={`h-6 w-6 ${i < Math.round(averageRating) ? "fill-current" : "text-muted"}`}
                                    />
                                ))}
                            </div>
                            <span className="text-xl font-bold text-fg">
                                {averageRating.toFixed(1)} / 5.0
                            </span>
                        </div>
                        <p className="text-muted">
                            Based on {testimonials.length} reviews
                        </p>
                    </div>

                    {/* Testimonials Grid with Framer Motion Animation */}
                    <motion.div
                        layout
                        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                    >
                        <AnimatePresence mode="popLayout">
                            {visibleTestimonials.map((testimonial) => (
                                <motion.div
                                    layout
                                    initial={{ opacity: 0, scale: 0.9 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    exit={{ opacity: 0, scale: 0.9 }}
                                    transition={{ duration: 0.3 }}
                                    key={testimonial.id}
                                    className="bg-card p-8 border border-border flex flex-col h-full shadow-sm"
                                >
                                    <div className="flex text-yellow-500 mb-4">
                                        {[...Array(5)].map((_, i) => (
                                            <Star
                                                key={i}
                                                className={`h-4 w-4 ${i < testimonial.rating ? "fill-current" : "text-muted"}`}
                                            />
                                        ))}
                                    </div>
                                    <p className="text-fg italic mb-6 flex-grow leading-relaxed">
                                        "{testimonial.quote}"
                                    </p>
                                    <div className="flex items-center gap-4 mt-auto pt-6 border-t border-border">
                                        <img
                                            src={testimonial.avatar}
                                            alt={testimonial.name}
                                            className="w-12 h-12 rounded-full object-cover"
                                        />
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                {testimonial.name}
                                            </h4>
                                            <p className="text-xs text-muted">
                                                {testimonial.location}
                                            </p>
                                        </div>
                                    </div>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </motion.div>

                    {/* Pagination Actions (See More / See Less) */}
                    <div className="mt-16 text-center flex justify-center">
                        {visibleCount < testimonials.length ? (
                            /* --- See More Button --- */
                            <button
                                onClick={handleSeeMore}
                                className="inline-flex items-center gap-2 px-8 py-3.5 border border-border bg-card text-fg font-display text-sm font-semibold uppercase tracking-wider hover:bg-primary hover:text-primary-fg hover:border-primary transition-all duration-300 shadow-sm"
                            >
                                See More Reviews
                                <ChevronDown className="h-4 w-4" />
                            </button>
                        ) : (
                            /* --- See Less Button (පෙන්වන්නේ ඔක්කොම ලෝඩ් වුණාම පමණි) --- */
                            testimonials.length > ITEMS_PER_PAGE && (
                                <button
                                    onClick={handleSeeLess}
                                    className="inline-flex items-center gap-2 px-8 py-3.5 border border-primary bg-primary text-primary-fg font-display text-sm font-semibold uppercase tracking-wider hover:bg-transparent hover:text-fg hover:border-border transition-all duration-300 shadow-sm"
                                >
                                    See Less
                                    <ChevronUp className="h-4 w-4" />
                                </button>
                            )
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
