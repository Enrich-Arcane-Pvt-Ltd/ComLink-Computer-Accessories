import { AnnouncementBar } from "@/Components/layout/AnnouncementBar";
import Footer from "@/Components/layout/Footer";
import Navbar from "@/Components/layout/Navbar";
import { CartProvider } from "@/contexts/CartContext";
import { ThemeProvider } from "@/contexts/ThemeContext";
import { WishlistProvider } from "@/contexts/WishlistContext";
import { Head, usePage } from "@inertiajs/react";
import { useEffect } from "react";
import { toast, Toaster } from "sonner";
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
    const appName = import.meta.env.VITE_APP_NAME || "WikiStore";
    const { flash, auth } = usePage<PageProps>().props;
    const { url } = usePage();

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash]);

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
                    content="Welcome to Wiki-Store Online Shop."
                />
                <meta property="og:type" content="Website" />
                <meta property="og:url" content={url} />
                <meta property="og:image" content="/meta.webp?aa" />
                <link
                    rel="icon"
                    type="image/png"
                    href="/favicon-96x96.png"
                    sizes="96x96"
                />
                <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
                <link rel="shortcut icon" href="/favicon.ico" />
                <link
                    rel="apple-touch-icon"
                    sizes="180x180"
                    href="/apple-touch-icon.png"
                />
                <meta name="apple-mobile-web-app-title" content="WikiStore" />
                <link rel="manifest" href="/site.webmanifest" />

                <meta name="google-site-verification" content="" />
            </Head>

            <ThemeProvider>
                <CartProvider>
                    <WishlistProvider>
                        <div
                            className={`min-h-screen flex flex-col bg-bg text-fg transition-colors duration-300 ${className}`}
                        >
                            <div className="sticky top-0 z-40 w-full">
                                <AnnouncementBar />
                                <Navbar />
                            </div>
                            <main className="flex-grow">{children}</main>
                            <Footer />
                            {/* <FlashAlerts flash={flash} /> */}
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
