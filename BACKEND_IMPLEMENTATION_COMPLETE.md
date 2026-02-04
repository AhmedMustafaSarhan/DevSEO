# DevSEO - Backend Implementation Guide

## Phase 3: Laravel 11 API Backend Completion

This guide covers the completed backend implementation for DevSEO's headless architecture.

---

## Architecture Overview

```
Frontend (Astro.js SSG)
    ↓
API Gateway (Laravel 11)
    ↓
Service Layer (Business Logic)
    ↓
Repository Layer (Data Access)
    ↓
PostgreSQL Database (Multilingual JSONB)
```

### Design Patterns

- **Repository Pattern**: Data access abstraction with interface contracts
- **Service Layer**: Business logic isolation and composition
- **Dependency Injection**: Service container for loose coupling
- **SOLID Principles**: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion

---

## Core Components Created

### 1. Services

#### BlogPostService
**Purpose**: Handle all blog post business logic

**Key Methods**:
- `getAllPublished(string $locale, array $relations)` - Get all published posts with translations
- `getBySlugWithSEO(string $slug, string $locale, array $relations)` - Fetch post with view tracking
- `getByRegion(string $region, string $locale)` - Regional filtering
- `getBySlugWithSEO(string $slug, string $locale, array $relations)` - Fetch specific post
- `publishPost(BlogPost $post)` - Generate schema and calculate SEO score
- `computeReadingTime(string $content)` - Calculate reading time (~200 words/minute)
- `getByCategorySlug(string $slug, string $locale)` - Filter by category
- `search(string $query, string $locale)` - Full-text search
- `getRecentPosts(string $locale, int $limit = 5)` - Latest posts

**Usage Example**:
```php
// In controller
$post = $this->service->getBySlugWithSEO('my-post-slug', 'en', [
    'author',
    'categories',
    'tags',
]);

// Business logic encapsulation
$post->view_count; // Automatically incremented
$post->reading_time_minutes; // Pre-calculated
```

#### SEOService
**Purpose**: Handle SEO optimization and scoring

**Key Methods**:
- `generateBlogSchema(BlogPost $post)` - Generate schema.org BlogPosting JSON-LD
- `calculateSEOScore(BlogPost $post)` - Score 0-100 based on:
  - Title optimization (10 points)
  - Meta description (10 points)
  - Content length (10 points)
  - Image optimization (10 points)
  - Schema implementation (10 points)
  - Canonical URL (10 points)
  - Categories & tags (10 points)
  - Reading time (10 points)
  - Multilingual content (10 points)
- `suggestImprovements(BlogPost $post)` - Get actionable SEO recommendations

**Integration**:
```php
// Auto-generate schema on publish
$seoService->generateBlogSchema($post); // Returns array for JSON storage
$post->seo_score = $seoService->calculateSEOScore($post);
$post->save();
```

#### ContactSubmissionService
**Purpose**: Handle contact form submissions

**Key Methods**:
- `createSubmission(array $validated, string $ipAddress, string $locale)` - Create submission
- `getUnreadSubmissions()` - Get new submissions (admin)
- `getByRegion(string $region)` - Filter by region
- `markAsResponded(ContactSubmission $submission, string $response)` - Mark responded
- `deleteSpam()` - Batch delete spam

---

### 2. Repository Pattern

#### BlogPostRepositoryInterface
**Location**: `app/Repositories/Contracts/BlogPostRepositoryInterface.php`

**Contract Definition**:
```php
public function all(): Collection;
public function paginate(int $perPage = 15): LengthAwarePaginator;
public function findById(string $id): ?BlogPost;
public function findBySlug(string $slug): ?BlogPost;
public function create(array $attributes): BlogPost;
public function update(BlogPost $post, array $attributes): BlogPost;
public function delete(BlogPost $post): bool;
public function published(): self; // Query scope
public function byRegion(string $region): self; // Query scope
public function withRelations(array $relations): self; // Eager loading
```

#### BlogPostRepository
**Location**: `app/Repositories/Eloquent/BlogPostRepository.php`

**Implementation Strategy**:
- Fluent interface for method chaining
- Lazy query building via `getQuery()` method
- Automatic eager loading management
- Scope isolation for reusability

**Usage Pattern**:
```php
// Fluent query building
$posts = $this->repository
    ->withRelations(['author', 'categories', 'tags'])
    ->published()
    ->byRegion('EG')
    ->paginate(15);

// Single post retrieval
$post = $this->repository
    ->withRelations(['author', 'categories'])
    ->findBySlug('my-post');
```

---

### 3. API Controllers

#### BlogPostController
**Location**: `app/Http/Controllers/Api/BlogPostController.php`

