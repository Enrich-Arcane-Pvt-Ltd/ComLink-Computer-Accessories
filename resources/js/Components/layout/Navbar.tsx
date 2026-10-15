import { useCart } from "@/contexts/CartContext";
import { useTheme } from "@/contexts/ThemeContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { cn } from "@/utils/cn";
import { Link, usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import {
    ChevronDown,
    Cpu,
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
import React, { Children, useEffect, useMemo, useRef, useState } from "react";
import { SearchModal } from "../search/SearchModal";
import { Button } from "../ui/Button";

/* -------------------------------------------------------------------------- */
/*  Category navigation data                                                  */
/* -------------------------------------------------------------------------- */

type NavChild = { name: string; path: string; image?: string };
type NavItem = {
    name: string;
    path: string;
    image?: string;
    children?: NavChild[];
};

const cat = (slug: string) => `/shop?category=${slug}`;

const defaultNavItems: NavItem[] = [
    { name: "Laptops", path: cat("laptops") },
    {
        name: "PC Components",
        path: cat("pc-components"),
        children: [
            { name: "Processors", path: cat("processors") },
            { name: "Motherboards", path: cat("motherboards") },
            { name: "Coolers", path: cat("coolers") },
            { name: "Memory Cards", path: cat("memory") },
            { name: "SSD", path: cat("ssd") },
            { name: "Hard Disk", path: cat("hard-disk") },
            { name: "Graphic Cards", path: cat("graphic-cards") },
            { name: "Power Supply", path: cat("power-supply") },
            { name: "Thermal Paste", path: cat("thermal-paste") },
            { name: "PC Cases", path: cat("pc-cases") },
        ],
    },
    {
        name: "Peripherals",
        path: cat("peripherals"),
        children: [
            { name: "Headsets", path: cat("headsets") },
            { name: "Keyboards", path: cat("keyboards") },
            { name: "Mouse", path: cat("mouse") },
            { name: "Mouse Pad", path: cat("mouse-pad") },
            { name: "Speakers", path: cat("speakers") },
            { name: "Webcam", path: cat("webcam") },
            { name: "Converters", path: cat("converters") },
            { name: "Adapters", path: cat("adapters") },
            {
                name: "Video & Creator Equipments",
                path: cat("video-creator-equipments"),
            },
        ],
    },
    { name: "Monitors & Displays", path: cat("monitors-displays") },
    {
        name: "Gaming",
        path: cat("gaming"),
        children: [
            { name: "Laptops", path: cat("gaming-laptops") },
            { name: "Consoles", path: cat("consoles") },
            { name: "Controllers", path: cat("controllers") },
        ],
    },
    {
        name: "Products",
        path: cat("products"),
        children: [
            { name: "Computers", path: cat("computers") },
            { name: "UPS", path: cat("ups") },
            { name: "Chairs", path: cat("chairs") },
            { name: "Tables", path: cat("tables") },
            { name: "Power Banks", path: cat("power-banks") },
            { name: "Extension Cards", path: cat("extension-cards") },
            { name: "Printers", path: cat("printers") },
            {
                name: "Toners and Cartridges",
                path: cat("toners-cartridges"),
            },
            { name: "Scanners", path: cat("scanners") },
        ],
    },
    { name: "Software", path: cat("software") },
];

const BUILD_PC_ROUTE = "/build-my-pc";

/* -------------------------------------------------------------------------- */
/*  Navbar                                                                    */
/* -------------------------------------------------------------------------- */

export default function Navbar() {
    const { auth, navCategories } = usePage().props as any;
    const user = auth?.user ?? null;

    // Dynamically populated categories from Admin database, with fallback
    const navItems: NavItem[] = useMemo(() => {
        if (
            navCategories &&
            Array.isArray(navCategories) &&
            navCategories.length > 0
        ) {
            return navCategories.map((item: any) => ({
                name: item.name,
                path: item.path || `/shop?category=${item.slug}`,
                image: item.image,
                children: item.children?.map((child: any) => ({
                    name: child.name,
                    path: child.path || `/shop?category=${child.slug}`,
                    image: child.image,
                })),
            }));
        }
        return defaultNavItems;
    }, [navCategories]);

    const { url } = usePage();

    // Role-based routes
    const isAdmin = user?.role === "admin" || user?.role === "super-admin";
    const dashboardRoute = isAdmin ? "/admin" : "/";
    const profileRoute = "/profile";

    const [isScrolled, setIsScrolled] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [mobileOpenGroup, setMobileOpenGroup] = useState<string | null>(null);
    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const [isUserDropdownOpen, setIsUserDropdownOpen] = useState(false);
    // "cart" | "wishlist" | "nav:<Item name>" | null
    const [openDropdown, setOpenDropdown] = useState<string | null>(null);

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
        setMobileOpenGroup(null);
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

    const openMenu = (menu: string) => {
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

    const isActive = (path: string) => url === path || url.startsWith(path + "&");

    return (
        <header
            className={cn(
                "sticky top-0 z-40 w-full transition-all duration-300 border-b",
                isScrolled
                    ? "bg-bg/90 backdrop-blur-lg border-border shadow-md"
                    : "bg-bg border-transparent",
            )}
        >
            {/* ------------------------------------------------------------ */}
            {/*  TOP ROW: Logo | Search | Theme | Auth | Wishlist | Cart      */}
            {/* ------------------------------------------------------------ */}
            <div className="container mx-auto px-4 md:px-6">
                <div
                    className={cn(
                        "flex items-center justify-between gap-4 md:gap-8 transition-all duration-300",
                        isScrolled ? "py-2" : "py-4",
                    )}
                >
                    {/* Mobile Menu Toggle */}
                    <button
                        className="lg:hidden p-2 -ml-2 text-fg cursor-pointer"
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
                            alt="ComLink Logo"
                            className="h-8 md:h-12 w-auto object-contain transition-transform duration-300 hover:scale-105"
                        />
                    </Link>

                    {/* Search box (desktop / tablet) */}
                    <button
                        type="button"
                        onClick={handleSearchClick}
                        className="hidden md:flex flex-1 max-w-xl items-center gap-3 rounded-md border border-border bg-card px-4 py-2.5 text-left text-sm text-muted transition-colors hover:border-accent focus:outline-none focus-visible:border-accent cursor-pointer"
                        aria-label="Search products"
                    >
                        <Search className="h-4 w-4 flex-shrink-0" />
                        <span className="truncate">
                            Search laptops, graphic cards, keyboards...
                        </span>
                    </button>

                    {/* Icons & Auth */}
                    <div className="flex items-center gap-3 md:gap-5">
                        {/* Search icon (mobile only) */}
                        <button
                            onClick={handleSearchClick}
                            className="md:hidden p-1 text-fg hover:text-accent transition-colors cursor-pointer"
                            aria-label="Search"
                        >
                            <Search className="h-5 w-5" />
                        </button>

                        {/* Dark / light toggle */}
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

                        {/* --- AUTHENTICATION AREA --- */}
                        {user ? (
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
                                            className="absolute right-0 mt-3 w-56 bg-bg border border-border shadow-xl z-50 py-2 font-sans"
                                        >
                                            {/* Signed in as */}
                                            <div className="px-4 py-3 border-b border-border">
                                                <div className="flex items-center gap-3">
                                                    <img
                                                        src={
                                                            user.avatar_url
                                                                ? `/storage/${user.avatar_url}`
                                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                                      (
                                                                          user.first_name +
                                                                          " " +
                                                                          user.last_name
                                                                      ).trim(),
                                                                  )}&color=04202b&background=22d3ee`
                                                        }
                                                        alt={user.first_name}
                                                        className="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                                    />
                                                    <div className="min-w-0">
                                                        <p className="text-sm font-semibold text-fg truncate">
                                                            {user.first_name}{" "}
                                                            {user.last_name}
                                                        </p>
                                                        <p className="text-xs text-muted truncate">
                                                            {user.email}
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
                            /* Guests: user icon + Login | Register */
                            <div className="hidden sm:flex items-center gap-2">
                                <User className="h-5 w-5 text-fg" />
                                <Link
                                    href="/login"
                                    className="text-xs uppercase tracking-wider font-semibold text-fg hover:text-accent py-1 transition-colors cursor-pointer"
                                >
                                    Login
                                </Link>
                                <span className="text-border">|</span>
                                <Link
                                    href="/register"
                                    className="text-xs uppercase tracking-wider font-semibold text-fg hover:text-accent py-1 transition-colors cursor-pointer"
                                >
                                    Register
                                </Link>
                            </div>
                        )}

                        {/* Build My PC button */}
                        <Link
                            href={BUILD_PC_ROUTE}
                            className="hidden lg:inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-xs font-bold uppercase tracking-wider text-accent-fg whitespace-nowrap transition-opacity hover:opacity-90"
                        >
                            <Cpu className="h-4 w-4" />
                            Build My PC
                        </Link>

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
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-accent-fg">
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
                                                    className="flex items-center gap-3 py-3 border-b border-border last:border-0"
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
                                                            className="text-sm font-medium text-fg line-clamp-1 hover:text-accent"
                                                        >
                                                            {p.name}
                                                        </Link>
                                                        <p className="text-sm text-fg font-semibold">
                                                            Rs. {Number(p.price || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
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
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-accent-fg">
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
                                                        Rs. {totalPrice.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
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
                                                            className="text-sm font-medium text-fg line-clamp-1 hover:text-accent"
                                                        >
                                                            {item.name}
                                                        </Link>
                                                        <p className="text-xs text-muted">
                                                            {item.quantity} × Rs. {Number(item.price || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
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

            {/* ------------------------------------------------------------ */}
            {/*  BOTTOM NAV (desktop): categories + Build My PC               */}
            {/* ------------------------------------------------------------ */}
            <div className="hidden lg:block bg-primary text-primary-fg">
                <div className="container mx-auto px-4 md:px-6">
                    <div className="flex items-center justify-between">
                        <nav
                            className="flex items-center"
                            aria-label="Product categories"
                        >
                            {navItems.map((item) => {
                                const key = `nav:${item.name}`;
                                const hasChildren = !!item.children?.length;
                                const active =
                                    isActive(item.path) ||
                                    !!item.children?.some((c) =>
                                        isActive(c.path),
                                    );

                                return (
                                    <div
                                        key={item.name}
                                        className="relative"
                                        onMouseEnter={() =>
                                            hasChildren
                                                ? openMenu(key)
                                                : scheduleClose()
                                        }
                                        onMouseLeave={scheduleClose}
                                    >
                                        <Link
                                            href={item.path}
                                            className={cn(
                                                "flex items-center gap-1 px-4 py-3 text-xs xl:text-sm font-medium uppercase tracking-wider whitespace-nowrap transition-colors hover:text-accent-300",
                                                active
                                                    ? "text-accent-300"
                                                    : "text-primary-fg",
                                            )}
                                        >
                                            {item.name}
                                            {hasChildren && (
                                                <ChevronDown
                                                    className={cn(
                                                        "h-3.5 w-3.5 transition-transform duration-200",
                                                        openDropdown === key &&
                                                            "rotate-180",
                                                    )}
                                                />
                                            )}
                                        </Link>

                                        <AnimatePresence>
                                            {hasChildren &&
                                                openDropdown === key && (
                                                    <motion.div
                                                        initial={{
                                                            opacity: 0,
                                                            y: 8,
                                                        }}
                                                        animate={{
                                                            opacity: 1,
                                                            y: 0,
                                                        }}
                                                        exit={{
                                                            opacity: 0,
                                                            y: 8,
                                                        }}
                                                        transition={{
                                                            duration: 0.15,
                                                        }}
                                                        className={cn(
                                                            "absolute left-0 top-full z-50 bg-bg border border-border shadow-xl py-2",
                                                            item.children!
                                                                .length > 6
                                                                ? "w-[26rem] grid grid-cols-2"
                                                                : "w-56",
                                                        )}
                                                    >
                                                        {item.children!.map(
                                                            (child) => (
                                                                <Link
                                                                    key={
                                                                        child.name
                                                                    }
                                                                    href={
                                                                        child.path
                                                                    }
                                                                    className={cn(
                                                                        "group flex items-center gap-2.5 px-4 py-2 text-sm transition-colors hover:bg-muted/10 hover:text-accent",
                                                                        isActive(
                                                                            child.path,
                                                                        )
                                                                            ? "text-accent font-medium"
                                                                            : "text-fg",
                                                                    )}
                                                                >
                                                                    {child.image && (
                                                                        <img
                                                                            src={
                                                                                child.image
                                                                            }
                                                                            alt={
                                                                                child.name
                                                                            }
                                                                            className="w-5 h-5 rounded-sm object-cover flex-shrink-0 opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-200"
                                                                        />
                                                                    )}
                                                                    <span className="truncate">
                                                                        {child.name}
                                                                    </span>
                                                                </Link>
                                                            ),
                                                        )}
                                                    </motion.div>
                                                )}
                                        </AnimatePresence>
                                    </div>
                                );
                            })}
                        </nav>
                    </div>
                </div>
            </div>

            {/* ------------------------------------------------------------ */}
            {/*  MOBILE MENU                                                  */}
            {/* ------------------------------------------------------------ */}
            {isMobileMenuOpen && (
                <div className="absolute top-full left-0 w-full max-h-[80vh] overflow-y-auto bg-bg border-b border-border shadow-lg lg:hidden">
                    <nav className="flex flex-col px-4 py-6 gap-1">
                        {navItems.map((item) => {
                            const hasChildren = !!item.children?.length;
                            const isOpen = mobileOpenGroup === item.name;

                            return (
                                <div
                                    key={item.name}
                                    className="border-b border-border/50"
                                >
                                    <div className="flex items-center justify-between">
                                        <Link
                                            href={item.path}
                                            className="flex-1 text-base font-medium uppercase tracking-wider text-fg py-3"
                                        >
                                            {item.name}
                                        </Link>
                                        {hasChildren && (
                                            <button
                                                onClick={() =>
                                                    setMobileOpenGroup(
                                                        isOpen
                                                            ? null
                                                            : item.name,
                                                    )
                                                }
                                                className="p-2 text-fg cursor-pointer"
                                                aria-label={`Toggle ${item.name}`}
                                                aria-expanded={isOpen}
                                            >
                                                <ChevronDown
                                                    className={cn(
                                                        "h-5 w-5 transition-transform duration-200",
                                                        isOpen && "rotate-180",
                                                    )}
                                                />
                                            </button>
                                        )}
                                    </div>

                                    {hasChildren && isOpen && (
                                        <div className="flex flex-col pb-3 pl-4">
                                            {item.children!.map((child) => (
                                                <Link
                                                    key={child.name}
                                                    href={child.path}
                                                    className="flex items-center gap-2.5 py-2 text-sm text-muted hover:text-accent transition-colors"
                                                >
                                                    {child.image && (
                                                        <img
                                                            src={child.image}
                                                            alt={child.name}
                                                            className="w-4 h-4 rounded-sm object-cover opacity-80"
                                                        />
                                                    )}
                                                    <span>{child.name}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}

                        {/* Build My PC */}
                        <Link
                            href={BUILD_PC_ROUTE}
                            className="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-accent px-4 py-3 text-sm font-bold uppercase tracking-wider text-accent-fg"
                        >
                            <Cpu className="h-4 w-4" />
                            Build My PC
                        </Link>

                        {/* Mobile Auth Links */}
                        {!user ? (
                            <div className="flex gap-4 py-4 border-b border-border/50">
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
                                        className="text-base font-medium uppercase text-accent tracking-wider font-display py-3 border-b border-border/50"
                                    >
                                        Admin Panel
                                    </a>
                                )}
                                <Link
                                    href="/profile"
                                    className="text-base font-medium uppercase text-fg py-3 border-b border-border/50"
                                >
                                    My Profile
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="text-base font-medium uppercase text-red-500 text-left py-3 border-b border-border/50"
                                >
                                    Logout
                                </Link>
                            </>
                        )}

                        <div className="flex items-center gap-6 pt-4">
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

/* -------------------------------------------------------------------------- */
/*  MiniDropdown (cart / wishlist)                                            */
/* -------------------------------------------------------------------------- */

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