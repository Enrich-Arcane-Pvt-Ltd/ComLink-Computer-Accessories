import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";
import { useState } from "react";
import { products } from "../../data/products";
import { ProductCard } from "../product/ProductCard";
export function BestSellersCarousel() {
    const bestSellers = products.slice(0, 8);
    const [currentIndex, setCurrentIndex] = useState(0);
    const itemsPerPage = 3;
    const maxIndex = Math.max(0, bestSellers.length - itemsPerPage);
    const next = () => {
        setCurrentIndex((prev) => Math.min(prev + 1, maxIndex));
    };
    const prev = () => {
        setCurrentIndex((prev) => Math.max(prev - 1, 0));
    };
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
                            disabled={currentIndex === 0}
                            className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary disabled:opacity-30 disabled:hover:border-border transition-colors"
                            aria-label="Previous"
                        >
                            <ChevronLeft className="h-5 w-5" />
                        </button>
                        <button
                            onClick={next}
                            disabled={currentIndex >= maxIndex}
                            className="w-10 h-10 flex items-center justify-center border border-border hover:border-primary disabled:opacity-30 disabled:hover:border-border transition-colors"
                            aria-label="Next"
                        >
                            <ChevronRight className="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div className="relative overflow-hidden">
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

                <div className="mt-10 text-center">
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
