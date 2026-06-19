import { BestSellersCarousel } from "@/Components/home/BestSellersCarousel";
import { HeroCarousel } from "@/Components/home/HeroCarousel";
import { InstagramGrid } from "@/Components/home/InstagramGrid";
import { TestimonialsSection } from "@/Components/home/TestimonialsSection";
import { ProductCard } from "@/Components/product/ProductCard";
import { Button } from "@/Components/ui/Button";
import { categories, products } from "@/data/products";
import AppLayout from "@/Layouts/AppLayout";
import { PageProps } from "@/types";
import { Head, Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, Clock, RefreshCw, ShieldCheck, Truck } from "lucide-react";

export default function Home({ auth }: PageProps<{}>) {
    const newProducts = products.filter((p) => p.isNew).slice(0, 4);
    const bestSellers = products.slice(0, 4);

    return (
        <AppLayout>
            <Head title="Home" />
            <div className="flex flex-col w-full">
                {/* Hero Carousel */}
                <HeroCarousel />

                {/* Categories Showcase */}
                <section className="py-20 bg-bg">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="text-center mb-12">
                            <h2 className="font-display text-3xl md:text-4xl font-bold text-fg mb-4">
                                SHOP BY CATEGORY
                            </h2>
                            <p className="text-muted max-w-2xl mx-auto">
                                Discover our wide range of premium clothing
                                designed for the modern lifestyle.
                            </p>
                        </div>

                        {/* Desktop: flex row | Tablet: 4-col grid | Mobile: 2x2 grid */}
                        <div
                            className="
            grid grid-cols-2 gap-6
            sm:grid-cols-4 sm:gap-8 sm:max-w-xl sm:mx-auto
            lg:flex lg:justify-center lg:gap-24 lg:max-w-none
        "
                        >
                            {categories.map((category) => (
                                <Link
                                    key={category.name}
                                    href={`/shop?category=${category.name.toLowerCase()}`}
                                    className="group flex flex-col items-center text-center gap-3"
                                >
                                    <div
                                        className="
                        relative rounded-full overflow-hidden bg-muted/10
                        w-[110px] h-[110px]
                        sm:w-[100px] sm:h-[100px]
                        lg:w-[140px] lg:h-[140px]
                    "
                                    >
                                        <img
                                            src={category.image}
                                            alt={category.name}
                                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-fg text-sm group-hover:text-primary transition-colors leading-tight">
                                            {category.name}
                                        </h3>
                                        <p className="text-xs text-muted mt-0.5">
                                            {category.count} Products
                                        </p>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>

                {/* New Products */}
                <section className="py-20 bg-card">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="flex items-end justify-between mb-10">
                            <div>
                                <span className="text-accent font-semibold tracking-wider uppercase text-sm mb-2 block">
                                    Just Landed
                                </span>
                                <h2 className="font-display text-3xl md:text-4xl font-bold text-fg">
                                    OUR NEWEST PRODUCTS
                                </h2>
                            </div>
                            <Link
                                href="/shop"
                                className="hidden md:flex items-center gap-2 text-link-main font-semibold hover:text-link-hover transition-colors"
                            >
                                View All <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                            {newProducts.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                />
                            ))}
                        </div>

                        <div className="mt-10 text-center md:hidden">
                            <Button variant="outline" fullWidth asChild>
                                <Link href="/shop">View All Products</Link>
                            </Button>
                        </div>
                    </div>
                </section>

                {/* Sale Banner */}
                <section className="py-24 relative overflow-hidden bg-primary text-primary-fg">
                    <div className="absolute inset-0 opacity-20">
                        <img
                            src="https://images.unsplash.com/photo-1550639525-c97d455acf70?auhref=format&fit=crop&q=80&w=2000"
                            alt="Texture"
                            className="w-full h-full object-cover"
                        />
                    </div>
                    <div className="container mx-auto px-4 md:px-6 relative z-10 text-center">
                        <motion.div
                            initial={{
                                opacity: 0,
                                scale: 0.9,
                            }}
                            whileInView={{
                                opacity: 1,
                                scale: 1,
                            }}
                            viewport={{
                                once: true,
                            }}
                            transition={{
                                duration: 0.5,
                            }}
                        >
                            <h2 className="font-display text-4xl md:text-6xl font-bold mb-6">
                                SALE UP TO 40% OFF
                            </h2>
                            <p className="text-lg md:text-xl text-primary-fg/80 mb-10 max-w-2xl mx-auto">
                                Upgrade your wardrobe with our premium
                                essentials at unbeatable prices. Limited time
                                offer.
                            </p>
                            <Button size="lg" variant="accent" asChild>
                                <Link href="/shop?sale=true">
                                    Shop The Sale
                                </Link>
                            </Button>
                        </motion.div>
                    </div>
                </section>

                {/* Feature Strip */}
                <section className="py-12 border-y border-border bg-bg">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                            <div className="flex flex-col items-center justify-center space-y-3">
                                <Truck
                                    className="h-8 w-8 text-primary"
                                    strokeWidth={1.5}
                                />
                                <div>
                                    <h4 className="font-semibold text-fg">
                                        Free Shipping
                                    </h4>
                                    <p className="text-sm text-muted">
                                        On orders over $100
                                    </p>
                                </div>
                            </div>
                            <div className="flex flex-col items-center justify-center space-y-3">
                                <RefreshCw
                                    className="h-8 w-8 text-primary"
                                    strokeWidth={1.5}
                                />
                                <div>
                                    <h4 className="font-semibold text-fg">
                                        Easy Returns
                                    </h4>
                                    <p className="text-sm text-muted">
                                        30-day return policy
                                    </p>
                                </div>
                            </div>
                            <div className="flex flex-col items-center justify-center space-y-3">
                                <ShieldCheck
                                    className="h-8 w-8 text-primary"
                                    strokeWidth={1.5}
                                />
                                <div>
                                    <h4 className="font-semibold text-fg">
                                        Secure Checkout
                                    </h4>
                                    <p className="text-sm text-muted">
                                        100% secure payment
                                    </p>
                                </div>
                            </div>
                            <div className="flex flex-col items-center justify-center space-y-3">
                                <Clock
                                    className="h-8 w-8 text-primary"
                                    strokeWidth={1.5}
                                />
                                <div>
                                    <h4 className="font-semibold text-fg">
                                        24/7 Support
                                    </h4>
                                    <p className="text-sm text-muted">
                                        Dedicated support
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Best Sellers Carousel */}
                <BestSellersCarousel />

                {/* Newsletter */}
                <section className="py-20 relative overflow-hidden bg-primary">
                    {/* SVG vector bg — paste the SVG from the mockup here with opacity-[0.07] */}

                    <div className="container mx-auto px-4 md:px-6 max-w-xl text-center relative z-10">
                        <span className="font-display text-xs tracking-[0.2em] text-accent uppercase font-semibold mb-3 block">
                            Exclusive Offers
                        </span>
                        <h2 className="font-display text-4xl font-bold text-white mb-4">
                            JOIN OUR{" "}
                            <span className="text-accent">NEWSLETTER</span>
                        </h2>
                        <p className="text-white/50 mb-9 text-sm leading-relaxed">
                            Subscribe to get special offers, free giveaways, and
                            once-in-a-lifetime deals.
                        </p>
                        <form
                            className="flex max-w-md mx-auto"
                            onSubmit={(e) => e.preventDefault()}
                        >
                            <input
                                type="email"
                                placeholder="Enter your email address"
                                className="flex-1 h-12 px-5 bg-white/7 border border-white/18 border-r-0 text-white placeholder:text-white/30 focus:outline-none focus:border-accent text-sm"
                                required
                            />
                            <Button
                                type="submit"
                                className="h-12 sm:px-7 px-2 bg-accent hover:bg-accent-800 text-white font-display tracking-widest text-sm rounded-none"
                            >
                                Subscribe
                            </Button>
                        </form>
                    </div>
                </section>

                {/* Testimonials */}
                <TestimonialsSection />

                {/* Instagram Grid */}
                <InstagramGrid />
            </div>
        </AppLayout>
    );
}
