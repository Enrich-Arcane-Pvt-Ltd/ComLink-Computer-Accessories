import { Link } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { Search, X } from "lucide-react";
import { useEffect, useState } from "react";
import { products } from "../../data/products";
interface SearchModalProps {
    isOpen: boolean;
    onClose: () => void;
}
export function SearchModal({ isOpen, onClose }: SearchModalProps) {
    const [query, setQuery] = useState("");
    const [results, setResults] = useState(products.slice(0, 6));
    useEffect(() => {
        if (!query.trim()) {
            setResults(products.slice(0, 6));
            return;
        }
        const filtered = products.filter(
            (p) =>
                p.name.toLowerCase().includes(query.toLowerCase()) ||
                p.category.toLowerCase().includes(query.toLowerCase()) ||
                p.brand.toLowerCase().includes(query.toLowerCase()),
        );
        setResults(filtered.slice(0, 8));
    }, [query]);
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "unset";
            setQuery("");
        }
        return () => {
            document.body.style.overflow = "unset";
        };
    }, [isOpen]);
    return (
        <AnimatePresence>
            {isOpen && (
                <>
                    <motion.div
                        initial={{
                            opacity: 0,
                        }}
                        animate={{
                            opacity: 1,
                        }}
                        exit={{
                            opacity: 0,
                        }}
                        className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50"
                        onClick={onClose}
                    />

                    <motion.div
                        initial={{
                            opacity: 0,
                            y: -20,
                        }}
                        animate={{
                            opacity: 1,
                            y: 0,
                        }}
                        exit={{
                            opacity: 0,
                            y: -20,
                        }}
                        className="fixed top-0 left-0 right-0 z-50 bg-bg border-b border-border shadow-2xl"
                    >
                        <div className="container mx-auto px-4 md:px-6 py-5">
                            {/* Mini header */}
                            <div className="flex items-center justify-between pb-4 mb-4 border-b border-border">
                                <span className="font-display text-xl font-bold tracking-widest text-primary dark:text-fg">
                                    MILANA
                                </span>
                                <span className="text-xs uppercase tracking-[0.2em] text-muted">
                                    Search the store
                                </span>
                                <button
                                    onClick={onClose}
                                    className="text-muted hover:text-fg transition-colors"
                                    aria-label="Close search"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="relative mb-6">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted" />
                                <input
                                    type="text"
                                    value={query}
                                    onChange={(e) => setQuery(e.target.value)}
                                    placeholder="Search products..."
                                    className="w-full h-14 pl-12 pr-4 bg-card border border-border text-fg text-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    autoFocus
                                />
                            </div>

                            {results.length > 0 ? (
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 max-h-[60vh] overflow-y-auto pb-4">
                                    {results.map((product) => (
                                        <Link
                                            key={product.id}
                                            href={`/product/${product.id}`}
                                            onClick={onClose}
                                            className="flex gap-3 p-3 hover:bg-card border border-transparent hover:border-border transition-colors"
                                        >
                                            <img
                                                src={product.images[0]}
                                                alt={product.name}
                                                className="w-16 h-20 object-cover flex-shrink-0"
                                            />

                                            <div className="flex-grow min-w-0">
                                                <h4 className="text-sm font-medium text-fg line-clamp-2">
                                                    {product.name}
                                                </h4>
                                                <p className="text-xs text-muted mt-1">
                                                    {product.category}
                                                </p>
                                                <p className="text-sm font-semibold text-primary mt-1">
                                                    Rs. {Number(product.price || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                </p>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12 text-muted">
                                    No products found for "{query}"
                                </div>
                            )}
                        </div>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
}