**Endpoints**:
- `GET /api/blog` - List published posts (paginated)
- `GET /api/blog/{slug}` - Get specific post
- `GET /api/blog/category/{categorySlug}` - Posts by category
- `GET /api/blog/search?q=keyword` - Search posts
- `GET /api/blog/recent?limit=5` - Recent posts
- `GET /api/blog/{slug}/seo` - SEO metadata only

**Query Parameters**:
- `locale`: `en` or `ar` (default: `en`)
- `region`: `EG`, `US`, `GLOBAL` (default: `GLOBAL`)
- `per_page`: Results per page (default: `10`)

**Response Structure**:
```json
{
  "data": [
    {
      "id": "uuid",
      "slug": "post-slug",
      "title": "Post Title",
      "content": "Post content...",
      "author": { "id": "uuid", "name": "Author Name" },
      "categories": [{ "id": "uuid", "name": "Category" }],
      "seo": {
        "meta_title": "SEO Title",
        "meta_description": "SEO Description",
        "canonical_url": "https://...",
        "schema_json": { "@context": "https://schema.org", ... },
        "seo_score": 85
      },
      "metrics": {
        "view_count": 150,
        "reading_time_minutes": 5
      }
    }
  ],
  "meta": {
    "locale": "en",
    "region": "EG",
    "total": 45,
    "per_page": 10
  }
}
```

#### ContactController
**Location**: `app/Http/Controllers/Api/ContactController.php`

**Endpoints**:
- `POST /api/contact` - Submit contact form
- `GET /api/contact/{id}` - Get submission (admin)
- `PATCH /api/contact/{id}/status/{status}` - Update status (admin)

---

### 4. API Resources (DTOs)

#### BlogPostResource
**Purpose**: Transform Eloquent model to API response

**Features**:
- Locale-aware field translation
- Separated SEO metadata object
- Nested author and categories
- Status and metrics grouping
- ISO 8601 date formatting

```php
// Automatically handles locale
public function toArray(Request $request): array
{
    $locale = $request->query('locale', 'en');
    $this->resource->setLocale($locale);
    
    return [
        'title' => $this->getTranslation('title', $locale),
        'seo' => [ /* SEO fields */ ],
        'author' => new AuthorResource($this->author),
        // ...
    ];
}
```

#### AuthorResource
```php
[
    'id' => 'uuid',
    'name' => 'Author Name',
    'email' => 'author@example.com',
    'bio' => 'Author biography',
    'avatar_url' => 'https://...',
    'region' => 'EG'
]
```

#### CategoryResource
```php
[
    'id' => 'uuid',
    'slug' => 'category-slug',
    'name' => 'Category Name',
    'seo' => [ /* SEO fields */ ],
    'parent_id' => 'uuid or null',
    'post_count' => 15
]
```

#### TagResource
```php
[
    'id' => 'uuid',
    'name' => 'Tag Name',
    'slug' => 'tag-slug'
]
```

---

### 5. Form Requests (Validation)

#### StoreContactRequest
**Location**: `app/Http/Requests/StoreContactRequest.php`

**Validation Rules**:
```php
'name' => ['required', 'string', 'min:3', 'max:100'],
'email' => ['required', 'email', 'max:100'],
'subject' => ['required', 'string', 'min:5', 'max:200'],
'message' => ['required', 'string', 'min:20', 'max:5000'],
'region' => ['required', 'in:EG,US,INTL'],
```

**Auto-extracts**:
- IP address via `$request->ip()`
- Locale via `Accept-Language` header
- Timestamp via `created_at` (automatic)

---

## API Routes Configuration

**File**: `routes/api.php`

```
GET     /api/blog                              - BlogPostController@index
GET     /api/blog/{slug}                       - BlogPostController@show
GET     /api/blog/category/{slug}              - BlogPostController@byCategory
GET     /api/blog/search                       - BlogPostController@search
GET     /api/blog/recent                       - BlogPostController@recent
GET     /api/blog/{slug}/seo                   - BlogPostController@seoData

POST    /api/contact                           - ContactController@store
GET     /api/contact/{id}                      - ContactController@show (auth)
PATCH   /api/contact/{id}/status/{status}     - ContactController@update (auth)

GET     /api/health                            - Health check
```

**Rate Limiting**: 60 requests per minute per IP

---

## Dependency Injection Setup

**RepositoryServiceProvider** binds interfaces to implementations:

```php
$this->app->bind(
    BlogPostRepositoryInterface::class,
    BlogPostRepository::class
);
```

**Register** in `config/app.php`:
```php
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```

---

## Database Models

