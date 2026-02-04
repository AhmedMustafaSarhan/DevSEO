# DevSEO Backend - Laravel 11 Initialization Guide

## Project Overview
- **Framework**: Laravel 11 (API-only)
- **Database**: PostgreSQL (multi-region)
- **Architecture**: Headless (Astro SSG frontend)
- **Languages**: English (EN) + Arabic (AR)
- **Pattern**: Repository + Service + Controller (SOLID)

---

## Phase 1: Project Setup

### 1.1 Create Laravel Project
```bash
# Create new Laravel project
laravel new devseo-api --git

cd devseo-api

# Install key dependencies
composer require laravel/sanctum
composer require spatie/laravel-sluggable
composer require spatie/laravel-translatable
composer require symfony/var-dumper --dev
```

### 1.2 Environment Configuration
```bash
# Copy .env
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=devseo
DB_USERNAME=devseo_user
DB_PASSWORD=secure_password
```

---

## Phase 2: Database Architecture

### 2.1 Core Tables

#### Users Table (Authors & Administrators)
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    region ENUM('EG', 'US', 'INTL') DEFAULT 'INTL',
    is_author BOOLEAN DEFAULT false,
    is_admin BOOLEAN DEFAULT false,
    bio JSONB, -- Translatable
    avatar_url VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_region (region),
    INDEX idx_is_author (is_author)
);
```

#### Blog Posts Table (SEO-Optimized)
```sql
CREATE TABLE blog_posts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    slug VARCHAR(255) UNIQUE NOT NULL INDEX,
    title JSONB NOT NULL, -- { "en": "Title", "ar": "العنوان" }
    description JSONB NOT NULL,
    content JSONB NOT NULL,
    excerpt JSONB NOT NULL,
    
    -- SEO Fields
    meta_title JSONB NOT NULL,
    meta_description JSONB NOT NULL,
    canonical_url VARCHAR(500) NOT NULL,
    og_image VARCHAR(500) NULL,
    schema_json JSONB NOT NULL, -- BlogPosting schema
    
    -- Content Management
    region ENUM('EG', 'US', 'GLOBAL') DEFAULT 'GLOBAL',
    featured_image_url VARCHAR(500) NULL,
    reading_time_minutes INT DEFAULT 5,
    seo_score INT DEFAULT 0,
    
    -- Status
    published_at TIMESTAMP NULL,
    scheduled_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'published') DEFAULT 'draft',
    
    -- Tracking
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL, -- Soft delete
    
    INDEX idx_slug (slug),
    INDEX idx_published (published_at),
    INDEX idx_status (status),
    INDEX idx_region (region),
    INDEX idx_user (user_id)
);
```

#### Categories Table (Hierarchical)
```sql
CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    parent_id UUID REFERENCES categories(id) ON DELETE CASCADE NULL,
    name JSONB NOT NULL, -- { "en": "Category", "ar": "الفئة" }
    slug VARCHAR(255) UNIQUE NOT NULL,
    description JSONB NULL,
    meta_title JSONB NULL,
    meta_description JSONB NULL,
    schema_json JSONB NULL,
    
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug)
);
```

#### Blog Post Categories (Pivot)
```sql
CREATE TABLE blog_post_category (
    blog_post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (blog_post_id, category_id)
);
```

#### Tags Table
```sql
CREATE TABLE tags (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name JSONB NOT NULL, -- { "en": "Tag", "ar": "الوسم" }
    slug VARCHAR(255) UNIQUE NOT NULL,
    color VARCHAR(7) NULL, -- HEX color
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug)
);
```

#### Blog Post Tags (Pivot)
```sql
CREATE TABLE blog_post_tag (
    blog_post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
    tag_id UUID REFERENCES tags(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (blog_post_id, tag_id)
);
```

#### Contact Submissions
```sql
CREATE TABLE contact_submissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    
    -- Metadata
    region VARCHAR(50) NULL,
    ip_address INET NULL,
    user_agent TEXT NULL,
    referer VARCHAR(500) NULL,
    
    -- Status
    status ENUM('new', 'read', 'responded') DEFAULT 'new',
    responded_at TIMESTAMP NULL,
    responded_by UUID REFERENCES users(id) ON DELETE SET NULL NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_region (region),
    INDEX idx_created (created_at)
);
```

#### Performance Metrics
```sql
CREATE TABLE performance_metrics (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    blog_post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE NULL,
    
    -- Core Web Vitals
    lcp DECIMAL(5, 2) NULL, -- Largest Contentful Paint (seconds)
    fid DECIMAL(5, 2) NULL, -- First Input Delay (milliseconds)
    cls DECIMAL(5, 3) NULL, -- Cumulative Layout Shift
    
    -- Additional Metrics
    page_load_time DECIMAL(5, 2) NULL,
    time_to_first_byte DECIMAL(5, 2) NULL,
    
    -- Context
    region VARCHAR(50) NULL,
    device_type ENUM('mobile', 'tablet', 'desktop') NULL,
    browser VARCHAR(100) NULL,
    
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_post (blog_post_id),
    INDEX idx_region (region),
    INDEX idx_measured (measured_at)
);
```

---

## Phase 3: Eloquent Models

### Directory Structure
```
app/
├── Models/
│   ├── User.php
│   ├── BlogPost.php
│   ├── Category.php
│   ├── Tag.php
│   ├── ContactSubmission.php
│   └── PerformanceMetric.php
├── Repositories/
│   ├── Contracts/
│   │   ├── BlogPostRepositoryInterface.php
│   │   ├── CategoryRepositoryInterface.php
│   │   └── ...
│   └── Eloquent/
│       ├── BlogPostRepository.php
│       ├── CategoryRepository.php
│       └── ...
├── Services/
│   ├── BlogPostService.php
│   ├── SEOService.php
│   └── ContentPublishingService.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── BlogPostController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ContactController.php
│   │   │   └── PerformanceController.php
│   ├── Requests/
│   │   ├── StoreBlogPostRequest.php
│   │   ├── StoreContactRequest.php
│   │   └── ...
│   └── Resources/
│       ├── BlogPostResource.php
│       ├── CategoryResource.php
│       └── ...
└── Actions/
    ├── PublishBlogPost.php
    ├── GenerateSEOSchema.php
    └── ...
