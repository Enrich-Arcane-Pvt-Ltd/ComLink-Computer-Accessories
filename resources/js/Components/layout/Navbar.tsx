import { useCart } from "@/contexts/CartContext";
import { useTheme } from "@/contexts/ThemeContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { cn } from "@/utils/cn";
import { Link, usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import {
    Heart,
    LayoutDashboard,
    LogOut,
    Menu,
    Moon,
    Search,
    ShoppingBag,
    Sun,
    Trash2,
    User,
    X,
} from "lucide-react";
import React, { Children, useEffect, useRef, useState } from "react";
import { SearchModal } from "../search/SearchModal";
import { Button } from "../ui/Button";

export default function Navbar() {
    const { auth } = usePage().props as any;
    const user = auth?.user ?? null;

    const { url } = usePage();

    // Role-based routes
    const isAdmin = user?.role === "admin" || user?.role === "super-admin";
    const dashboardRoute = isAdmin ? "/admin" : "/";
    const profileRoute = "/profile";

    const [isScrolled, setIsScrolled] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const [isUserDropdownOpen, setIsUserDropdownOpen] = useState(false);
    const [openDropdown, setOpenDropdown] = useState<
        "cart" | "wishlist" | null
    >(null);

    const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const userDropdownRef = useRef<HTMLDivElement>(null);

    const {
        items: cartItems,
        totalItems,
        totalPrice,
        removeFromCart,
    } = useCart();
    const {
        items: wishlistItems,
        count: wishlistCount,
        removeFromWishlist,
    } = useWishlist();
    const { theme, toggleTheme } = useTheme();

    useEffect(() => {
        const handleScroll = () => setIsScrolled(window.scrollY > 20);
        window.addEventListener("scroll", handleScroll);
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    useEffect(() => {
        setIsMobileMenuOpen(false);
        setOpenDropdown(null);
        setIsUserDropdownOpen(false);
    }, [url]);

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (
                userDropdownRef.current &&
                !userDropdownRef.current.contains(event.target as Node)
            ) {
                setIsUserDropdownOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const openMenu = (menu: "cart" | "wishlist") => {
        if (closeTimer.current) clearTimeout(closeTimer.current);
        setOpenDropdown(menu);
    };

    const scheduleClose = () => {
        if (closeTimer.current) clearTimeout(closeTimer.current);
        closeTimer.current = setTimeout(() => setOpenDropdown(null), 180);
    };

    const handleSearchClick = () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
        setIsSearchOpen(true);
    };

    const navLinks = [
        {
            name: "Home",
            path: "/",
        },
        {
            name: "Shop",
            path: "/shop",
        },
        {
            name: "Men",
            path: "/shop?category=men",
        },
        {
            name: "Women",
            path: "/shop?category=women",
        },
        {
            name: "Blog",
            path: "/blog",
        },
    ];

    return (
        <header
            className={cn(
                "sticky top-0 z-40 w-full transition-all duration-300 border-b border-transparent",
                isScrolled
                    ? "bg-white/10 backdrop-blur-lg border-border shadow-md py-3 border-0"
                    : "bg-bg py-5",
            )}
        >
            <div className="container mx-auto px-4 md:px-6">
                <div className="flex items-center justify-between">
                    {/* Mobile Menu Toggle */}
                    <button
                        className="md:hidden p-2 -ml-2 text-fg cursor-pointer"
                        onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                        aria-label="Toggle menu"
                    >
                        {isMobileMenuOpen ? (
                            <X className="h-6 w-6" />
                        ) : (
                            <Menu className="h-6 w-6" />
                        )}
                    </button>

                    {/* Logo */}
                    <Link href="/" className="flex-shrink-0 -my-2">
                        <img
                            src={
                                theme === "dark"
                                    ? "/images/logo-white.png"
                                    : "/images/logo.png"
                            }
                            alt="Wiki Store Logo"
                            className="h-8 md:h-12 w-auto object-contain transition-transform duration-300 hover:scale-105"
                        />
                    </Link>

                    {/* Desktop Nav */}
                    <nav className="hidden md:flex items-center gap-8">
                        {navLinks.map((link) => {
                            const basePath = link.path.split("?")[0];

                            let isActive = false;

                            if (basePath === "/") {
                                isActive = url === "/";
                            } else {
                                const regex = new RegExp(
                                    `^${basePath}(/|\\?|$)`,
                                );
                                isActive = regex.test(url);
                            }

                            return (
                                <Link
                                    key={link.name}
                                    href={link.path}
                                    className={cn(
                                        "text-sm font-medium uppercase tracking-wider transition-colors hover:text-accent",
                                        isActive ? "text-accent" : "text-fg",
                                    )}
                                >
                                    {link.name}
                                </Link>
                            );
                        })}
                    </nav>

                    {/* Icons & Auth */}
                    <div className="flex items-center gap-3 md:gap-5">
                        <button
                            onClick={toggleTheme}
                            className="p-1 text-fg hover:text-accent transition-colors hidden sm:block cursor-pointer"
                            aria-label="Toggle theme"
                        >
                            {theme === "dark" ? (
                                <Sun className="h-5 w-5" />
                            ) : (
                                <Moon className="h-5 w-5" />
                            )}
                        </button>

                        <button
                            onClick={handleSearchClick}
                            className="p-1 text-fg hover:text-accent transition-colors cursor-pointer"
                            aria-label="Search"
                        >
                            <Search className="h-5 w-5" />
                        </button>

                        {/* --- AUTHENTICATION AREA --- */}
                        {user ? (
                            /* ලොග් වී ඇති අය සඳහා Dropdown එක */
                            <div className="relative" ref={userDropdownRef}>
                                <button
                                    onClick={() =>
                                        setIsUserDropdownOpen(
                                            !isUserDropdownOpen,
                                        )
                                    }
                                    className="p-1 text-fg hover:text-accent transition-colors flex items-center gap-1 focus:outline-none cursor-pointer"
                                    aria-label="User Menu"
                                >
                                    <User className="h-5 w-5" />
                                    <span className="text-xs font-semibold hidden md:inline max-w-[80px] truncate">
                                        {user.first_name}
                                    </span>
                                </button>

                                <AnimatePresence>
                                    {isUserDropdownOpen && (
                                        <motion.div
                                            initial={{ opacity: 0, y: 8 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            exit={{ opacity: 0, y: 8 }}
                                            className="absolute right-0 mt-3 w-48 bg-bg border border-border shadow-xl z-50 py-2 font-sans"
                                        >
                                            {/* <div className="px-4 py-2 border-b border-border mb-1">
                                                <p className="text-xs text-muted">
                                                    Signed in as
                                                </p>
                                                <p className="text-sm font-semibold text-fg truncate">
                                                    {user.email}
                                                </p>
                                            </div> */}
                                            {/* Signed in as section — dropdown top */}
                                            <div className="px-4 py-3 border-b border-border">
                                                <div className="flex items-center gap-3">
                                                    <img
                                                        src={
                                                            auth.user.avatar_url
                                                                ? `/storage/${auth.user.avatar_url}`
                                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                                      (
                                                                          auth
                                                                              .user
                                                                              .first_name +
                                                                          " " +
                                                                          auth
                                                                              .user
                                                                              .last_name
                                                                      ).trim(),
                                                                  )}&color=7F9CF5&background=EBF4FF`
                                                        }
                                                        alt={
                                                            auth.user.first_name
                                                        }
                                                        className="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                                    />
                                                    <div className="min-w-0">
                                                        <p className="text-sm font-semibold text-fg truncate">
                                                            {
                                                                auth.user
                                                                    .first_name
                                                            }{" "}
                                                            {
                                                                auth.user
                                                                    .last_name
                                                            }
                                                        </p>
                                                        <p className="text-xs text-muted truncate">
                                                            {auth.user.email}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            {isAdmin && (
                                                <a
                                                    href={dashboardRoute}
                                                    className="flex items-center gap-2 px-4 py-2 text-sm text-fg hover:bg-muted/10 transition-colors uppercase tracking-wider font-medium font-display cursor-pointer"
                                                >
                                                    <LayoutDashboard className="h-4 w-4 text-accent" />
                                                    Admin Panel
                                                </a>
                                            )}

                                            <Link
                                                href={profileRoute}
                                                className="flex items-center gap-2 px-4 py-2 text-sm text-fg hover:bg-muted/10 transition-colors cursor-pointer"
                                            >
                                                <User className="h-4 w-4" />
                                                My Profile
                                            </Link>

                                            <Link
                                                href="/logout"
                                                method="post"
                                                as="button"
                                                className="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors text-left cursor-pointer"
                                            >
                                                <LogOut className="h-4 w-4" />
                                                Logout
                                            </Link>
                                        </motion.div>
                                    )}
                                </AnimatePresence>
                            </div>
                        ) : (
                            /* ලොග් වී නැති අය සඳහා බටන්ස් */
                            <div className="hidden sm:flex items-center gap-2">
                                <Link
                                    href="/login"
                                    className="text-xs uppercase tracking-wider font-semibold text-fg hover:text-accent px-2 py-1 transition-colors cursor-pointer"
                                >
                                    Login
                                </Link>
                                <span className="text-border">|</span>
                                <Link
                                    href="/register"
                                    className="text-xs uppercase tracking-wider font-semibold text-fg hover:text-accent px-2 py-1 transition-colors cursor-pointer"
                                >
                                    Register
                                </Link>
                            </div>
                        )}

                        {/* Wishlist Dropdown */}
                        <div
                            className="relative hidden sm:block"
                            onMouseEnter={() => openMenu("wishlist")}
                            onMouseLeave={scheduleClose}
                        >
                            <Link
                                href="/wishlist"
                                className="p-1 text-fg hover:text-accent transition-colors relative block cursor-pointer"
                                aria-label="Wishlist"
                            >
                                <Heart className="h-5 w-5" />
                                {wishlistCount > 0 && (
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white">
                                        {wishlistCount}
                                    </span>
                                )}
                            </Link>
                            <AnimatePresence>
                                {openDropdown === "wishlist" && (
                                    <MiniDropdown
                                        title="Wishlist"
                                        emptyLabel="Your wishlist is empty"
                                        viewAllHref="/wishlist"
                                        viewAllLabel="View Wishlist"
                                    >
                                        {wishlistItems
                                            .slice(0, 4)
                                            .map((p: any) => (
                                                <div
                                                    key={p.id}
                                                    className="flex items-center gap-3 py-3 border-b border-border last:border-0 "
                                                >
                                                    <Link
                                                        href={`/product/${p.id}`}
                                                        className="w-12 h-14 flex-shrink-0 bg-muted/10 cursor-pointer"
                                                    >
                                                        <img
                                                            src={p.images?.[0]}
                                                            alt={p.name}
                                                            className="w-full h-full object-cover"
                                                        />
                                                    </Link>
                                                    <div className="flex-grow min-w-0">
                                                        <Link
                                                            href={`/product/${p.id}`}
                                                            className="text-sm font-medium text-fg line-clamp-1 hover:text-primary"
                                                        >
                                                            {p.name}
                                                        </Link>
                                                        <p className="text-sm text-primary font-semibold">
                                                            $
                                                            {p.price.toFixed(2)}
                                                        </p>
                                                    </div>
                                                    <button
                                                        onClick={() =>
                                                            removeFromWishlist(
                                                                p.id,
                                                            )
                                                        }
                                                        className="text-muted hover:text-accent p-1 cursor-pointer"
                                                        aria-label="Remove"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            ))}
                                    </MiniDropdown>
                                )}
                            </AnimatePresence>
                        </div>

                        {/* Cart Dropdown */}
                        <div
                            className="relative"
                            onMouseEnter={() => openMenu("cart")}
                            onMouseLeave={scheduleClose}
                        >
                            <Link
                                href="/cart"
                                className="p-1 text-fg hover:text-accent transition-colors relative block cursor-pointer"
                                aria-label="Cart"
                            >
                                <ShoppingBag className="h-5 w-5" />
                                {totalItems > 0 && (
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white">
                                        {totalItems}
                                    </span>
                                )}
                            </Link>
                            <AnimatePresence>
                                {openDropdown === "cart" && (
                                    <MiniDropdown
                                        title="Shopping Cart"
                                        emptyLabel="Your cart is empty"
                                        viewAllHref="/cart"
                                        viewAllLabel="View Cart"
                                        footer={
                                            cartItems.length > 0 ? (
                                                <div className="flex items-center justify-between text-sm font-semibold text-fg pt-3">
                                                    <span>Subtotal</span>
                                                    <span>
                                                        ${totalPrice.toFixed(2)}
                                                    </span>
                                                </div>
                                            ) : undefined
                                        }
                                        secondaryAction={
                                            cartItems.length > 0 ? (
                                                <Button
                                                    size="sm"
                                                    fullWidth
                                                    asChild
                                                >
                                                    <Link href="/checkout">
                                                        Checkout
                                                    </Link>
                                                </Button>
                                            ) : undefined
                                        }
                                    >
                                        {cartItems
                                            .slice(0, 4)
                                            .map((item: any) => (
                                                <div
                                                    key={item.id}
                                                    className="flex items-center gap-3 py-3 border-b border-border last:border-0"
                                                >
                                                    <Link
                                                        href={`/product/${item.productId}`}
                                                        className="w-12 h-14 flex-shrink-0 bg-muted/10"
                                                    >
                                                        <img
                                                            src={item.image}
                                                            alt={item.name}
                                                            className="w-full h-full object-cover"
                                                        />
                                                    </Link>
                                                    <div className="flex-grow min-w-0">
                                                        <Link
                                                            href={`/product/${item.productId}`}
                                                            className="text-sm font-medium text-fg line-clamp-1 hover:text-primary"
                                                        >
                                                            {item.name}
                                                        </Link>
                                                        <p className="text-xs text-muted">
                                                            {item.quantity} × $
                                                            {item.price.toFixed(
                                                                2,
                                                            )}
                                                        </p>
                                                    </div>
                                                    <button
                                                        onClick={() =>
                                                            removeFromCart(
                                                                item.id,
                                                            )
                                                        }
                                                        className="text-muted hover:text-accent p-1 cursor-pointer"
                                                        aria-label="Remove"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            ))}
                                    </MiniDropdown>
                                )}
                            </AnimatePresence>
                        </div>
                    </div>
                </div>
            </div>

            {/* Mobile Menu Overlay */}
            {isMobileMenuOpen && (
                <div className="absolute top-full left-0 w-full bg-bg border-b border-border shadow-lg md:hidden">
                    <nav className="flex flex-col px-4 py-6 gap-4">
                        {navLinks.map((link) => (
                            <Link
                                key={link.name}
                                href={link.path}
                                className="text-lg font-medium uppercase tracking-wider text-fg py-2 border-b border-border/50"
                            >
                                {link.name}
                            </Link>
                        ))}

                        {/* Mobile Auth Links */}
                        {!user ? (
                            <div className="flex gap-4 py-2 border-b border-border/50">
                                <Link
                                    href="/login"
                                    className="text-base font-medium uppercase text-fg"
                                >
                                    Login
                                </Link>
                                <span className="text-muted">|</span>
                                <Link
                                    href="/register"
                                    className="text-base font-medium uppercase text-fg"
                                >
                                    Register
                                </Link>
                            </div>
                        ) : (
                            <>
                                {isAdmin && (
                                    <a
                                        href="/admin"
                                        className="text-base font-medium uppercase text-accent tracking-wider font-display py-2 border-b border-border/50"
                                    >
                                        Admin Panel
                                    </a>
                                )}
                                <Link
                                    href="/profile"
                                    className="text-base font-medium uppercase text-fg py-2 border-b border-border/50"
                                >
                                    My Profile
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="text-base font-medium uppercase text-red-500 text-left py-2 border-b border-border/50"
                                >
                                    Logout
                                </Link>
                            </>
                        )}

                        <div className="flex items-center gap-6 pt-2">
                            <button
                                onClick={toggleTheme}
                                className="flex items-center gap-2 text-fg"
                            >
                                {theme === "dark" ? (
                                    <Sun className="h-5 w-5" />
                                ) : (
                                    <Moon className="h-5 w-5" />
                                )}
                                <span className="text-sm uppercase">Theme</span>
                            </button>
                            <Link
                                href="/wishlist"
                                className="flex items-center gap-2 text-fg"
                            >
                                <Heart className="h-5 w-5" />
                                <span className="text-sm uppercase">
                                    Wishlist
                                </span>
                            </Link>
                        </div>
                    </nav>
                </div>
            )}

            <SearchModal
                isOpen={isSearchOpen}
                onClose={() => setIsSearchOpen(false)}
            />
        </header>
    );
}

interface MiniDropdownProps {
    title: string;
    emptyLabel: string;
    viewAllHref: string;
    viewAllLabel: string;
    children: React.ReactNode;
    footer?: React.ReactNode;
    secondaryAction?: React.ReactNode;
}
function MiniDropdown({
    title,
    emptyLabel,
    viewAllHref,
    viewAllLabel,
    children,
    footer,
    secondaryAction,
}: MiniDropdownProps) {
    const hasItems = Children.toArray(children).length > 0;
    return (
        <motion.div
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 8 }}
            transition={{ duration: 0.15 }}
            className="absolute right-0 top-full mt-3 w-80 bg-bg border border-border shadow-xl z-50 p-4"
        >
            <h4 className="font-display text-sm font-bold uppercase tracking-wider text-fg mb-2">
                {title}
            </h4>
            {hasItems ? (
                <>
                    <div className="max-h-72 overflow-y-auto">{children}</div>
                    {footer}
                    <div className="mt-3 space-y-2">
                        <Button variant="outline" size="sm" fullWidth asChild>
                            <Link href={viewAllHref}>{viewAllLabel}</Link>
                        </Button>
                        {secondaryAction}
                    </div>
                </>
            ) : (
                <div className="py-8 text-center text-sm text-muted">
                    {emptyLabel}
                </div>
            )}
        </motion.div>
    );
}