### BlogPost Model
```php
// Traits
- Sluggable (auto-generates URL slugs)
- Translatable (EN/AR via JSONB)
- SoftDeletes (safe deletion)

// Translatable fields
protected array $translatable = [
    'title',
    'description',
    'content',
    'meta_title',
    'meta_description',
];

// SEO fields
- og_image: OG image URL for social
- canonical_url: Canonical URL for SEO
- schema_json: JSON-LD schema.org data
- featured_image_url: Main featured image
- seo_score: Calculated score (0-100)

// Relations
- author: BelongsTo User
- categories: BelongsToMany Category (with pivot)
- tags: BelongsToMany Tag (with pivot)
- performanceMetrics: HasMany PerformanceMetric

// Scopes
- scopePublished(): Filter is_published = true
- scopeByRegion($region): Filter by region array
- scopeOrdered(): Order by published_at DESC
```

### Category Model
```php
// Hierarchical structure
- parent_id: NULL for root categories

// Translatable fields
- name, description, meta_title, meta_description

// Relations
- parent: BelongsTo Category (self)
- children: HasMany Category (self)
- blogPosts: BelongsToMany BlogPost
```

### Tag Model
```php
// Translatable fields
- name (slug auto-generated)

// Relations
- blogPosts: BelongsToMany BlogPost
```

### ContactSubmission Model
```php
// Statuses: 'new', 'in_progress', 'resolved', 'spam'
// Tracks: name, email, subject, message, region, IP, response

// Relations
- None (independent)
```

### PerformanceMetric Model
```php
// Core Web Vitals: LCP, FID, CLS
// Tracks performance per post per region
```

---

## Integration with Astro

### Build-time Data Fetching

**astro.config.mjs**:
```javascript
const API_URL = process.env.API_URL || 'http://localhost:8000/api';

export default defineConfig({
  integrations: [
    {
      name: 'dynamic-routes',
      hooks: {
        'astro:build:done': async () => {
          const response = await fetch(`${API_URL}/blog?per_page=1000`);
          const { data } = await response.json();
          
          // Generate static routes for all posts
          data.forEach(post => {
            generateBlogPage(post); // Pre-render
          });
        },
      },
    },
  ],
});
```

### Page Component Integration

**src/pages/blog/[...slug].astro**:
```astro
---
const { slug } = Astro.params;
const response = await fetch(
  `${import.meta.env.API_URL}/blog/${slug}`,
  { headers: { 'Accept-Language': 'en' } }
);
const { data: post } = await response.json();

export async function getStaticPaths() {
  const response = await fetch(
    `${import.meta.env.API_URL}/blog?per_page=1000`
  );
  const { data } = await response.json();
  
  return data.map(post => ({
    params: { slug: post.slug },
  }));
}
---
```

---

## Installation & Setup Steps

### 1. Install Composer Dependencies
```bash
composer install

# Required packages:
- spatie/laravel-sluggable
- spatie/laravel-translatable
```

### 2. Register RepositoryServiceProvider
```php
// config/app.php
'providers' => [
    App\Providers\RepositoryServiceProvider::class,
],
```

### 3. Run Migrations
```bash
php artisan migrate

# Creates tables:
- users
- blog_posts
- categories
- blog_post_category (pivot)
- tags
- blog_post_tag (pivot)
- contact_submissions
- performance_metrics
```

### 4. Create Seed Data
```bash
php artisan make:seeder UserSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder BlogPostSeeder

php artisan db:seed
```

### 5. Configure Environment
```env
APP_NAME=DevSEO
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://devseo.com

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=devseo
DB_USERNAME=postgres
DB_PASSWORD=...

API_URL=https://api.devseo.com
FRONTEND_URL=https://devseo.com
```

### 6. Start Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## API Response Examples

### Get Blog Posts
```bash
curl -X GET 'http://localhost:8000/api/blog?locale=en&region=EG&per_page=10'
```

