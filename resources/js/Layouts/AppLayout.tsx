import { AnnouncementBar } from "@/Components/layout/AnnouncementBar";
import Footer from "@/Components/layout/Footer";
import Navbar from "@/Components/layout/Navbar";
import { CartProvider } from "@/contexts/CartContext";
import { ThemeProvider } from "@/contexts/ThemeContext";
import { WishlistProvider } from "@/contexts/WishlistContext";
import { Head, usePage } from "@inertiajs/react";
import { Toaster } from "sonner";
import FlashAlerts from "../Components/Elements/Alerts/FlashAlerts";
import ScrollTopButton from "../Components/Elements/Buttons/ScrollTopButton";
import { PageProps } from "../types";

export default function AppLayout({
    children,
    product = null,
    className = "bg-white",
}: {
    children: React.ReactNode;
    product?: any;
    className?: any;
}) {
    const appName = import.meta.env.VITE_APP_NAME || "ThinkPath";
    const { flash, auth } = usePage<PageProps>().props;
    const { url } = usePage();

    function isActive(startWith?: string) {
        if (startWith == "/") {
            return url == startWith;
        } else {
            return url.startsWith(startWith ?? "");
        }
    }
    const whatsappUrl = import.meta.env.VITE_WHATSAPP_URL || "";
    return (
        <>
            <Head>
                <title>{appName}</title>
                <meta
                    head-key="description"
                    name="description"
                    content="Welcome to Vicky-Store Online Shop."
                />
                <meta property="og:type" content="Website" />
                <meta property="og:url" content={url} />
                <meta property="og:image" content="/assets/meta/meta.webp?aa" />
                <link
                    rel="icon"
                    type="image/svg+xml"
                    href="/assets/favicon.ico?1212a"
                />
                <meta name="google-site-verification" content="" />
            </Head>

            <ThemeProvider>
                <CartProvider>
                    <WishlistProvider>
                        <div
                            className={`min-h-screen flex flex-col bg-bg text-fg transition-colors duration-300 ${className}`}
                        >
                            <AnnouncementBar />
                            <Navbar />
                            <main className="flex-grow">{children}</main>
                            <Footer />
                            <FlashAlerts flash={flash} />
                            <ScrollTopButton />
                        </div>
                        <Toaster
                            position="bottom-right"
                            toastOptions={{
                                className: "bg-bg text-fg border-border",
                            }}
                        />
                    </WishlistProvider>
                </CartProvider>
            </ThemeProvider>
        </>
    );
}
