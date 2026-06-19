import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";
import { useEffect, useState } from "react";
import { ProductCard } from "../product/ProductCard";

interface Product {
    id: string;
    name: string;
    price: number;
    originalPrice?: number;
    images: string[];
    category: string;
    rating: number;
    reviews: number;
    isNew?: boolean;
    isSale?: boolean;
    colors: string[];
    sizes: string[];
    description: string;
    brand: string;
}
interface BestSellersCarouselProps {
    products: Product[];
}
export function BestSellersCarousel({
    products = [],
}: BestSellersCarouselProps) {
    const bestSellers = products.slice(0, 8);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const itemsPerPage = 3;
    const maxIndex = Math.max(0, bestSellers.length - itemsPerPage);

    const next = () =>
        setCurrentIndex((prev) => (prev >= maxIndex ? 0 : prev + 1));
    const prev = () =>
        setCurrentIndex((prev) => (prev <= 0 ? maxIndex : prev - 1));

    useEffect(() => {
        if (isPaused) return;
        const interval = setInterval(next, 4000);
        return () => clearInterval(interval);
    }, [isPaused, currentIndex]);

    return (
        <section className="py-20 bg-bg">
            <div className="container mx-auto px-4 md:px-6">
                <div className="flex items-end justify-between mb-10">
                    <div>
                        <span className="text-accent font-semibold tracking-wider uppercase text-sm mb-2 block">
                            Customer Favorites
                        </span>
                        <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                            BEST SELLERS
                        </h2>
                    </div>
                    <div className="hidden md:flex items-center gap-4">
                        <button
                            onClick={prev}
                            className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary transition-colors"
                            aria-label="Previous"
                        >
                            <ChevronLeft className="h-5 w-5" />
                        </button>
                        <button
                            onClick={next}
                            className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary transition-colors"
                            aria-label="Next"
                        >
                            <ChevronRight className="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div
                    className="relative overflow-hidden"
                    onMouseEnter={() => setIsPaused(true)}
                    onMouseLeave={() => setIsPaused(false)}
                >
                    <motion.div
                        className="flex gap-6 md:gap-8"
                        animate={{
                            x: `-${currentIndex * (100 / itemsPerPage)}%`,
                        }}
                        transition={{
                            type: "spring",
                            stiffness: 300,
                            damping: 30,
                        }}
                    >
                        {bestSellers.map((product) => (
                            <div
                                key={product.id}
                                className="w-full md:w-1/3 flex-shrink-0"
                            >
                                <ProductCard product={product} />
                            </div>
                        ))}
                    </motion.div>
                </div>

                {/* Progress dots */}
                <div className="flex justify-center gap-2 mt-6">
                    {Array.from({ length: maxIndex + 1 }).map((_, i) => (
                        <button
                            key={i}
                            onClick={() => setCurrentIndex(i)}
                            className={`h-1.5 rounded-full transition-all duration-300 ${
                                i === currentIndex
                                    ? "w-6 bg-primary"
                                    : "w-1.5 bg-border"
                            }`}
                            aria-label={`Go to slide ${i + 1}`}
                        />
                    ))}
                </div>

                <div className="mt-8 text-center">
                    <Link
                        href="#"
                        className="inline-flex items-center gap-2 text-primary font-semibold hover:text-accent transition-colors"
                    >
                        View All Products <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>
    );
}
