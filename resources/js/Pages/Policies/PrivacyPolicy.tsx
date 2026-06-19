import AppLayout from "@/Layouts/AppLayout";
import { Head } from "@inertiajs/react";

interface PrivacyData {
    title: string;
    last_updated: string;
    content: string;
}

interface PrivacyPolicyProps {
    auth: any;
    privacy: PrivacyData;
}

export default function PrivacyPolicy({ auth, privacy }: PrivacyPolicyProps) {
    return (
        <AppLayout>
            <Head title={privacy?.title || "Privacy Policy"} />

            <div className="bg-bg min-h-screen py-16">
                <div className="container mx-auto px-4 md:px-6 max-w-4xl">
                    {/* Header Section */}
                    <div className="text-center mb-16 border-b border-border pb-8">
                        <h1 className="font-display text-4xl font-bold text-fg mb-4 uppercase">
                            {privacy?.title}
                        </h1>
                        <p className="text-muted">{privacy?.last_updated}</p>
                    </div>

                    {/* HTML Content Render Section */}
                    {privacy?.content && (
                        <div
                            className="prose prose-lg dark:prose-invert max-w-none text-muted prose-headings:font-display prose-headings:font-bold prose-headings:text-fg prose-a:text-primary space-y-6"
                            dangerouslySetInnerHTML={{
                                __html: privacy.content,
                            }}
                        />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
