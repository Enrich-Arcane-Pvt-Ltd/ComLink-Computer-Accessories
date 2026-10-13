import AppLayout from "@/Layouts/AppLayout";
import { Head, Link, router } from "@inertiajs/react";
import { ChevronRight, Search, X } from "lucide-react";
import { useState } from "react";

interface BlogPost {
    id: string;
    title: string;
    slug: string;
    excerpt: string;
    image: string;
    date: string;
    category: string;
    author: string;
    tags: string[];
    views_count: number;
    likes_count: number;
}

interface BlogCategory {
    name: string;
    count: number;
}

interface Pagination {
    current_page: number;
    last_page: number;
    has_more: boolean;
    total: number;
}

interface BlogProps {
    blogs: BlogPost[];
    blogCategories: BlogCategory[];
    popularTags: string[];
    title: string;
    filters: { search?: string; category?: string; tag?: string };
    pagination: Pagination | null;
}

export default function Blog({
    blogs = [],
    blogCategories = [],
    popularTags = [],
    title = "Blog",
    filters = {},
    pagination,
}: BlogProps) {
    const [searchInput, setSearchInput] = useState(filters.search ?? "");
    const [loadingMore, setLoadingMore] = useState(false);

    const featuredPost = blogs[0];
    const regularPosts = blogs.slice(1);

    const navigate = (params: Record<string, string | number>) => {
        router.get(
            route("blog"),
            { ...filters, ...params },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        navigate({ search: searchInput, page: 1 });
    };

    const handleCategoryFilter = (name: string) => {
        navigate({ category: filters.category === name ? "" : name, page: 1 });
    };

    const handleTagFilter = (tag: string) => {
        navigate({ tag: filters.tag === tag ? "" : tag, page: 1 });
    };

    const clearFilters = () => {
        setSearchInput("");
        router.get(route("blog"), {}, { replace: true });
    };

    const loadMore = () => {
        if (!pagination?.has_more || loadingMore) return;
        setLoadingMore(true);
        router.get(
            route("blog"),
            { ...filters, page: pagination.current_page + 1 },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setLoadingMore(false),
            },
        );
    };

    const hasActiveFilters = filters.search || filters.category || filters.tag;

    return (
        <AppLayout>
            <Head title={title} />
            <div className="bg-bg min-h-screen">
                <div className="bg-muted/10 py-16 text-center">
                    <h1 className="font-display text-4xl font-bold text-fg mb-4">
                        OUR BLOG
                    </h1>
                    <p className="text-muted max-w-2xl mx-auto px-4">
                        Style guides, brand news, and fashion insights.
                    </p>
                </div>

                <div className="container mx-auto px-4 md:px-6 py-16">
                    {/* Active Filters Bar */}
                    {hasActiveFilters && (
                        <div className="flex items-center gap-3 mb-8 flex-wrap">
                            <span className="text-sm text-muted">
                                Filtering by:
                            </span>
                            {filters.search && (
                                <span className="text-xs bg-primary/10 text-primary border border-primary/30 px-3 py-1">
                                    Search: "{filters.search}"
                                </span>
                            )}
                            {filters.category && (
                                <span className="text-xs bg-primary/10 text-primary border border-primary/30 px-3 py-1">
                                    Category: {filters.category}
                                </span>
                            )}
                            {filters.tag && (
                                <span className="text-xs bg-primary/10 text-primary border border-primary/30 px-3 py-1">
                                    Tag: {filters.tag}
                                </span>
                            )}
                            <button
                                onClick={clearFilters}
                                className="inline-flex items-center gap-1 text-xs text-muted hover:text-primary transition-colors"
                            >
                                <X className="h-3 w-3" /> Clear all
                            </button>
                        </div>
                    )}

                    <div className="flex flex-col lg:flex-row gap-12">
                        {/* Main Content */}
                        <div className="lg:w-2/3 space-y-12">
                            {blogs.length === 0 ? (
                                <div className="flex flex-col items-center justify-center py-24 text-center">
                                    <div className="text-6xl mb-6">📝</div>
                                    <h2 className="font-display text-2xl font-bold text-fg mb-3">
                                        No Articles Found
                                    </h2>
                                    <p className="text-muted mb-6 max-w-sm">
                                        {hasActiveFilters
                                            ? "No articles match your current filters."
                                            : "No blog posts published yet. Check back soon!"}
                                    </p>
                                    {hasActiveFilters && (
                                        <button
                                            onClick={clearFilters}
                                            className="text-sm font-semibold text-primary underline underline-offset-4"
                                        >
                                            Clear all filters
                                        </button>
                                    )}
                                </div>
                            ) : (
                                <>
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
                                                        <span>
                                                            {featuredPost.date}
                                                        </span>
                                                        <span>•</span>
                                                        <span>
                                                            By{" "}
                                                            {
                                                                featuredPost.author
                                                            }
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
                                    {regularPosts.length > 0 && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-border">
                                            {regularPosts.map((post) => (
                                                <article
                                                    key={post.id}
                                                    className="group cursor-pointer"
                                                >
                                                    <Link
                                                        href={route(
                                                            "blog.show",
                                                            { slug: post.slug },
                                                        )}
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
                                    )}

                                    {/* Load More — pagination */}
                                    {pagination?.has_more && (
                                        <div className="flex flex-col items-center gap-2 pt-8">
                                            <button
                                                onClick={loadMore}
                                                disabled={loadingMore}
                                                className="border border-border px-8 py-3 text-sm font-semibold text-fg hover:border-primary hover:text-primary transition-colors disabled:opacity-50"
                                            >
                                                {loadingMore
                                                    ? "Loading..."
                                                    : "Load More Articles"}
                                            </button>
                                            <span className="text-xs text-muted">
                                                Page {pagination.current_page}{" "}
                                                of {pagination.last_page} ·{" "}
                                                {pagination.total} total
                                            </span>
                                        </div>
                                    )}

                                    {/* All loaded */}
                                    {!pagination?.has_more &&
                                        pagination &&
                                        pagination.total > 5 && (
                                            <p className="text-center text-sm text-muted pt-4">
                                                All {pagination.total} articles
                                                loaded.
                                            </p>
                                        )}
                                </>
                            )}
                        </div>

                        {/* Sidebar */}
                        <aside className="lg:w-1/3 space-y-10">
                            {/* Search */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Search
                                </h3>
                                <form
                                    onSubmit={handleSearch}
                                    className="relative"
                                >
                                    <input
                                        type="text"
                                        value={searchInput}
                                        onChange={(e) =>
                                            setSearchInput(e.target.value)
                                        }
                                        placeholder="Search articles..."
                                        className="w-full h-12 pl-4 pr-12 border border-border bg-bg text-fg focus:outline-none focus:border-primary"
                                    />
                                    <button
                                        type="submit"
                                        className="absolute right-0 top-0 h-12 w-12 flex items-center justify-center text-muted hover:text-primary"
                                    >
                                        <Search className="h-5 w-5" />
                                    </button>
                                </form>
                            </div>

                            {/* Categories */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Categories
                                </h3>
                                {blogCategories.length === 0 ? (
                                    <p className="text-sm text-muted">
                                        No categories yet.
                                    </p>
                                ) : (
                                    <ul className="space-y-3">
                                        {blogCategories.map((cat) => (
                                            <li key={cat.name}>
                                                <button
                                                    onClick={() =>
                                                        handleCategoryFilter(
                                                            cat.name,
                                                        )
                                                    }
                                                    className={`w-full flex items-center justify-between transition-colors ${
                                                        filters.category ===
                                                        cat.name
                                                            ? "text-primary"
                                                            : "text-muted hover:text-primary"
                                                    }`}
                                                >
                                                    <span>{cat.name}</span>
                                                    <span
                                                        className={`text-xs px-2 py-1 rounded-full ${
                                                            filters.category ===
                                                            cat.name
                                                                ? "bg-primary/20 text-primary"
                                                                : "bg-muted/20 text-muted"
                                                        }`}
                                                    >
                                                        {cat.count}
                                                    </span>
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>

                            {/* Tags */}
                            <div className="bg-card p-6 border border-border">
                                <h3 className="font-display text-xl font-bold text-fg mb-4">
                                    Popular Tags
                                </h3>
                                {popularTags.length === 0 ? (
                                    <p className="text-sm text-muted">
                                        No tags yet.
                                    </p>
                                ) : (
                                    <div className="flex flex-wrap gap-2">
                                        {popularTags.map((tag) => (
                                            <button
                                                key={tag}
                                                onClick={() =>
                                                    handleTagFilter(tag)
                                                }
                                                className={`text-xs border px-3 py-1.5 transition-colors ${
                                                    filters.tag === tag
                                                        ? "border-primary text-primary bg-primary/10"
                                                        : "border-border text-muted hover:border-primary hover:text-primary"
                                                }`}
                                            >
                                                {tag}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
