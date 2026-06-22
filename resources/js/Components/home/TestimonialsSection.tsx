import { AnimatePresence, motion } from "framer-motion";
import { ChevronLeft, ChevronRight, Star } from "lucide-react";
import { useEffect, useState } from "react";

interface Testimonial {
    id: string;
    name: string;
    location: string;
    avatar: string;
    rating: number;
    quote: string;
}
interface TestimonialsSectionProps {
    testimonials: Testimonial[];
}

export function TestimonialsSection({
    testimonials = [],
}: TestimonialsSectionProps) {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);

    const next = () => {
        if (testimonials.length === 0) return;
        setCurrentIndex((prev) => (prev + 1) % testimonials.length);
    };

    const prev = () => {
        if (testimonials.length === 0) return;
        setCurrentIndex(
            (prev) => (prev - 1 + testimonials.length) % testimonials.length,
        );
    };

    if (!testimonials || testimonials.length === 0) {
        return null;
    }

    const currentTestimonial = testimonials[currentIndex];

    useEffect(() => {
        if (isPaused) return;
        const interval = setInterval(next, 5000);
        return () => clearInterval(interval);
    }, [isPaused]);

    return (
        <section className="py-20 bg-card">
            <div className="container mx-auto px-4 md:px-6">
                <div className="grid lg:grid-cols-2 gap-12 items-center">
                    <div
                        className="relative"
                        onMouseEnter={() => setIsPaused(true)}
                        onMouseLeave={() => setIsPaused(false)}
                    >
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={currentIndex}
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                transition={{ duration: 0.5 }}
                                className="bg-muted/10 p-8 md:p-12"
                            >
                                <div className="flex items-center gap-4 mb-6">
                                    <img
                                        src={currentTestimonial?.avatar}
                                        alt={currentTestimonial?.name}
                                        className="w-16 h-16 rounded-full object-cover"
                                    />
                                    <div>
                                        <h4 className="font-semibold text-fg text-lg">
                                            {currentTestimonial?.name}
                                        </h4>
                                        <p className="text-sm text-muted">
                                            From {currentTestimonial?.location}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex gap-1 mb-4">
                                    {[...Array(5)].map((_, i) => (
                                        <Star
                                            key={i}
                                            className={`h-4 w-4 ${
                                                i < currentTestimonial.rating
                                                    ? "fill-yellow-500 text-yellow-500"
                                                    : "text-muted"
                                            }`}
                                        />
                                    ))}
                                </div>

                                <p className="text-muted leading-relaxed text-lg italic">
                                    "{currentTestimonial?.quote}"
                                </p>
                            </motion.div>
                        </AnimatePresence>

                        <div className="flex items-center gap-3 mt-6">
                            <button
                                onClick={prev}
                                className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary transition-colors"
                                aria-label="Previous testimonial"
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button
                                onClick={next}
                                className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary transition-colors"
                                aria-label="Next testimonial"
                            >
                                <ChevronRight className="h-5 w-5" />
                            </button>

                            {/* Progress dots */}
                            <div className="flex gap-2 ml-2">
                                {testimonials.map((_, i) => (
                                    <button
                                        key={i}
                                        onClick={() => setCurrentIndex(i)}
                                        className={`h-1.5 rounded-full transition-all duration-300 ${
                                            i === currentIndex
                                                ? "w-6 bg-primary"
                                                : "w-1.5 bg-border"
                                        }`}
                                        aria-label={`Go to testimonial ${i + 1}`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="relative h-[500px] rounded-lg overflow-hidden">
                        <img
                            src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&q=80&w=1000"
                            alt="Happy customer"
                            className="w-full h-full object-cover"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent flex items-end p-8">
                            <div className="text-white">
                                <h3 className="font-display text-3xl font-bold mb-2">
                                    Customers Says.
                                </h3>
                                <p className="text-white/90">
                                    Hear from those who have embraced our
                                    products and services, and let their
                                    heartfelt testimonials inspire you.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
