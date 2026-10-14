import { BestSellersCarousel } from "@/Components/home/BestSellersCarousel";
import { HeroCarousel } from "@/Components/home/HeroCarousel";
import { InstagramGrid } from "@/Components/home/InstagramGrid";
import { NewProductsCarousel } from "@/Components/home/NewProductsCarousel";
import { TestimonialsSection } from "@/Components/home/TestimonialsSection";
import { ProductCard } from "@/Components/product/ProductCard";
import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { PageProps } from "@/types";
import { Head, Link, useForm } from "@inertiajs/react";
import { motion } from "framer-motion";
import { ArrowRight, Clock, RefreshCw, ShieldCheck, Truck } from "lucide-react";
import { useState } from "react";

interface CategoryProp {
    name: string;
    count: number;
    image: string;
}

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

interface Testimonial {
    id: string;
    name: string;
    location: string;
    avatar: string;
    rating: number;
    quote: string;
}
interface Slide {
    id: number;
    image: string;
    title: string;
    subtitle: string;
    description: string;
    cta: string;
    link: string;
}
interface HomeProps extends PageProps {
    products: Product[];
    categories: CategoryProp[];
    testimonials: Testimonial[];
    slides: Slide[];
    instagramImages: string[];
    title: string;
}

export default function Home({
    auth,
    products = [],
    categories = [],
    testimonials = [],
    slides = [],
    instagramImages = [],
    title = "Home",
}: HomeProps) {
    const newProducts = products.filter((p) => p.isNew).slice(0, 4);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        username_hp: "",
    });
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const handleNewsletterSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // අලුතින් submit කරන විට පරණ මැසේජ් clear කරන්න
        setSuccessMessage(null);
        setErrorMessage(null);

        post(route("newsletter.subscribe"), {
            preserveScroll: true,
            onSuccess: (page) => {
                reset("email");
                setSuccessMessage(
                    "Thank you for subscribing to our newsletter!",
                );

                // තත්පර 5කට (5000ms) පසු සක්සස් මැසේජ් එක අයින් කරන්න
                setTimeout(() => {
                    setSuccessMessage(null);
                }, 5000);
            },
            onError: (errors) => {
                if (errors.email) {
                    setErrorMessage(errors.email);

                    // තත්පර 5කට (5000ms) පසු एरර් මැසේජ් එක අයින් කරන්න
                    setTimeout(() => {
                        setErrorMessage(null);
                    }, 5000);
                }
            },
        });
    };
    return (
        <AppLayout>
            <Head title={title} />
            <div className="flex flex-col w-full">
                {/* Hero Carousel */}
                <HeroCarousel slides={slides} />

                {/* Categories Showcase */}
                <section className="py-20 bg-bg">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="text-center mb-12">
                            <h2 className="font-display text-3xl md:text-4xl font-bold text-fg mb-4">
                                SHOP BY CATEGORY
                            </h2>
                            <p className="text-muted max-w-2xl mx-auto">
                                Explore our wide range of laptops, PC components, and accessories
                                built for gaming, work, and everyday performance.
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
                            {categories.length > 0 ? (
                                categories.map((category) => (
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
                                ))
                            ) : (
                                <p className="text-muted col-span-full text-center py-4">
                                    No categories available at the moment.
                                </p>
                            )}
                        </div>
                    </div>
                </section>

                {/* New Products Carousel */}
                <NewProductsCarousel />

                {/* Best Sellers Carousel */}
                <BestSellersCarousel />

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
                {/* Newsletter */}
                <section className="py-20 relative overflow-hidden bg-primary">
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
                            className="flex flex-col max-w-md mx-auto"
                            onSubmit={handleNewsletterSubmit}
                        >
                            <div className="flex w-full">
                                {/* Honeypot field - invisible to real users */}
                                <div className="hidden" aria-hidden="true">
                                    <input
                                        type="text"
                                        name="username_hp"
                                        value={data.username_hp || ""}
                                        onChange={(e) =>
                                            setData(
                                                "username_hp",
                                                e.target.value,
                                            )
                                        }
                                        tabIndex={-1}
                                        autoComplete="off"
                                    />
                                </div>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    placeholder="Enter your email address"
                                    className="flex-1 h-12 px-5 bg-white/5 border border-white/18 border-r-0 text-white placeholder:text-white/30 focus:outline-none focus:border-accent text-sm"
                                    required
                                    disabled={processing}
                                />
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="h-12 sm:px-7 px-2 bg-accent hover:bg-accent-800 text-white font-display tracking-widest text-sm rounded-none whitespace-nowrap"
                                >
                                    {processing ? "Joining..." : "Subscribe"}
                                </Button>
                            </div>

                            {/* Error Message - පින්තූරයේ පරිදි Input එකට යටින් පෙන්වයි (තත්පර 5කින් මැකී යයි) */}
                            {errorMessage && (
                                <motion.div
                                    initial={{ opacity: 0, y: -5 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="text-red-500 text-xs text-left mt-2 font-medium"
                                >
                                    {errorMessage}
                                </motion.div>
                            )}

                            {/* Success Message - Alert වෙනුවට කොළ පාටින් මෙතනම වැටේ (තත්පර 5කින් මැකී යයි) */}
                            {successMessage && (
                                <motion.div
                                    initial={{ opacity: 0, y: -5 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="text-emerald-400 text-xs text-left mt-2 font-medium"
                                >
                                    {successMessage}
                                </motion.div>
                            )}
                        </form>
                    </div>
                </section>

                {/* Testimonials */}
                <TestimonialsSection testimonials={testimonials} />

                {/* Instagram Grid */}
                <InstagramGrid instagramImages={instagramImages} />
            </div>
        </AppLayout>
    );
}