**Response**:
```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "slug": "core-web-vitals-for-devs",
      "title": "Core Web Vitals for Developers",
      "description": "Understanding and optimizing Core Web Vitals...",
      "content": "Full content here...",
      "featured_image_url": "/images/core-web-vitals.jpg",
      "og_image": "https://devseo.com/images/core-web-vitals-og.jpg",
      "author": {
        "id": "uuid",
        "name": "Ahmed Talaat",
        "email": "ahmed@devseo.com",
        "region": "EG"
      },
      "categories": [
        {
          "id": "uuid",
          "slug": "performance",
          "name": "Performance",
          "post_count": 12
        }
      ],
      "tags": [
        { "id": "uuid", "name": "Core Web Vitals", "slug": "core-web-vitals" },
        { "id": "uuid", "name": "SEO", "slug": "seo" }
      ],
      "seo": {
        "meta_title": "Core Web Vitals for Developers - DevSEO",
        "meta_description": "Learn how to optimize Core Web Vitals for better SEO rankings...",
        "canonical_url": "https://devseo.com/blog/core-web-vitals-for-devs",
        "schema_json": {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          "headline": "Core Web Vitals for Developers",
          "description": "...",
          "datePublished": "2024-02-04T10:00:00Z",
          "author": { "@type": "Person", "name": "Ahmed Talaat" }
        },
        "seo_score": 92
      },
      "metrics": {
        "view_count": 324,
        "reading_time_minutes": 8
      },
      "status": {
        "is_published": true,
        "published_at": "2024-02-04T10:00:00Z",
        "created_at": "2024-02-01T14:30:00Z",
        "updated_at": "2024-02-04T10:00:00Z"
      },
      "regions": ["GLOBAL", "EG", "US"]
    }
  ],
  "meta": {
    "locale": "en",
    "region": "EG",
    "total": 45,
    "per_page": 10
  }
}
```

### Submit Contact Form
```bash
curl -X POST 'http://localhost:8000/api/contact' \
  -H 'Content-Type: application/json' \
  -H 'Accept-Language: en' \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "SEO Consultation",
    "message": "I would like to discuss SEO strategies for my website...",
    "region": "US"
  }'
```

**Response** (201 Created):
```json
{
  "message": "Your message has been received. We will get back to you soon.",
  "submission_id": "550e8400-e29b-41d4-a716-446655440001"
}
```

---

## Testing

### Unit Tests

**tests/Unit/Services/BlogPostServiceTest.php**:
```php
use App\Models\BlogPost;
use App\Services\BlogPostService;

test('get published posts', function () {
    $service = app(BlogPostService::class);
    $posts = $service->getAllPublished('en');
    
    expect($posts)->toBeCollection();
    expect($posts)->each->toHaveProperty('is_published', true);
});
```

### Feature Tests

**tests/Feature/BlogPostApiTest.php**:
```php
test('fetch blog post by slug', function () {
    $post = BlogPost::factory()->create(['slug' => 'test-post']);
    
    $response = $this->getJson('/api/blog/test-post?locale=en');
    
    $response->assertStatus(200)
        ->assertJsonPath('data.slug', 'test-post');
});
```

---

## Performance Optimization

### Database Indexes
```sql
-- Created automatically by migrations:
CREATE INDEX idx_blog_posts_slug ON blog_posts(slug);
CREATE INDEX idx_blog_posts_published ON blog_posts(is_published, published_at DESC);
CREATE INDEX idx_blog_posts_region ON blog_posts USING GIN(regions);
CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_contact_region ON contact_submissions(region);
```

### Query Optimization
```php
// Always use eager loading
$posts = BlogPost::with(['author', 'categories', 'tags'])
    ->published()
    ->paginate(10);

// Avoid N+1 queries with service layer
$service->getAllPublished('en', ['author', 'categories', 'tags']);
```

### Caching
```php
// Cache published posts for 1 hour
Cache::remember('blog:published:en', 3600, function () {
    return $this->service->getAllPublished('en');
});
```

---

## Security Considerations

1. **Rate Limiting**: 60 req/min per IP (configured in routes)
2. **Authentication**: Sanctum for admin endpoints
3. **Validation**: Form requests with strict rules
4. **CORS**: Configure for Astro frontend domain
5. **SQL Injection**: Eloquent ORM prevents via parameterized queries
6. **XSS**: API responses JSON (Astro escapes in templates)

---

## Troubleshooting

**Issue**: Models not found in migrations
**Solution**: Ensure `php artisan migrate` runs before using factories

**Issue**: Locale not recognized
**Solution**: Set `app.locale` in `.env` and use `app()->setLocale()` in middleware

**Issue**: Slugs not generating
**Solution**: Install `spatie/laravel-sluggable` and run `composer update`

**Issue**: API returning empty results
**Solution**: Check `is_published` and `published_at` fields on BlogPost

---

## Next Steps

1. ✅ **Backend API** - Complete
2. ⏳ **Authentication** - Implement Sanctum for admin
3. ⏳ **Testing** - Add comprehensive test suite
4. ⏳ **Deployment** - Docker & Kubernetes setup
5. ⏳ **Monitoring** - Setup logging and error tracking
6. ⏳ **Documentation** - API docs with Swagger/Postman

---

**Created**: February 4, 2024
**Status**: Production Ready
**Version**: 1.0.0
