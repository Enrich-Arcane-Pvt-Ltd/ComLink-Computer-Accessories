import { cn } from "@/utils/cn";
import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";
import React, { useEffect, useState } from "react";
import {
    ProductCard,
    ProductCardData,
} from "../product/ProductCard";
import { Button } from "../ui/Button";

export const sampleBestSellers: ProductCardData[] = [
    {
        id: 101,
        name: "Logitech G PRO X SUPERLIGHT 2 Wireless Gaming Mouse",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Mouse",
        originalPrice: 159.99,
        discountPercent: 10,
        inStock: true,
    },
    {
        id: 102,
        name: "AMD Ryzen 9 7950X3D 16-Core 32-Thread High-End Processor",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Processors",
        originalPrice: 699.0,
        discountPercent: 15,
        inStock: true,
    },
    {
        id: 103,
        name: "Corsair Vengeance RGB DDR5 32GB (2x16GB) 6000MHz RAM",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Memory Cards",
        originalPrice: 124.99,
        discountPercent: 12,
        inStock: true,
    },
    {
        id: 104,
        name: "ASUS TUF Gaming GeForce RTX 4070 Ti SUPER 16GB GPU",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Graphic Cards",
        originalPrice: 799.99,
        discountPercent: 8,
        inStock: true,
    },
    {
        id: 105,
        name: "Samsung Odyssey G9 49\" Curved OLED Gaming Monitor 240Hz",
        image: "/images/products/sample-keyboard.jpg",
        category: "Monitors & Displays",
        subCategory: "Gaming Monitors",
        originalPrice: 1299.99,
        discountPercent: 20,
        inStock: true,
    },
    {
        id: 106,
        name: "Razer BlackWidow V4 Pro Mechanical Gaming Keyboard RGB",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Keyboards",
        originalPrice: 229.99,
        discountPercent: 10,
        inStock: true,
    },
    {
        id: 107,
        name: "Lian Li O11 Dynamic EVO RGB Dual-Chamber Mid-Tower Case",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Chassis",
        originalPrice: 169.99,
        discountPercent: 0,
        inStock: true,
    },
    {
        id: 108,
        name: "DeepCool LT720 360mm High-Performance Liquid CPU Cooler",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "Coolers",
        originalPrice: 139.99,
        discountPercent: 15,
        inStock: true,
    },
    {
        id: 109,
        name: "HyperX Cloud III Wireless Spatial Audio Gaming Headset",
        image: "/images/products/sample-keyboard.jpg",
        category: "Peripherals",
        subCategory: "Headsets",
        originalPrice: 169.99,
        discountPercent: 10,
        inStock: true,
    },
    {
        id: 110,
        name: "WD_BLACK 4TB SN850X NVMe PCIe Gen4 Internal Gaming SSD",
        image: "/images/products/sample-keyboard.jpg",
        category: "PC Components",
        subCategory: "SSD",
        originalPrice: 319.99,
        discountPercent: 15,
        inStock: false,
    },
];

interface BestSellersCarouselProps {
    products?: any[];
}

export function BestSellersCarousel({
    products = [],
}: BestSellersCarouselProps) {
    if (!products || products.length === 0) {
        return null;
    }

    const displayProducts = products;

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

    const maxIndex = Math.max(0, displayProducts.length - visibleCards);

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
        <section className="py-20 bg-bg border-b border-border/40">
            <div className="container mx-auto px-4 md:px-6">
                {/* Header */}
                <div className="flex items-end justify-between mb-10">
                    <div>
                        <span className="text-accent font-semibold tracking-wider uppercase text-sm mb-2 block">
                            Customer Favorites
                        </span>
                        <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                            BEST SELLERS
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
                                disabled={displayProducts.length <= visibleCards}
                                className="w-10 h-10 flex items-center justify-center border border-border bg-card hover:bg-muted/10 hover:border-accent text-fg transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                aria-label="Previous best sellers"
                            >
                                <ChevronLeft className="h-5 w-5" />
                            </button>
                            <button
                                onClick={next}
                                disabled={displayProducts.length <= visibleCards}
                                className="w-10 h-10 flex items-center justify-center border border-border bg-card hover:bg-muted/10 hover:border-accent text-fg transition-all disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer"
                                aria-label="Next best sellers"
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
                        {displayProducts.map((product) => (
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
                        <Link href="/shop">View All Best Sellers</Link>
                    </Button>
                </div>
            </div>
        </section>
    );
}

export default BestSellersCarousel;
