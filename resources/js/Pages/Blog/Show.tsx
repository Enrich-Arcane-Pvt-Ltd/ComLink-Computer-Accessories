import AppLayout from "@/Layouts/AppLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";
import {
    ChevronLeft,
    Facebook,
    Heart,
    Linkedin,
    MessageCircle,
    Share2,
    Twitter,
} from "lucide-react";
import { useEffect, useState } from "react";
import { toast } from "sonner";

interface Comment {
    id: number;
    user_name: string;
    comment: string;
    created_at: string;
}

interface BlogPost {
    id: string;
    slug: string;
    title: string;
    excerpt: string;
    content: string;
    image: string;
    author: string;
    author_intro: string;
    date: string;
    category: string;
    tags: string[];
    views_count: number;
    likes_count: number;
    comments: Comment[];
    has_liked?: boolean;
}

interface ShowProps {
    post: BlogPost;
    relatedPosts: BlogPost[];
    auth: {
        user: { first_name: string; last_name: string; email: string } | null;
    };
}

export default function Show({ post, relatedPosts = [], auth }: ShowProps) {
    const [likesCount, setLikesCount] = useState(post.likes_count ?? 0);
    const [liked, setLiked] = useState(post.has_liked ?? false);
    const [likeLoading, setLikeLoading] = useState(false);
    const [shareCopied, setShareCopied] = useState(false);

    useEffect(() => {
        setLikesCount(post.likes_count ?? 0);
        setLiked(post.has_liked ?? false);
    }, [post]);

    const isAuth = !!auth?.user;
    const authUserName =
        auth?.user?.first_name && auth?.user?.last_name
            ? `${auth.user.first_name} ${auth.user.last_name}`
            : "User";

    // Ensure tags is always an array
    const tags = Array.isArray(post.tags) ? post.tags : [];

    const {
        data,
        setData,
        post: submitComment,
        processing,
        reset,
        errors,
    } = useForm({
        comment: "",
    });

    const handleLike = () => {
        if (!isAuth) return;

        router.post(
            route("blog.like", { id: post.id }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (!liked) {
                        // toast.success("Post liked!");
                    } else {
                        toast.info("Like removed");
                    }
                },
            },
        );
    };

    const handleCommentSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        submitComment(route("blog.comment", { id: post.id }), {
            preserveScroll: true,
            onSuccess: () => {
                reset("comment"); // Comment field එක විතරක් clear කරන්න
            },
        });
    };

    // Share helpers
    const currentUrl =
        typeof window !== "undefined" ? window.location.href : "";
    const encodedUrl = encodeURIComponent(currentUrl);
    const encodedTitle = encodeURIComponent(post.title);

    const shareLinks = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
    };

    const handleCopyLink = () => {
        navigator.clipboard.writeText(currentUrl).then(() => {
            setShareCopied(true);
            setTimeout(() => setShareCopied(false), 2000);
        });
    };

    return (
        <AppLayout>
            <Head title={post.title} />

            <div className="bg-bg min-h-screen pb-24">
                {/* Hero */}
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
                                <span>•</span>
                                <span>{post.views_count} views</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="container mx-auto px-4 md:px-6 mt-12">
                    <div className="max-w-3xl mx-auto">
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
                            dangerouslySetInnerHTML={{ __html: post.content }}
                        />

                        {/* Tags & Share */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-6 py-8 mt-12 border-y border-border">
                            {/* Tags */}
                            <div className="flex items-center gap-2 flex-wrap">
                                <span className="text-sm font-bold text-fg mr-2">
                                    Tags:
                                </span>
                                {tags.length > 0 ? (
                                    tags.map((tag) => (
                                        <Link
                                            key={tag}
                                            href={
                                                route("blog") +
                                                `?tag=${encodeURIComponent(tag)}`
                                            }
                                            className="text-xs bg-muted/10 px-3 py-1 text-muted hover:bg-primary hover:text-primary-fg transition-colors"
                                        >
                                            {tag}
                                        </Link>
                                    ))
                                ) : (
                                    <span className="text-sm text-muted">
                                        —
                                    </span>
                                )}
                            </div>

                            {/* Share buttons — functional */}
                            <div className="flex items-center gap-3 flex-shrink-0">
                                <span className="text-sm font-bold text-fg flex items-center gap-2">
                                    <Share2 className="h-4 w-4" /> Share:
                                </span>
                                <a
                                    href={shareLinks.facebook}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-muted hover:text-[#1877F2] transition-colors"
                                    title="Share on Facebook"
                                >
                                    <Facebook className="h-5 w-5" />
                                </a>
                                <a
                                    href={shareLinks.twitter}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-muted hover:text-[#1DA1F2] transition-colors"
                                    title="Share on X (Twitter)"
                                >
                                    <Twitter className="h-5 w-5" />
                                </a>
                                <a
                                    href={shareLinks.linkedin}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-muted hover:text-[#0A66C2] transition-colors"
                                    title="Share on LinkedIn"
                                >
                                    <Linkedin className="h-5 w-5" />
                                </a>
                                <button
                                    onClick={handleCopyLink}
                                    className="text-xs text-muted hover:text-primary border border-border hover:border-primary px-2 py-1 transition-colors"
                                    title="Copy link"
                                >
                                    {shareCopied ? "✓ Copied!" : "Copy link"}
                                </button>
                            </div>
                        </div>

                        {/* Like Button */}
                        <div className="flex items-center gap-4 py-8">
                            {isAuth ? (
                                <button
                                    onClick={handleLike}
                                    className={`inline-flex items-center gap-2 px-6 py-3 border transition-all font-semibold text-sm cursor-pointer ${
                                        liked
                                            ? "border-red-400 text-red-500 bg-red-50 dark:bg-red-950/20"
                                            : "border-border text-muted hover:border-red-400 hover:text-red-500"
                                    }`}
                                >
                                    <Heart
                                        className={`h-5 w-5 transition-all ${liked ? "fill-red-500 text-red-500" : ""}`}
                                    />
                                    <span>{likesCount}</span>
                                    <span>
                                        {liked ? "Liked!" : "Like this post"}
                                    </span>
                                </button>
                            ) : (
                                <Link
                                    href={route("login")}
                                    className="inline-flex items-center gap-2 px-6 py-3 border border-border text-muted hover:border-primary hover:text-primary transition-colors text-sm font-semibold"
                                >
                                    <Heart className="h-5 w-5" />
                                    <span>{likesCount}</span>
                                    <span>Login to like</span>
                                </Link>
                            )}
                        </div>

                        {/* Author Bio */}
                        <div className="flex items-center gap-3 py-8 bg-card px-8 mt-4 border border-border">
                            <div className="w-20 h-20 rounded-full flex-shrink-0 overflow-hidden">
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
                                    {post.author_intro ??
                                        `Fashion enthusiast and lead
                                        editor at VICKY STORE. Passionate about
                                        sustainable fashion and helping build
                                        versatile, timeless wardrobes.`}
                                </p>
                            </div>
                        </div>

                        {/* ===== COMMENTS SECTION ===== */}
                        <div className="mt-16">
                            <h3 className="font-display text-2xl font-bold text-fg mb-8 flex items-center gap-3">
                                <MessageCircle className="h-6 w-6 text-primary" />
                                Comments
                                {post.comments.length > 0 && (
                                    <span className="text-sm font-normal text-muted bg-muted/10 px-2 py-0.5 rounded-full">
                                        {post.comments.length}
                                    </span>
                                )}
                            </h3>

                            {post.comments.length === 0 ? (
                                <div className="text-center py-12 border border-dashed border-border">
                                    <MessageCircle className="h-10 w-10 text-muted/40 mx-auto mb-3" />
                                    <p className="text-muted text-sm">
                                        No approved comments yet. Be the first!
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-6 mb-10">
                                    {post.comments.map((comment) => (
                                        <div
                                            key={comment.id}
                                            className="flex gap-4 p-6 bg-card border border-border"
                                        >
                                            <div className="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                                <img
                                                    src={`https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user_name)}&background=192341&color=fff&size=40`}
                                                    alt={comment.user_name}
                                                    className="w-full h-full object-cover"
                                                />
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between gap-2 mb-2">
                                                    <span className="font-bold text-fg text-sm">
                                                        {comment.user_name}
                                                    </span>
                                                    <span className="text-xs text-muted flex-shrink-0">
                                                        {comment.created_at}
                                                    </span>
                                                </div>
                                                <p className="text-muted text-sm leading-relaxed">
                                                    {comment.comment}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Add Comment */}
                            <div className="border border-border p-8 bg-card mt-8">
                                <h4 className="font-display text-xl font-bold text-fg mb-6">
                                    Leave a Comment
                                </h4>

                                {isAuth ? (
                                    <form onSubmit={handleCommentSubmit}>
                                        <div className="flex items-center gap-3 mb-4">
                                            <div className="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                                                <img
                                                    src={`https://ui-avatars.com/api/?name=${encodeURIComponent(authUserName)}&background=192341&color=fff&size=36`}
                                                    alt={authUserName}
                                                />
                                            </div>
                                            <span className="text-sm font-semibold text-fg">
                                                {authUserName}
                                            </span>
                                        </div>

                                        <textarea
                                            value={data.comment}
                                            onChange={(e) =>
                                                setData(
                                                    "comment",
                                                    e.target.value,
                                                )
                                            }
                                            rows={5}
                                            placeholder="Share your thoughts..."
                                            className="w-full border border-border bg-bg text-fg p-4 focus:outline-none focus:border-primary resize-none text-sm"
                                        />
                                        {errors.comment && (
                                            <p className="text-red-500 text-xs mt-1">
                                                {errors.comment}
                                            </p>
                                        )}

                                        <div className="flex items-center justify-between mt-4">
                                            <p className="text-xs text-muted">
                                                💡 Comments are reviewed before
                                                appearing publicly.
                                            </p>
                                            <button
                                                type="submit"
                                                disabled={
                                                    processing ||
                                                    !data.comment.trim()
                                                }
                                                className="bg-primary text-primary-fg px-6 py-2.5 text-sm font-bold uppercase tracking-wider hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {processing
                                                    ? "Submitting..."
                                                    : "Post Comment"}
                                            </button>
                                        </div>
                                    </form>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-muted mb-4 text-sm">
                                            You need to be logged in to leave a
                                            comment.
                                        </p>
                                        <Link
                                            href={route("login")}
                                            className="inline-block bg-primary text-primary-fg px-8 py-3 text-sm font-bold uppercase tracking-wider hover:opacity-90 transition-opacity"
                                        >
                                            Login to Comment
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>
                        {/* ===== END COMMENTS SECTION ===== */}
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
