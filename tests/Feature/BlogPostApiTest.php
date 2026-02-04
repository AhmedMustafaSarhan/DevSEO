<?php

namespace Tests\Feature;

use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\BlogPostRepositoryInterface;
use App\Services\BlogPostService;
use App\Services\SEOService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Category $category;
    protected Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test data
        $this->author = User::factory()->create(['region' => 'EG']);
        $this->category = Category::factory()->create();
        $this->tag = Tag::factory()->create();
    }

    /** @test */
    public function can_list_published_blog_posts(): void
    {
        // Create published and unpublished posts
        BlogPost::factory(5)
            ->published()
            ->create(['author_id' => $this->author->id]);

        BlogPost::factory(2)
            ->create(['author_id' => $this->author->id, 'is_published' => false]);

        // Test listing
        $response = $this->getJson('/api/blog?locale=en&per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.locale', 'en');

        // Verify only published posts are returned
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('data.0.status.is_published', true);
    }

    /** @test */
    public function can_fetch_blog_post_by_slug(): void
    {
        $post = BlogPost::factory()
            ->published()
            ->create([
                'author_id' => $this->author->id,
                'slug' => 'test-post-slug',
                'title' => ['en' => 'Test Post Title'],
                'meta_title' => ['en' => 'SEO Title'],
                'meta_description' => ['en' => 'SEO Description'],
            ]);

        $response = $this->getJson('/api/blog/test-post-slug?locale=en');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'test-post-slug')
            ->assertJsonPath('data.title', 'Test Post Title')
            ->assertJsonPath('data.seo.meta_title', 'SEO Title')
            ->assertJsonPath('data.author.id', $this->author->id);
    }

    /** @test */
    public function returns_404_for_nonexistent_post(): void
    {
        $response = $this->getJson('/api/blog/nonexistent-slug');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Blog post not found.');
    }

    /** @test */
    public function can_filter_posts_by_category(): void
    {
        $post1 = BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id]);
        $post1->categories()->attach($this->category->id);

        BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id]);

        $response = $this->getJson(
            '/api/blog/category/' . $this->category->slug . '?locale=en'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('meta.category', $this->category->slug);
    }

    /** @test */
    public function can_search_blog_posts(): void
    {
        BlogPost::factory()
            ->published()
            ->create([
                'author_id' => $this->author->id,
                'title' => ['en' => 'Laravel Performance'],
            ]);

        BlogPost::factory()
            ->published()
            ->create([
                'author_id' => $this->author->id,
                'title' => ['en' => 'PHP Best Practices'],
            ]);

        $response = $this->getJson('/api/blog/search?q=Laravel&locale=en');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.query', 'Laravel');
    }

    /** @test */
    public function search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/blog/search?q=a&locale=en');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Search query must be at least 2 characters.');
    }

    /** @test */
    public function can_get_recent_posts(): void
    {
        BlogPost::factory(10)
            ->published()
            ->create(['author_id' => $this->author->id]);

        $response = $this->getJson('/api/blog/recent?locale=en&limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.limit', 5);
    }

    /** @test */
    public function can_get_seo_metadata(): void
    {
        $post = BlogPost::factory()
            ->create([
                'author_id' => $this->author->id,
                'slug' => 'seo-test',
                'meta_title' => ['en' => 'SEO Title'],
                'meta_description' => ['en' => 'SEO Description'],
                'og_image' => 'https://example.com/og.jpg',
                'canonical_url' => 'https://devseo.com/blog/seo-test',
                'schema_json' => json_encode(['@context' => 'https://schema.org']),
            ]);

        $response = $this->getJson('/api/blog/seo-test/seo');

        $response->assertStatus(200)
            ->assertJsonPath('slug', 'seo-test')
            ->assertJsonPath('meta_title', 'SEO Title')
            ->assertJsonPath('og_image', 'https://example.com/og.jpg')
            ->assertJsonPath('canonical_url', 'https://devseo.com/blog/seo-test')
            ->assertJsonStructure(['schema_json']);
    }

    /** @test */
    public function api_resource_includes_author_and_relationships(): void
    {
        $post = BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id]);

        $post->categories()->attach($this->category->id);
        $post->tags()->attach($this->tag->id);

        $response = $this->getJson('/api/blog/' . $post->slug . '?locale=en');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'title',
                    'author' => ['id', 'name', 'email'],
                    'categories' => [
                        ['id', 'slug', 'name'],
                    ],
                    'tags' => [
                        ['id', 'name', 'slug'],
                    ],
                    'seo',
                    'metrics',
                    'status',
                ],
            ]);
    }

    /** @test */
    public function region_filtering_works_correctly(): void
    {
        $egPost = BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id, 'regions' => ['EG']]);

        $usPost = BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id, 'regions' => ['US']]);

        $globalPost = BlogPost::factory()
            ->published()
            ->create(['author_id' => $this->author->id, 'regions' => ['GLOBAL']]);

        // Test EG region
        $response = $this->getJson('/api/blog?region=EG&per_page=100');
        $this->assertEquals(2, $response->json('meta.total')); // EG + GLOBAL

        // Test US region
        $response = $this->getJson('/api/blog?region=US&per_page=100');
        $this->assertEquals(2, $response->json('meta.total')); // US + GLOBAL
    }

    /** @test */
    public function response_locale_affects_translatable_fields(): void
    {
        $post = BlogPost::factory()
            ->create([
                'author_id' => $this->author->id,
                'title' => [
                    'en' => 'English Title',
                    'ar' => 'العنوان بالعربية',
                ],
            ]);

        // Get English version
        $response = $this->getJson('/api/blog/' . $post->slug . '?locale=en');
        $response->assertJsonPath('data.title', 'English Title');

        // Get Arabic version
        $response = $this->getJson('/api/blog/' . $post->slug . '?locale=ar');
        $response->assertJsonPath('data.title', 'العنوان بالعربية');
    }

    /** @test */
    public function repository_pattern_works_correctly(): void
    {
        $repository = app(BlogPostRepositoryInterface::class);

        BlogPost::factory(5)
            ->published()
            ->create(['author_id' => $this->author->id]);

        // Test all()
        $posts = $repository->all();
        $this->assertGreaterThanOrEqual(5, $posts->count());

        // Test findBySlug()
        $post = BlogPost::factory()
            ->published()
            ->create([
                'author_id' => $this->author->id,
                'slug' => 'repository-test',
            ]);

        $found = $repository->findBySlug('repository-test');
        $this->assertNotNull($found);
        $this->assertEquals('repository-test', $found->slug);

        // Test fluent interface
        $published = $repository
            ->withRelations(['author'])
            ->published()
            ->paginate(10);

        $this->assertTrue($published->count() > 0);
        $this->assertNotNull($published->items()[0]->author);
    }

    /** @test */
    public function service_layer_increments_view_count(): void
    {
        $service = app(BlogPostService::class);

        $post = BlogPost::factory()
            ->published()
            ->create([
                'author_id' => $this->author->id,
                'slug' => 'view-count-test',
                'view_count' => 0,
            ]);

        // First fetch
        $retrieved = $service->getBySlugWithSEO('view-count-test', 'en');
        $this->assertEquals(1, $retrieved->view_count);

        // Second fetch
        $retrieved = $service->getBySlugWithSEO('view-count-test', 'en');
        $this->assertEquals(2, $retrieved->view_count);
    }

    /** @test */
    public function seo_service_generates_valid_schema(): void
    {
        $seoService = app(SEOService::class);

        $post = BlogPost::factory()
            ->create([
                'author_id' => $this->author->id,
                'title' => ['en' => 'Schema Test'],
            ]);

        $schema = $seoService->generateBlogSchema($post);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('BlogPosting', $schema['@type']);
        $this->assertEquals('Schema Test', $schema['headline']);
        $this->assertArrayHasKey('author', $schema);
        $this->assertArrayHasKey('datePublished', $schema);
    }

    /** @test */
    public function seo_service_calculates_accurate_score(): void
    {
        $seoService = app(SEOService::class);

        // High-quality post
        $post = BlogPost::factory()
            ->create([
                'author_id' => $this->author->id,
                'title' => ['en' => 'This is a well-optimized title for SEO'],
                'meta_title' => ['en' => 'Well-Optimized Title - 45 chars'],
                'meta_description' => ['en' => 'This is a comprehensive meta description with proper length for SEO optimization'],
                'content' => ['en' => str_repeat('word ', 500)], // 2500 words
                'og_image' => 'https://example.com/image.jpg',
                'featured_image_url' => 'https://example.com/feature.jpg',
                'canonical_url' => 'https://devseo.com/blog/test',
                'schema_json' => json_encode(['@context' => 'https://schema.org']),
            ]);

        $post->categories()->attach($this->category->id);
        $post->tags()->attach($this->tag->id);

        $score = $seoService->calculateSEOScore($post);

        $this->assertGreaterThanOrEqual(70, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    /** @test */
    public function seo_service_suggests_improvements(): void
    {
        $seoService = app(SEOService::class);

        // Poor SEO post
        $post = BlogPost::factory()
            ->create([
                'author_id' => $this->author->id,
                'title' => ['en' => 'Short'],
                'meta_title' => ['en' => 'Too Short'],
                'meta_description' => ['en' => 'Short'],
                'content' => ['en' => 'Insufficient content length'],
            ]);

        $improvements = $seoService->suggestImprovements($post);

        $this->assertNotEmpty($improvements);
        $this->assertTrue(
            in_array('Content is too short. Aim for at least 1,000 words for better SEO.', $improvements)
        );
    }
}