```

---

## Phase 4: Model Examples

### User Model (Strict Typing)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'region',
        'is_author',
        'is_admin',
        'bio',
        'avatar_url',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_author' => 'boolean',
        'is_admin' => 'boolean',
        'bio' => 'json',
        'last_login_at' => 'datetime',
    ];

    // Relationships
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function contactSubmissions(): HasMany
    {
        return $this->hasMany(ContactSubmission::class, 'responded_by');
    }
}
```

### BlogPost Model (Multilingual + SEO)
```php
<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasFactory, HasUuids, Sluggable, HasTranslations, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_image',
        'schema_json',
        'region',
        'featured_image_url',
        'reading_time_minutes',
        'seo_score',
        'published_at',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'title' => 'json',
        'description' => 'json',
        'content' => 'json',
        'excerpt' => 'json',
        'meta_title' => 'json',
        'meta_description' => 'json',
        'schema_json' => 'json',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'view_count' => 'integer',
        'seo_score' => 'integer',
    ];

    protected array $translatable = [
        'title',
        'description',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
    ];

    // Sluggable Configuration
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'blog_post_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag');
    }

    public function performanceMetrics()
    {
        return $this->hasMany(PerformanceMetric::class);
    }

    // Query Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region)
            ->orWhere('region', 'GLOBAL');
    }

    // Accessors
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }
}
```

---

## Phase 5: Repository Pattern (SOLID)

### Interface Definition
```php
<?php

namespace App\Repositories\Contracts;

use App\Models\BlogPost;
use Illuminate\Pagination\Paginator;

interface BlogPostRepositoryInterface
{
    public function all(): \Illuminate\Database\Eloquent\Collection;
    
    public function paginate(int $perPage = 15): Paginator;
    
    public function findBySlug(string $slug): ?BlogPost;
    
    public function findById(string $id): ?BlogPost;
    
    public function create(array $data): BlogPost;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function published(): \Illuminate\Database\Eloquent\Collection;
    
    public function byRegion(string $region): \Illuminate\Database\Eloquent\Collection;
}
```

