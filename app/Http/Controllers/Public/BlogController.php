<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $query = Post::where('is_active', true);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('tag')) {
                $query->where('tags', 'like', "%{$request->tag}%");
            }

            // Pagination — 5 per page
            $posts = $query->latest()->paginate(5)->withQueryString();

            $formattedBlogs = collect($posts->items())->map(function ($post) {
                return [
                    'id'          => (string) $post->id,
                    'title'       => $post->title,
                    'slug'        => $post->slug,
                    'excerpt'     => $post->excerpt ?? substr(strip_tags($post->content), 0, 150) . '...',
                    'image'       => $post->image_url,
                    'date'        => $post->created_at->format('M d, Y'),
                    'category'    => $post->category,
                    'author'      => $post->author,
                    'author_intro'      => $post->author_intro,
                    'tags'        => self::parseTags($post->tags),
                    'views_count' => $post->views_count,
                    'likes_count' => $post->likes_count,
                ];
            })->toArray();

            $blogCategories = Post::where('is_active', true)
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get()
                ->map(fn($c) => ['name' => $c->category, 'count' => $c->count])
                ->toArray();

            // Tags — properly flatten, deduplicate
            $allPostsTags = Post::where('is_active', true)
                ->whereNotNull('tags')
                ->pluck('tags')
                ->flatMap(fn($t) => self::parseTags($t))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } catch (Exception $e) {
            Log::error('Blog Index Error: ' . $e->getMessage());
            $formattedBlogs  = [];
            $blogCategories  = [];
            $allPostsTags    = [];
            $posts           = null;
        }

        return Inertia::render('Blog/Index', [
            'blogs'          => $formattedBlogs,
            'blogCategories' => $blogCategories,
            'popularTags'    => $allPostsTags,
            'title'          => 'Blog Insights',
            'filters'        => $request->only(['search', 'category', 'tag']),
            'pagination'     => $posts ? [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'has_more'     => $posts->hasMorePages(),
                'total'        => $posts->total(),
            ] : null,
            'auth'           => ['user' => Auth::user()],
        ]);
    }

    public function show($slug): Response
    {
        try {
            $post = Post::where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();

            $post->increment('views_count');

            $comments = $post->comments()->get()->map(function ($comment) {
                return [
                    'id'         => $comment->id,
                    'user_name'  => $comment->user_name,
                    'comment'    => $comment->comment,
                    'created_at' => $comment->created_at->format('M d, Y'),
                ];
            })->toArray();

            $tags = self::parseTags($post->tags);
            $hasLiked = false;

            if (Auth::check()) {
                $hasLiked = $post->likedByUsers()->where('user_id', Auth::id())->exists();
            }
            $blogDetail = [
                'id'          => (string) $post->id,
                'title'       => $post->title,
                'slug'        => $post->slug,
                'content'     => $post->content,
                'image'       => $post->image_url,
                'date'        => $post->created_at->format('M d, Y'),
                'category'    => $post->category,
                'author'      => $post->author,
                'author_intro'      => $post->author_intro,
                'tags'        => $tags,
                'views_count' => $post->views_count,
                'likes_count' => $post->likes_count,
                'comments'    => $comments,
                'has_liked'   => $hasLiked,
            ];

            $relatedPosts = Post::where('category', $post->category)
                ->where('id', '!=', $post->id)
                ->where('is_active', true)
                ->take(2)->get()
                ->map(fn($b) => [
                    'id'       => (string) $b->id,
                    'title'    => $b->title,
                    'slug'     => $b->slug,
                    'image'    => $b->image_url,
                    'category' => $b->category,
                    'date'     => $b->created_at->format('M d, Y'),
                ])->toArray();

            return Inertia::render('Blog/Show', [
                'post'         => $blogDetail,
                'relatedPosts' => $relatedPosts,
                'auth'         => ['user' => Auth::user()],
            ]);
        } catch (Exception $e) {
            abort(404);
        }
    }

    public function like($id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();
        $status = $post->likedByUsers()->toggle($user->id);
        if (count($status['attached']) > 0) {
            $post->increment('likes_count');
        } else {
            $post->decrement('likes_count');
        }

        return back()->with('success', 'Post liked!');
    }

    public function storeComment(Request $request, $id)
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'comment.required' => 'Please write your thoughts before posting.',
            'comment.min'      => 'Your comment must be at least 3 characters long.',
            'comment.max'      => 'Your comment cannot exceed 2000 characters.',
        ]);

        $user = Auth::user();

        // ඔබගේ User Model එකේ තියෙන්නේ first_name සහ last_name නිසා නම ගොඩනගමු
        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $user->name ?? 'Anonymous User';
        }

        BlogComment::create([
            'post_id'     => $id,
            'user_name'   => $fullName, // <--- මෙතන නිවැරදි කලා
            'user_email'  => $user->email,
            'comment'     => $validated['comment'],
            'is_approved' => false, // Admin approve කරනකල් false
        ]);

        return back()->with('success', 'Comment submitted! It will appear after approval.');
    }

    /**
     * DB tags field parse — handle both:
     * 1. Proper JSON array: ["Denim","Streetwear"]
     * 2. Wrongly stored string: "\"Denim,Streetwear,Menswear\""
     */
    private static function parseTags($tags): array
    {
        if (empty($tags)) return [];

        // Already an array (Laravel cast)
        if (is_array($tags)) {
            // Array of comma-separated strings? e.g. ["Denim,Streetwear,Menswear"]
            if (count($tags) === 1 && str_contains($tags[0], ',')) {
                return array_map('trim', explode(',', $tags[0]));
            }
            return array_map('trim', $tags);
        }

        // String — try JSON decode first
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            if (is_array($decoded)) {
                // Could be ["Denim,Streetwear"] — single element with commas
                if (count($decoded) === 1 && str_contains($decoded[0], ',')) {
                    return array_map('trim', explode(',', $decoded[0]));
                }
                return array_map('trim', $decoded);
            }
            // Plain comma-separated string
            return array_map('trim', explode(',', trim($tags, '"')));
        }

        return [];
    }
}
