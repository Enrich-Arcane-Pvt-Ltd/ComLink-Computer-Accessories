import AppLayout from "@/Layouts/AppLayout";
import { Head, Link } from "@inertiajs/react";
import { ChevronLeft, Facebook, Linkedin, Share2, Twitter } from "lucide-react";

interface BlogPost {
    id: string;
    slug: string;
    title: string;
    excerpt: string;
    content: string;
    image: string;
    author: string;
    date: string;
    category: string;
    tags: string[];
}

interface ShowProps {
    post: BlogPost;
    relatedPosts: BlogPost[];
}

export default function Show({ post, relatedPosts = [] }: ShowProps) {
    return (
        <AppLayout>
            {/* Browser Tab Title */}
            <Head title={post.title} />

            <div className="bg-bg min-h-screen pb-24">
                {/* Hero Image */}
                <div className="w-full h-[50vh] min-h-[400px] relative">
                    <img
                        src={post.image}
                        alt={post.title}
                        className="w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-black/40" />
                    <div className="absolute inset-0 flex items-center justify-center">
                        <div className="container mx-auto px-4 text-center text-white max-w-4xl">
                            <div className="mb-4">
                                <span className="bg-primary text-primary-fg text-xs font-bold px-3 py-1 uppercase tracking-wider">
                                    {post.category}
                                </span>
                            </div>
                            <h1 className="font-display text-4xl md:text-6xl font-bold leading-tight mb-6">
                                {post.title}
                            </h1>
                            <div className="flex items-center justify-center gap-4 text-sm text-white/80">
                                <span>{post.date}</span>
                                <span>•</span>
                                <span>By {post.author}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="container mx-auto px-4 md:px-6 mt-12">
                    <div className="max-w-3xl mx-auto">
                        {/* Inertia Link for Back Button */}
                        <Link
                            href={route("blog")}
                            className="inline-flex items-center text-sm text-muted hover:text-primary mb-8 transition-colors"
                        >
                            <ChevronLeft className="h-4 w-4 mr-1" /> Back to
                            Blog
                        </Link>

                        {/* Content */}
                        <div
                            className="prose prose-lg dark:prose-invert max-w-none prose-headings:font-display prose-headings:font-bold prose-a:text-primary hover:prose-a:text-accent text-fg/90 leading-relaxed space-y-6"
                            dangerouslySetInnerHTML={{
                                __html: post.content,
                            }}
                        />

                        {/* Tags & Share */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-6 py-8 mt-12 border-y border-border">
                            <div className="flex items-center gap-2">
                                <span className="text-sm font-bold text-fg mr-2">
                                    Tags:
                                </span>
                                {post.tags?.map((tag) => (
                                    <Link
                                        key={tag}
                                        href="#"
                                        className="text-xs bg-muted/10 px-3 py-1 text-muted hover:bg-primary hover:text-primary-fg transition-colors"
                                    >
                                        {tag}
                                    </Link>
                                ))}
                            </div>
                            <div className="flex items-center gap-4">
                                <span className="text-sm font-bold text-fg flex items-center gap-2">
                                    <Share2 className="h-4 w-4" /> Share:
                                </span>
                                <button className="text-muted hover:text-[#1877F2] transition-colors">
                                    <Facebook className="h-5 w-5" />
                                </button>
                                <button className="text-muted hover:text-[#1DA1F2] transition-colors">
                                    <Twitter className="h-5 w-5" />
                                </button>
                                <button className="text-muted hover:text-[#0A66C2] transition-colors">
                                    <Linkedin className="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        {/* Author Bio */}
                        <div className="flex items-center gap-3 py-8 bg-card px-8 mt-12 border border-border">
                            <div className="w-20 h-20 rounded-full bg-muted/0 flex-shrink-0 overflow-hidden">
                                <img
                                    src={`https://ui-avatars.com/api/?name=${encodeURIComponent(post.author)}&background=192341&color=fff`}
                                    alt={post.author}
                                />
                            </div>
                            <div>
                                <h4 className="font-bold text-fg text-lg mb-2">
                                    {post.author}
                                </h4>
                                <p className="text-muted text-sm leading-relaxed">
                                    Fashion enthusiast and lead editor at VICKY
                                    STORE. Passionate about sustainable fashion
                                    and helping build versatile, timeless
                                    wardrobes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Related Posts */}
                {relatedPosts.length > 0 && (
                    <div className="container mx-auto px-4 md:px-6 mt-24">
                        <h3 className="font-display text-3xl font-bold text-fg text-center mb-12">
                            YOU MIGHT ALSO LIKE
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                            {relatedPosts.map((related) => (
                                <article
                                    key={related.id}
                                    className="group cursor-pointer"
                                >
                                    <Link
                                        href={route("blog.show", {
                                            slug: related.slug,
                                        })}
                                    >
                                        <div className="relative aspect-[16/9] overflow-hidden mb-4">
                                            <img
                                                src={related.image}
                                                alt={related.title}
                                                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <div className="text-xs text-primary font-bold uppercase tracking-wider">
                                                {related.category}
                                            </div>
                                            <h4 className="font-display text-xl font-bold text-fg group-hover:text-primary transition-colors line-clamp-2">
                                                {related.title}
                                            </h4>
                                        </div>
                                    </Link>
                                </article>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