### Repository Implementation
```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\BlogPost;
use App\Repositories\Contracts\BlogPostRepositoryInterface;

class BlogPostRepository implements BlogPostRepositoryInterface
{
    public function __construct(private BlogPost $model)
    {
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function findById(string $id): ?BlogPost
    {
        return $this->model->find($id);
    }

    public function create(array $data): BlogPost
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->model->find($id)?->update($data) ?? false;
    }

    public function delete(string $id): bool
    {
        return (bool) $this->model->find($id)?->delete();
    }

    public function published(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->published()->get();
    }

    public function byRegion(string $region): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->byRegion($region)->get();
    }
}
```

---

## Phase 6: Service Layer

### BlogPost Service
```php
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

    public function getAllPublished(string $locale = 'en'): Collection
    {
        return $this->repository->published()
            ->each(fn($post) => $post->setLocale($locale));
    }

    public function getBySlugWithSEO(string $slug, string $locale = 'en'): ?BlogPost
    {
        $post = $this->repository->findBySlug($slug);
        
        if (!$post) {
            return null;
        }

        $post->setLocale($locale);
        $post->increment('view_count');

        return $post;
    }

    public function publishPost(BlogPost $post): void
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'schema_json' => $this->seoService->generateBlogSchema($post),
            'seo_score' => $this->seoService->calculateSEOScore($post),
        ]);
    }

    public function computeReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200)); // ~200 words per minute
    }
}
```

### SEO Service
```php
<?php

namespace App\Services;

use App\Models\BlogPost;

class SEOService
{
    public function generateBlogSchema(BlogPost $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->meta_description,
            'image' => $post->og_image,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $post->canonical_url,
            ],
        ];
    }

    public function calculateSEOScore(BlogPost $post): int
    {
        $score = 0;

        // Title optimization (10 points)
        if (strlen($post->meta_title) >= 30 && strlen($post->meta_title) <= 60) {
            $score += 10;
        }

        // Meta description (10 points)
        if (strlen($post->meta_description) >= 120 && strlen($post->meta_description) <= 160) {
            $score += 10;
        }

        // Content length (10 points)
        if (strlen($post->content) >= 1000) {
            $score += 10;
        }

        // Image optimization (10 points)
        if ($post->og_image) {
            $score += 10;
        }

        // Schema implementation (10 points)
        if ($post->schema_json) {
            $score += 10;
        }

        // Canonical URL (10 points)
        if ($post->canonical_url) {
            $score += 10;
        }

        // Tags/Categories (10 points)
        if ($post->tags->count() > 0 && $post->categories->count() > 0) {
            $score += 10;
        }

        // Reading time (10 points)
        if ($post->reading_time_minutes > 0) {
            $score += 10;
        }

        return min($score, 100);
    }
}
```

---

## Phase 7: API Controllers

### BlogPost Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Services\BlogPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function __construct(private BlogPostService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $posts = $this->service->getAllPublished($locale);

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($posts),
            'meta' => [
                'locale' => $locale,
                'count' => $posts->count(),
            ]
        ]);
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $post = $this->service->getBySlugWithSEO($slug, $locale);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Blog post not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($post),
        ]);
    }
}
```

---

## Phase 8: API Resources (Optimized for SSG)

### BlogPost Resource
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            
            // SEO
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'canonical_url' => $this->canonical_url,
                'og_image' => $this->og_image,
                'schema_json' => $this->schema_json,
            ],
            
            // Content
            'featured_image' => $this->featured_image_url,
            'reading_time_minutes' => $this->reading_time_minutes,
            'region' => $this->region,
            
            // Meta
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->author->avatar_url,
            ],
            'categories' => CategoryResource::collection($this->categories),
            'tags' => TagResource::collection($this->tags),
            
            // Metadata
            'published_at' => $this->published_at?->toIso8601String(),
            'view_count' => $this->view_count,
            'seo_score' => $this->seo_score,
        ];
    }
}
```

---

## Next Steps

1. ✅ Create database migrations
2. ✅ Define models with relationships
3. ✅ Implement repository pattern
4. ✅ Build service layer
5. ⏳ Create API controllers
6. ⏳ Setup routes
7. ⏳ Create tests
8. ⏳ Document API

This foundation ensures:
- **Multilingual Support**: JSONB fields + Translatable trait
- **SEO Optimization**: Schema, meta tags, canonical URLs
- **Type Safety**: Strict typing throughout
- **SOLID Principles**: Repository, Service, Controller separation
- **SSG Ready**: Optimized JSON responses for Astro
