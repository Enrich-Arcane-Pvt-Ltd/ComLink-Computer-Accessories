import { Instagram } from "lucide-react";

interface InstagramGridProps {
    instagramImages: string[];
}

export function InstagramGrid({ instagramImages = [] }: InstagramGridProps) {
    if (!instagramImages || instagramImages.length === 0) {
        return null;
    }

    return (
        <section className="pt-12 bg-bg border-t border-border">
            <div className="container mx-auto px-4 md:px-6 mb-8">
                <div className="text-center">
                    <div className="inline-flex items-center gap-3 mb-2 text-fg">
                        <Instagram className="h-6 w-6" />
                        <h3 className="font-display text-2xl font-bold">
                            @WikiStore
                        </h3>
                    </div>
                    <p className="text-muted">
                        Follow us on Instagram for daily inspiration
                    </p>
                </div>
            </div>

            <div className="relative overflow-hidden">
                <div
                    className="flex"
                    style={{
                        animation: "marquee-px 30s linear infinite",
                        width: "max-content",
                    }}
                >
                    {[...instagramImages, ...instagramImages].map(
                        (img, idx) => (
                            <a
                                key={idx}
                                href="https://instagram.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="relative flex-shrink-0 group overflow-hidden"
                                style={{
                                    width: "clamp(180px, 20vw, 280px)",
                                    height: "clamp(180px, 20vw, 280px)",
                                }}
                            >
                                <img
                                    src={img}
                                    alt={`Instagram post ${idx + 1}`}
                                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                    <Instagram className="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </a>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}
