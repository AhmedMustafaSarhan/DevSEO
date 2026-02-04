<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Repositories\Contracts\BlogPostRepositoryInterface;
use App\Services\BlogPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly BlogPostRepositoryInterface $repository,
        private readonly BlogPostService $service,
    ) {}

    /**
     * Get all published blog posts (paginated, optimized for SSG).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $locale = $request->query('locale', 'en');
        $region = $request->query('region', 'GLOBAL');
        $perPage = (int) $request->query('per_page', 10);

        $posts = $this->repository
            ->withRelations(['author', 'categories', 'tags'])
            ->published()
            ->byRegion($region)
            ->paginate($perPage);

        return BlogPostResource::collection($posts)
            ->additional([
                'meta' => [
                    'locale' => $locale,
                    'region' => $region,
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                ],
            ]);
    }

    /**
     * Get specific blog post by slug with SEO optimization.
     */
    public function show(string $slug, Request $request): BlogPostResource|JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $post = $this->service->getBySlugWithSEO($slug, $locale, [
            'author',
            'categories',
            'tags',
            'performanceMetrics',
        ]);

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found.',
                'slug' => $slug,
            ], 404);
        }

        return new BlogPostResource($post);
    }

    /**
     * Get posts by category slug.
     */
    public function byCategory(string $categorySlug, Request $request): AnonymousResourceCollection
    {
        $locale = $request->query('locale', 'en');
        $perPage = (int) $request->query('per_page', 10);

        $posts = $this->service->getByCategorySlug(
            $categorySlug,
            $locale,
            $perPage
        );

        return BlogPostResource::collection($posts)
            ->additional([
                'meta' => [
                    'locale' => $locale,
                    'category' => $categorySlug,
                ],
            ]);
    }

    /**
     * Search blog posts.
     */
    public function search(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $query = $request->query('q');
        $locale = $request->query('locale', 'en');

        if (!$query || strlen($query) < 2) {
            return response()->json([
                'message' => 'Search query must be at least 2 characters.',
            ], 422);
        }

        $posts = $this->service->search($query, $locale);

        return BlogPostResource::collection($posts)
            ->additional([
                'meta' => [
                    'locale' => $locale,
                    'query' => $query,
                    'results' => $posts->count(),
                ],
            ]);
    }

    /**
     * Get recent blog posts.
     */
    public function recent(Request $request): AnonymousResourceCollection
    {
        $locale = $request->query('locale', 'en');
        $limit = (int) $request->query('limit', 5);

        $posts = $this->service->getRecentPosts($locale, $limit);

        return BlogPostResource::collection($posts)
            ->additional([
                'meta' => [
                    'locale' => $locale,
                    'limit' => $limit,
                ],
            ]);
    }

    /**
     * Get SEO data for a blog post.
     */
    public function seoData(string $slug, Request $request): JsonResponse
    {
        $post = $this->repository->findBySlug($slug);

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found.',
            ], 404);
        }

        return response()->json([
            'slug' => $post->slug,
            'meta_title' => $post->getTranslation('meta_title', 'en'),
            'meta_description' => $post->getTranslation('meta_description', 'en'),
            'og_image' => $post->og_image,
            'canonical_url' => $post->canonical_url,
            'schema_json' => json_decode($post->schema_json, true),
        ]);
    }
}
