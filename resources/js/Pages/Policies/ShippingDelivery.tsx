import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";

interface ShippingData {
    title: string;
    last_updated: string;
    content: string;
}

interface ShippingDeliveryProps {
    auth: any;
    shipping: ShippingData;
}

export default function ShippingDelivery({
    auth,
    shipping,
}: ShippingDeliveryProps) {
    return (
        <AppLayout>
            <Head title={shipping?.title || "Shipping & Delivery"} />

            <div className="bg-bg min-h-screen py-16">
                <div className="container mx-auto px-4 md:px-6 max-w-4xl">
                    {/* Header Section */}
                    <div className="text-center mb-16 border-b border-border pb-8">
                        <h1 className="font-display text-4xl font-bold text-fg mb-4 uppercase">
                            {shipping?.title}
                        </h1>
                        <p className="text-muted">{shipping?.last_updated}</p>
                    </div>

                    {/* HTML Content Render Section */}
                    {shipping?.content && (
                        <div
                            className="prose prose-lg dark:prose-invert max-w-none text-muted prose-headings:font-display prose-headings:font-bold prose-headings:text-fg prose-a:text-primary space-y-6"
                            dangerouslySetInnerHTML={{
                                __html: shipping.content,
                            }}
                        />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
