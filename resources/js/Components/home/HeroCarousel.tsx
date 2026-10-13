import { Link } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "../ui/Button";

interface Slide {
    id: number;
    image: string;
    title: string;
    subtitle: string;
    description: string;
    cta: string;
    link: string;
}

export function HeroCarousel({ slides = [] }: { slides: Slide[] }) {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [direction, setDirection] = useState(0);
    useEffect(() => {
        const timer = setInterval(() => {
            setDirection(1);
            setCurrentSlide((prev) => (prev + 1) % slides.length);
        }, 6000);
        return () => clearInterval(timer);
    }, []);
    const goToSlide = (index: number) => {
        setDirection(index > currentSlide ? 1 : -1);
        setCurrentSlide(index);
    };
    const nextSlide = () => {
        setDirection(1);
        setCurrentSlide((prev) => (prev + 1) % slides.length);
    };
    const prevSlide = () => {
        setDirection(-1);
        setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);
    };
    const variants = {
        enter: (direction: number) => ({
            opacity: 0,
            scale: 1.1,
        }),
        center: {
            opacity: 1,
            scale: 1,
        },
        exit: (direction: number) => ({
            opacity: 0,
            scale: 0.95,
        }),
    };
    return (
        <section className="relative h-[80vh] min-h-[600px] w-full overflow-hidden bg-bg/90">
            <AnimatePresence initial={false} custom={direction} mode="wait">
                <motion.div
                    key={currentSlide}
                    custom={direction}
                    variants={variants}
                    initial="enter"
                    animate="center"
                    exit="exit"
                    transition={{
                        opacity: {
                            duration: 0.5,
                        },
                        scale: {
                            duration: 0.8,
                        },
                    }}
                    className="absolute inset-0"
                >
                    <img
                        src={slides[currentSlide].image}
                        alt={slides[currentSlide].title}
                        className="w-full h-full object-cover"
                    />

                    <div className="absolute inset-0 bg-overlay-shade " />
                </motion.div>
            </AnimatePresence>

            <div className="relative h-full container mx-auto px-4 md:px-6 flex items-center">
                <motion.div
                    key={`content-${currentSlide}`}
                    initial={{
                        opacity: 0,
                        y: 30,
                    }}
                    animate={{
                        opacity: 1,
                        y: 0,
                    }}
                    transition={{
                        duration: 0.8,
                        delay: 1.2,
                    }}
                    className="max-w-2xl text-white"
                >
                    <span className="uppercase tracking-[0.2em] text-sm font-semibold mb-4 block">
                        {slides[currentSlide].subtitle}
                    </span>
                    <h1 className="font-display text-5xl md:text-7xl font-bold leading-tight mb-6">
                        {slides[currentSlide].title}
                    </h1>
                    <p className="text-lg md:text-xl text-white/90 mb-8 max-w-lg">
                        {slides[currentSlide].description}
                    </p>
                    <Button
                        size="lg"
                        className="bg-white text-primary hover:bg-white/90"
                        asChild
                    >
                        <Link href={slides[currentSlide].link}>
                            {slides[currentSlide].cta}
                        </Link>
                    </Button>
                </motion.div>
            </div>

            {/* Navigation Arrows */}
            <button
                onClick={prevSlide}
                className="hidden lg:flex absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 items-center justify-center bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-colors z-10"
                aria-label="Previous slide"
            >
                <ChevronLeft className="h-6 w-6 text-white" />
            </button>
            <button
                onClick={nextSlide}
                className="hidden lg:flex absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 items-center justify-center bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-colors z-10"
                aria-label="Next slide"
            >
                <ChevronRight className="h-6 w-6 text-white" />
            </button>

            {/* Dots */}
            <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                {slides.map((slide: any, index: number) => (
                    <button
                        key={index}
                        onClick={() => goToSlide(index)}
                        className={`h-2 transition-all ${index === currentSlide ? "w-8 bg-white" : "w-2 bg-white/50 hover:bg-white/70"}`}
                        aria-label={`Go to slide ${index + 1}`}
                    />
                ))}
            </div>
        </section>
    );
}
