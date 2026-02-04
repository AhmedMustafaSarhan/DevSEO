<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Repositories\Contracts\BlogPostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BlogPostService
{
    public function __construct(
        private BlogPostRepositoryInterface $repository,
        private SEOService $seoService
    ) {
    }

    /**
     * Get all published blog posts with optional relations.
     */
    public function getAllPublished(string $locale = 'en', array $relations = []): Collection
    {
        return $this->repository
            ->withRelations($relations)
            ->published()
            ->each(fn($post) => $post->setLocale($locale));
    }

    /**
     * Get published posts by region.
     */
    public function getByRegion(string $region, string $locale = 'en', array $relations = []): Collection
    {
        return $this->repository
            ->withRelations($relations)
            ->byRegion($region)
            ->published()
            ->each(fn($post) => $post->setLocale($locale));
    }

    /**
     * Get blog post by slug with SEO optimization and tracking.
     */
    public function getBySlugWithSEO(string $slug, string $locale = 'en', array $relations = ['author', 'categories', 'tags']): ?BlogPost
    {
        $post = $this->repository
            ->withRelations($relations)
            ->findBySlug($slug);

        if (!$post || !$post->is_published) {
            return null;
        }

        $post->setLocale($locale);
        $post->increment('view_count');

        return $post;
    }

    /**
     * Publish a blog post and generate SEO data.
     */
    public function publishPost(BlogPost $post): void
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'schema_json' => $this->seoService->generateBlogSchema($post),
            'seo_score' => $this->seoService->calculateSEOScore($post),
        ]);
    }

    /**
     * Calculate reading time in minutes.
     */
    public function computeReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200)); // ~200 words per minute
    }

    /**
     * Get recent posts for sidebar.
     */
    public function getRecentPosts(int $limit = 5, string $locale = 'en'): Collection
    {
        return $this->repository
            ->withRelations(['author'])
            ->published()
            ->take($limit)
            ->get()
            ->each(fn($post) => $post->setLocale($locale));
    }

    /**
     * Get posts by category.
     */
    public function getByCategorySlug(string $categorySlug, string $locale = 'en'): Collection
    {
        return BlogPost::query()
            ->published()
            ->whereHas('categories', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })
            ->with(['author', 'categories', 'tags'])
            ->get()
            ->each(fn($post) => $post->setLocale($locale));
    }

    /**
     * Search posts by title or content.
     */
    public function search(string $query, string $locale = 'en'): Collection
    {
        $searchTerm = "%{$query}%";

        return BlogPost::query()
            ->published()
            ->where('title', 'ilike', $searchTerm)
            ->orWhere('content', 'ilike', $searchTerm)
            ->with(['author', 'categories', 'tags'])
            ->get()
            ->each(fn($post) => $post->setLocale($locale));
    }
}
