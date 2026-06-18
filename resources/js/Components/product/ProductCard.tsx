import { useCart } from "@/contexts/CartContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { Link } from "@inertiajs/react";
import { motion } from "framer-motion";
import { Eye, Heart, ShoppingCart } from "lucide-react";
import React from "react";
import { Product } from "../../data/products";
import { Badge } from "../ui/Badge";
import { Button } from "../ui/Button";
interface ProductCardProps {
    product: Product;
}
export function ProductCard({ product }: ProductCardProps) {
    const { addToCart } = useCart();
    const { toggleWishlist, isInWishlist } = useWishlist();
    const wishlisted = isInWishlist(product.id);
    const handleWishlist = (e: React.MouseEvent) => {
        e.preventDefault();
        toggleWishlist(product);
    };
    const handleAddToCart = (e: React.MouseEvent) => {
        e.preventDefault();
        addToCart({
            productId: product.id,
            name: product.name,
            price:
                product.isSale && product.originalPrice
                    ? product.price
                    : product.price,
            image: product.images[0],
            quantity: 1,
            size: product.sizes[0],
            color: product.colors[0], // Default to first color
        });
    };
    return (
        <motion.div
            className="group relative flex flex-col bg-card"
            initial={{
                opacity: 0,
                y: 20,
            }}
            whileInView={{
                opacity: 1,
                y: 0,
            }}
            viewport={{
                once: true,
            }}
            transition={{
                duration: 0.4,
            }}
        >
            {/* Image Container */}
            <Link
                href={`/product/${product.id}`}
                className="relative aspect-[3/4] overflow-hidden bg-muted/10 block"
            >
                {/* Badges */}
                <div className="absolute top-3 left-3 z-10 flex flex-col gap-2">
                    {product.isNew && <Badge variant="default">NEW</Badge>}
                    {product.isSale && <Badge variant="accent">SALE</Badge>}
                </div>

                {/* Images with hover crossfade */}
                <img
                    src={product.images[0]}
                    alt={product.name}
                    className="absolute inset-0 h-full w-full object-cover transition-opacity duration-500 group-hover:opacity-0"
                />

                {product.images[1] && (
                    <img
                        src={product.images[1]}
                        alt={`${product.name} alternate view`}
                        className="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                    />
                )}

                {/* Hover Actions */}
                <div className="absolute bottom-0 left-0 right-0 flex translate-y-full items-center justify-center gap-2 bg-bg/80 p-4 backdrop-blur-sm transition-transform duration-300 group-hover:translate-y-0">
                    <Button
                        variant="secondary"
                        size="icon"
                        className="rounded-full h-10 w-10 shadow-sm"
                        onClick={handleAddToCart}
                        title="Add to Cart"
                    >
                        <ShoppingCart className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="secondary"
                        size="icon"
                        className="rounded-full h-10 w-10 shadow-sm"
                        title="Quick View"
                    >
                        <Eye className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="secondary"
                        size="icon"
                        className="rounded-full h-10 w-10 shadow-sm"
                        title="Add to Wishlist"
                        onClick={handleWishlist}
                    >
                        <Heart
                            className={`h-4 w-4 ${wishlisted ? "fill-accent text-accent" : ""}`}
                        />
                    </Button>
                </div>
            </Link>

            {/* Product Info */}
            <div className="flex flex-col pt-4 pb-2">
                <div className="flex items-center justify-between gap-2">
                    <span className="text-xs text-muted uppercase tracking-wider">
                        {product.category}
                    </span>
                    <div className="flex items-center gap-1 text-xs text-muted">
                        <span className="text-yellow-500">★</span>
                        <span>{product.rating}</span>
                    </div>
                </div>

                <Link href={`/product/${product.id}`} className="mt-1 block">
                    <h3 className="font-medium text-fg line-clamp-1 hover:text-primary transition-colors">
                        {product.name}
                    </h3>
                </Link>

                <div className="mt-2 flex items-center gap-2">
                    <span className="font-semibold text-fg">
                        ${product.price.toFixed(2)}
                    </span>
                    {product.isSale && product.originalPrice && (
                        <span className="text-sm text-muted line-through">
                            ${product.originalPrice.toFixed(2)}
                        </span>
                    )}
                </div>
            </div>
        </motion.div>
    );
}
