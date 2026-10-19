import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";
import React, { useEffect, useState } from "react";
import {
    ProductCard,
    ProductCardData,
    sample10Products,
} from "../product/ProductCard";
import { Button } from "../ui/Button";

interface NewProductsCarouselProps {
    products?: ProductCardData[];
}

export function NewProductsCarousel({
    products = [],
}: NewProductsCarouselProps) {
    if (!products || products.length === 0) {
        return null;
    }
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const [visibleCards, setVisibleCards] = useState(4);

    // Responsive visible cards count
    useEffect(() => {
        const updateVisibleCards = () => {
            if (window.innerWidth >= 1024) {
                setVisibleCards(4);
            } else if (window.innerWidth >= 640) {
                setVisibleCards(2);
            } else {
                setVisibleCards(1);
            }
        };

        updateVisibleCards();
        window.addEventListener("resize", updateVisibleCards);
        return () => window.removeEventListener("resize", updateVisibleCards);
    }, []);

    const maxIndex = Math.max(0, products.length - visibleCards);

    const next = () => {
        setCurrentIndex((prev) => (prev >= maxIndex ? 0 : prev + 1));
    };

    const prev = () => {
        setCurrentIndex((prev) => (prev <= 0 ? maxIndex : prev - 1));
    };

    // Auto-advance every 5 seconds
    useEffect(() => {
        if (isPaused || maxIndex === 0) return;
        const interval = setInterval(next, 5000);
        return () => clearInterval(interval);
    }, [isPaused, maxIndex, currentIndex]);

    return (
        <section className="py-20 bg-card border-y border-border/40">
            <div className="container mx-auto px-4 md:px-6">
                {/* Header */}
                <div className="flex items-end justify-between mb-10">
                    <div>
                        <span className="text-accent font-semibold tracking-wider uppercase text-sm mb-2 block">
                            Just Landed
                        </span>
                        <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                            OUR NEWEST PRODUCTS
                        </h2>
                    </div>

                    <div className="flex items-center gap-4">
                        <Link
                            href="/shop"
                            className="hidden md:flex items-center gap-2 text-link-main font-semibold hover:text-link-hover transition-colors mr-2"
                        >
                            View All <ArrowRight className="h-4 w-4" />
                        </Link>

                        {/* Navigation Arrows */}
                        <div className="flex items-center gap-2">
                            <button
                                onClick={prev}
                                disabled={products.length <= visibleCards}
                                className="w-10 h-10 flex items-center justify-center border border-border bg-bg/60 hover:bg-muted/10 hover:border-accent text-fg transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                aria-label="Previous products"
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button
                                onClick={next}
                                disabled={products.length <= visibleCards}
                                className="w-10 h-10 flex items-center justify-center border border-border bg-bg/60 hover:bg-muted/10 hover:border-accent text-fg transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                aria-label="Next products"
                            >
                                <ChevronRight className="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>

                {/* Carousel Track */}
                <div
                    className="relative overflow-hidden py-3 -my-3"
                    onMouseEnter={() => setIsPaused(true)}
                    onMouseLeave={() => setIsPaused(false)}
                >
                    <motion.div
                        className="flex"
                        animate={{
                            x: `-${currentIndex * (100 / visibleCards)}%`,
                        }}
                        transition={{
                            type: "spring",
                            stiffness: 260,
                            damping: 26,
                        }}
                    >
                        {products.map((product) => (
                            <div
                                key={product.id}
                                className={cn(
                                    "flex-shrink-0 px-3",
                                    "w-full sm:w-1/2 lg:w-1/4",
                                )}
                            >
                                <ProductCard product={product} />
                            </div>
                        ))}
                    </motion.div>
                </div>

                {/* Pagination Dots */}
                <div className="flex justify-center items-center gap-2 mt-8">
                    {Array.from({ length: maxIndex + 1 }).map((_, i) => (
                        <button
                            key={i}
                            onClick={() => setCurrentIndex(i)}
                            className={cn(
                                "h-1.5 transition-all duration-300 rounded-full cursor-pointer",
                                i === currentIndex
                                    ? "w-8 bg-accent"
                                    : "w-2 bg-muted/40 hover:bg-muted",
                            )}
                            aria-label={`Go to slide ${i + 1}`}
                        />
                    ))}
                </div>

                {/* Mobile View All Button */}
                <div className="mt-8 text-center md:hidden">
                    <Button variant="outline" fullWidth asChild>
                        <Link href="/shop">View All Products</Link>
                    </Button>
                </div>
            </div>
        </section>
    );
}

export default NewProductsCarousel;
