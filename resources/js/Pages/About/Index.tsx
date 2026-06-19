import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, Link } from "@inertiajs/react";
import { motion } from "framer-motion";

export default function About({ auth, title = "About Us" }: any) {
    return (
        <AppLayout>
            <Head title={title} />
            <div className="bg-bg min-h-screen">
                {/* Hero Section */}
                <section className="relative h-[60vh] min-h-[400px] w-full bg-muted/20 overflow-hidden">
                    <div className="absolute inset-0">
                        <img
                            src="https://images.unsplash.com/photo-1550639525-c97d455acf70?auto=format&fit=crop&q=80&w=2000"
                            alt="MILANA Brand"
                            className="w-full h-full object-cover object-center"
                        />

                        <div className="absolute inset-0 bg-black/40" />
                    </div>

                    <div className="relative h-full container mx-auto px-4 md:px-6 flex items-center justify-center text-center">
                        <motion.div
                            className="max-w-3xl text-white"
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
                            }}
                        >
                            <h1 className="font-display text-5xl md:text-7xl font-bold leading-tight mb-6">
                                OUR STORY
                            </h1>
                            <p className="text-lg md:text-xl text-white/90">
                                Redefining modern essentials with a focus on
                                quality, fit, and timeless design.
                            </p>
                        </motion.div>
                    </div>
                </section>
                {/* Mission Section */}
                <section className="py-24">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="flex flex-col md:flex-row gap-12 items-center">
                            <div className="md:w-1/2">
                                <img
                                    src="https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=1000"
                                    alt="Design Process"
                                    className="w-full h-full object-cover lg:aspect-[16/9] sm:aspect-auto aspect-square"
                                />
                            </div>
                            <div className="md:w-1/2 space-y-6">
                                <span className="text-accent font-semibold tracking-wider uppercase text-sm">
                                    The Mission
                                </span>
                                <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                                    ELEVATING THE EVERYDAY
                                </h2>
                                <p className="text-muted leading-relaxed">
                                    Founded in 2026, MILANA was born out of a
                                    simple frustration: the inability to find
                                    high-quality, well-fitting basics that
                                    didn't cost a fortune. We believe that the
                                    clothes you wear every day should be the
                                    best clothes in your closet.
                                </p>
                                <p className="text-muted leading-relaxed">
                                    Our approach is simple. We focus on premium
                                    fabrics, meticulous construction, and fits
                                    that flatter. We don't chase fleeting
                                    trends; instead, we design timeless pieces
                                    that form the foundation of a modern
                                    wardrobe.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* Stats/Values */}
                <section className="py-20 bg-card border-y border-border">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 text-center">
                            <div className="space-y-2">
                                <h3 className="font-display text-4xl font-bold text-primary-bg">
                                    100%
                                </h3>
                                <p className="text-sm font-medium text-fg uppercase tracking-wider">
                                    Organic Cotton
                                </p>
                            </div>
                            <div className="space-y-2">
                                <h3 className="font-display text-4xl font-bold text-primary-bg">
                                    50+
                                </h3>
                                <p className="text-sm font-medium text-fg uppercase tracking-wider">
                                    Artisans
                                </p>
                            </div>
                            <div className="space-y-2">
                                <h3 className="font-display text-4xl font-bold text-primary-bg">
                                    Zero
                                </h3>
                                <p className="text-sm font-medium text-fg uppercase tracking-wider">
                                    Harmful Chemicals
                                </p>
                            </div>
                            <div className="space-y-2">
                                <h3 className="font-display text-4xl font-bold text-primary-bg">
                                    24/7
                                </h3>
                                <p className="text-sm font-medium text-fg uppercase tracking-wider">
                                    Customer Support
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* Vision Section */}
                <section className="py-24">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="flex flex-col md:flex-row gap-12 items-center">
                            <div className="md:w-1/2 space-y-6">
                                <span className="text-accent font-semibold tracking-wider uppercase text-sm">
                                    The Vision
                                </span>
                                <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                                    ELEVATING THE EVERYDAY
                                </h2>
                                <p className="text-muted leading-relaxed">
                                    Founded in 2026, MILANA was born out of a
                                    simple frustration: the inability to find
                                    high-quality, well-fitting basics that
                                    didn't cost a fortune. We believe that the
                                    clothes you wear every day should be the
                                    best clothes in your closet.
                                </p>
                                <p className="text-muted leading-relaxed">
                                    Our approach is simple. We focus on premium
                                    fabrics, meticulous construction, and fits
                                    that flatter. We don't chase fleeting
                                    trends; instead, we design timeless pieces
                                    that form the foundation of a modern
                                    wardrobe.
                                </p>
                            </div>
                            <div className="md:w-1/2">
                                <img
                                    src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=1000"
                                    alt="Design Process"
                                    className="w-full h-full object-cover lg:aspect-[16/9] sm:aspect-auto aspect-square"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="py-24 text-center">
                    <div className="container mx-auto px-4 max-w-2xl">
                        <h2 className="font-display text-3xl md:text-4xl font-bold text-fg mb-6">
                            EXPERIENCE THE DIFFERENCE
                        </h2>
                        <p className="text-muted mb-8">
                            Discover our collection of premium essentials
                            designed for the modern lifestyle.
                        </p>
                        <Button size="lg" asChild>
                            <Link href={route("home")}>
                                Shop The Collection
                            </Link>
                        </Button>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
