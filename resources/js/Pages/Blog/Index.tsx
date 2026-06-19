import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, Link } from "@inertiajs/react";
import { ChevronRight, Search } from "lucide-react";

interface BlogProps {
    blogs: any[];
    blogCategories: any[];
    title: string;
}

export default function Blog({
    blogs = [],
    blogCategories = [],
    title = "Blog",
}: BlogProps) {
    const featuredPost = blogs[0];
    const regularPosts = blogs.slice(1);

    return (
        <AppLayout>
            <Head title={title} />
            <div className="bg-bg min-h-screen">
                {/* Page Header */}
                <div className="bg-muted/10 py-16 text-center">
                    <h1 className="font-display text-4xl font-bold text-fg mb-4">
                        OUR BLOG
                    </h1>
                    <p className="text-muted max-w-2xl mx-auto px-4">
                        Style guides, brand news, and fashion insights.
                    </p>
                </div>

                <div className="container mx-auto px-4 md:px-6 py-16">
                    <div className="flex flex-col lg:flex-row gap-12">
                        {/* Main Content */}
                        <div className="lg:w-2/3 space-y-12">
                            {/* Featured Post */}
                            {featuredPost && (
                                <article className="group cursor-pointer">
                                    <Link
                                        href={route("blog.show", {
                                            slug: featuredPost.slug,
                                        })}
                                    >
                                        <div className="relative aspect-[16/9] overflow-hidden mb-6">
                                            <img
                                                src={featuredPost.image}
                                                alt={featuredPost.title}
                                                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                            />

                                            <div className="absolute top-4 left-4 bg-primary text-primary-fg text-xs font-bold px-3 py-1 uppercase tracking-wider">
                                                {featuredPost.category}
                                            </div>
                                        </div>
                                        <div className="space-y-3">
                                            <div className="flex items-center gap-4 text-sm text-muted">
                                                <span>{featuredPost.date}</span>
                                                <span>•</span>
                                                <span>
                                                    By {featuredPost.author}
                                                </span>
                                            </div>
                                            <h2 className="font-display text-3xl font-bold text-fg group-hover:text-primary transition-colors">
                                                {featuredPost.title}
                                            </h2>
                                            <p className="text-muted leading-relaxed">
                                                {featuredPost.excerpt}
                                            </p>
                                            <span className="inline-flex items-center font-semibold text-primary group-hover:text-accent transition-colors">
                                                Read More{" "}
                                                <ChevronRight className="h-4 w-4 ml-1" />
                                            </span>
                                        </div>
                                    </Link>
                                </article>
                            )}

                            {/* Grid Posts */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-border">
                                {regularPosts.map((post) => (
                                    <article
                                        key={post.id}
                                        className="group cursor-pointer"
                                    >
                                        <Link
                                            href={route("blog.show", {
                                                slug: featuredPost.slug,
                                            })}
                                        >
                                            <div className="relative aspect-[4/3] overflow-hidden mb-4">
                                                <img
                                                    src={post.image}
                                                    alt={post.title}
                                                    className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                                />
                                            </div>
                                            <div className="space-y-2">
                                                <div className="text-xs text-primary font-bold uppercase tracking-wider">
                                                    {post.category}
                                                </div>
                                                <h3 className="font-display text-xl font-bold text-fg group-hover:text-primary transition-colors line-clamp-2">
                                                    {post.title}
                                                </h3>
                                                <div className="text-sm text-muted">
                                                    {post.date}
                                                </div>
                                            </div>
                                        </Link>
                                    </article>
                                ))}
                            </div>

                            <div className="flex justify-center pt-8">
                                <Button variant="outline" size="lg">
                                    Load More Articles
                                </Button>
                            </div>
                        </div>

                        {/* Sidebar */}
                        <aside className="lg:w-1/3 space-y-10">
                            {/* Search */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Search
                                </h3>
                                <div className="relative">
                                    <input
                                        type="text"
                                        placeholder="Search articles..."
                                        className="w-full h-12 pl-4 pr-12 border border-border bg-bg text-fg focus:outline-none focus:border-primary"
                                    />

                                    <button className="absolute right-0 top-0 h-12 w-12 flex items-center justify-center text-muted hover:text-primary">
                                        <Search className="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            {/* Categories */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Categories
                                </h3>
                                <ul className="space-y-3">
                                    {blogCategories.map((cat) => (
                                        <li key={cat.name}>
                                            <Link
                                                href="#"
                                                className="flex items-center justify-between group"
                                            >
                                                <span className="text-muted group-hover:text-primary transition-colors">
                                                    {cat.name}
                                                </span>
                                                <span className="text-xs bg-muted/20 text-muted px-2 py-1 rounded-full">
                                                    {cat.count}
                                                </span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Tags */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Popular Tags
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {Array.from(
                                        new Set(blogs.flatMap((b) => b.tags)),
                                    ).map((tag) => (
                                        <Link
                                            key={tag}
                                            href="#"
                                            className="text-xs border border-border px-3 py-1.5 text-muted hover:border-primary hover:text-primary transition-colors"
                                        >
                                            {tag}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
