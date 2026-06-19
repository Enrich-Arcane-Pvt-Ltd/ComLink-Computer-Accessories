import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronDown, Search } from "lucide-react";
import { useState } from "react";

interface FAQItem {
    id: string;
    question: string;
    answer: string;
    category: "Orders" | "Shipping" | "Returns" | "Payments" | "Account";
}

interface FAQsProps {
    faqs: FAQItem[];
    title?: string;
}

export default function FAQs({ faqs = [], title = "FAQs" }: FAQsProps) {
    const [searchQuery, setSearchQuery] = useState("");
    const [openId, setOpenId] = useState<string | null>("1");

    const categories = Array.from(new Set(faqs.map((faq) => faq.category)));

    const filteredFaqs = faqs.filter(
        (faq) =>
            faq.question.toLowerCase().includes(searchQuery.toLowerCase()) ||
            faq.answer.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    return (
        <AppLayout>
            <Head title={title} />

            <div className="bg-bg min-h-screen">
                {/* Page Header */}
                <div className="bg-muted/10 py-16 text-center">
                    <h1 className="font-display text-4xl font-bold text-fg mb-4 uppercase tracking-wide">
                        {title}
                    </h1>
                    <p className="text-muted max-w-2xl mx-auto px-4 mb-8">
                        Find answers to common questions about our products,
                        shipping, returns, and more.
                    </p>

                    <div className="max-w-md mx-auto px-4 relative">
                        <input
                            type="text"
                            placeholder="Search FAQs..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full h-14 pl-12 pr-4 border border-border bg-bg text-fg focus:outline-none focus:border-primary shadow-sm"
                        />
                        <Search className="absolute left-8 top-1/2 -translate-y-1/2 h-5 w-5 text-muted" />
                    </div>
                </div>

                <div className="container mx-auto px-4 md:px-6 py-16 max-w-3xl">
                    {searchQuery ? (
                        /* --- Search Results State --- */
                        <div className="space-y-4">
                            <h3 className="font-bold text-fg mb-6">
                                Search Results ({filteredFaqs.length})
                            </h3>
                            {filteredFaqs.length > 0 ? (
                                filteredFaqs.map((faq) => (
                                    <div
                                        key={faq.id}
                                        className="border border-border bg-card overflow-hidden"
                                    >
                                        <button
                                            onClick={() =>
                                                setOpenId(
                                                    openId === faq.id
                                                        ? null
                                                        : faq.id,
                                                )
                                            }
                                            className="w-full flex items-center justify-between p-6 text-left focus:outline-none"
                                        >
                                            <span className="font-semibold text-fg pr-8">
                                                {faq.question}
                                            </span>
                                            <ChevronDown
                                                className={`h-5 w-5 text-muted transition-transform duration-300 flex-shrink-0 ${openId === faq.id ? "rotate-180 text-primary" : ""}`}
                                            />
                                        </button>
                                        <AnimatePresence>
                                            {openId === faq.id && (
                                                <motion.div
                                                    initial={{
                                                        height: 0,
                                                        opacity: 0,
                                                    }}
                                                    animate={{
                                                        height: "auto",
                                                        opacity: 1,
                                                    }}
                                                    exit={{
                                                        height: 0,
                                                        opacity: 0,
                                                    }}
                                                    transition={{
                                                        duration: 0.3,
                                                    }}
                                                >
                                                    <div className="px-6 pb-6 text-muted leading-relaxed border-t border-border pt-4 bg-muted/5">
                                                        {faq.answer}
                                                    </div>
                                                </motion.div>
                                            )}
                                        </AnimatePresence>
                                    </div>
                                ))
                            ) : (
                                <p className="text-muted text-center py-8">
                                    No results found for "{searchQuery}". Please
                                    try another term.
                                </p>
                            )}
                        </div>
                    ) : (
                        /* --- Default Grouped Categories State --- */
                        <div className="space-y-12">
                            {categories.map((category) => {
                                const categoryFaqs = faqs.filter(
                                    (f) => f.category === category,
                                );
                                return (
                                    <div key={category}>
                                        <h2 className="font-display text-2xl font-bold text-fg mb-6 pb-2 border-b border-border uppercase tracking-wider">
                                            {category}
                                        </h2>
                                        <div className="space-y-4">
                                            {categoryFaqs.map((faq) => (
                                                <div
                                                    key={faq.id}
                                                    className="border border-border bg-card overflow-hidden"
                                                >
                                                    <button
                                                        onClick={() =>
                                                            setOpenId(
                                                                openId ===
                                                                    faq.id
                                                                    ? null
                                                                    : faq.id,
                                                            )
                                                        }
                                                        className="w-full flex items-center justify-between p-6 text-left focus:outline-none hover:bg-muted/5 transition-colors"
                                                    >
                                                        <span className="font-semibold text-fg pr-8">
                                                            {faq.question}
                                                        </span>
                                                        <ChevronDown
                                                            className={`h-5 w-5 text-muted transition-transform duration-300 flex-shrink-0 ${openId === faq.id ? "rotate-180 text-primary" : ""}`}
                                                        />
                                                    </button>
                                                    <AnimatePresence>
                                                        {openId === faq.id && (
                                                            <motion.div
                                                                initial={{
                                                                    height: 0,
                                                                    opacity: 0,
                                                                }}
                                                                animate={{
                                                                    height: "auto",
                                                                    opacity: 1,
                                                                }}
                                                                exit={{
                                                                    height: 0,
                                                                    opacity: 0,
                                                                }}
                                                                transition={{
                                                                    duration: 0.3,
                                                                }}
                                                            >
                                                                <div className="px-6 pb-6 text-muted leading-relaxed border-t border-border pt-4 bg-muted/5">
                                                                    {faq.answer}
                                                                </div>
                                                            </motion.div>
                                                        )}
                                                    </AnimatePresence>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
