# DevSEO Architecture Blueprint

## Tech Stack
- **Frontend**: Astro 5 (SSG) + Minimal JS
- **Backend**: Laravel 11 (API-only, separate repo/container)
- **Database**: PostgreSQL (international scaling ready)
- **Deployment**: Cloudflare Pages (Frontend) + Render/Railway (API)
- **CDN**: Cloudflare + Image Optimization

---

## I. FRONTEND - ASTRO OPTIMIZATION

### 1. Performance Targets
- **Core Web Vitals**: LCP < 1.5s, FID < 100ms, CLS < 0.1
- **Total Bundle**: < 350KB (current: 252KB ✓)
- **JavaScript**: < 50KB (zero heavy frameworks)
- **Time to Interactive**: < 2s

### 2. SEO Architecture
- ✅ Static Site Generation (SSG) - maximum crawlability
- ✅ Sitemap generation (via @astrojs/sitemap)
- ✅ Dynamic robots.txt based on environment
- ✅ Open Graph + Twitter Cards metadata
- ✅ Canonical URLs (prevent duplicate content)
- ✅ Schema.org structured data (JSON-LD)

### 3. Astro Configuration Best Practices
```javascript
// Optimizations to implement:
- Enable image optimization with @astrojs/image
- Configure HTTP caching headers
- Enable precompression (gzip/brotli)
- Disable unused features (React, Vue, etc.)
- Configure sitemap with i18n for Egypt/USA locales
```

### 4. Content Strategy
- **Blog**: Static markdown for SEO (current setup ✓)
- **Case Studies**: Pre-rendered HTML
- **API Data**: Fetched at build-time, cached
- **Dynamic Content**: Minimal; use edge functions if needed

---

## II. BACKEND - LARAVEL API

### 1. API Structure (SOLID Principles)
```
app/
├── Http/Controllers/     # Thin controllers (route handling only)
├── Actions/              # Business logic (single responsibility)
├── Models/               # Eloquent + relationship definitions
├── Services/             # Complex operations (DIP)
├── Repositories/         # Data access abstraction (LSP)
├── Requests/             # Form request validation
├── Resources/            # API response transformation
└── Exceptions/           # Custom exception handling
```

### 2. Core Endpoints (MVP)
- `GET /api/blog` - List posts (paginated)
- `GET /api/blog/{slug}` - Single post
- `POST /api/contact` - Contact form submission
- `GET /api/stats` - Public analytics (performance metrics)

### 3. Database Schema - International Ready
```sql
-- Users/Authors (multi-region support)
CREATE TABLE users (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    region ENUM('EG', 'US', 'INTL'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Blog Posts (SEO-optimized)
CREATE TABLE blog_posts (
    id UUID PRIMARY KEY,
    slug VARCHAR(255) UNIQUE INDEX,
    title VARCHAR(255),
    description TEXT,
    content TEXT,
    author_id UUID REFERENCES users(id),
    region ENUM('EG', 'US', 'GLOBAL') DEFAULT 'GLOBAL',
    seo_score INT,
    published_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Contact submissions
CREATE TABLE contact_submissions (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    region VARCHAR(50),
    message TEXT,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP
);

-- Performance metrics (technical SEO)
CREATE TABLE performance_metrics (
    id UUID PRIMARY KEY,
    page_slug VARCHAR(255),
    lcp DECIMAL(5,2),
    fid DECIMAL(5,2),
    cls DECIMAL(5,3),
    measured_at TIMESTAMP,
    region VARCHAR(50)
);
```

---

## III. INTEGRATION STRATEGY

### 1. Build-Time Data Fetching
```javascript
// Astro fetches blog data during build
const posts = await fetch('https://api.devseo.dev/api/blog');
// Generates static pages for each post
```

### 2. Edge Function for Real-Time Data
- Contact form submissions → Lambda/Cloudflare Function → Laravel API
- Regional redirects based on GeoIP
- Dynamic meta tags for social sharing

### 3. Caching Strategy
```
- Astro static assets: Cache-Control: max-age=31536000
- HTML pages: Cache-Control: max-age=3600, stale-while-revalidate=86400
- API responses: Cache-Control: max-age=300 (5 min)
```

---

## IV. CLEAN CODE & SOLID PRINCIPLES

### Single Responsibility Principle
- Controllers: route handling only
- Services: business logic isolated
- Repositories: data access only

### Open/Closed Principle
- Use interfaces for extensibility
- Example: `BlogRepositoryInterface` with multiple implementations

### Liskov Substitution Principle
- All repositories implement same contract
- Easy to swap implementations (DB, Cache, etc.)

### Interface Segregation Principle
- Small, focused interfaces
- Example: `SEOAnalyzerInterface` vs broad `BlogServiceInterface`

### Dependency Inversion Principle
- Inject interfaces, not concrete classes
- Service container manages dependencies

---

## V. TECHNICAL SEO IMPLEMENTATION

### 1. Crawler Optimization
- ✅ XML sitemap with priority/frequency
- ✅ robots.txt with sitemap location
- ✅ Structured data (Article schema for blog posts)
- ✅ Breadcrumb schema for navigation

### 2. Performance SEO
- ✅ Image optimization (WebP, srcset)
- ✅ Code splitting (minimal JS per page)
- ✅ Critical CSS inlining
- ✅ Lazy loading for below-fold content

### 3. Internationalization (i18n)
- Hreflang tags for Egypt/USA locales
- Regional sitemap variants
- Geo-targeted content hints

### 4. Metadata Management
```astro
// Every page must have:
- <title> (50-60 chars)
- <meta name="description"> (150-160 chars)
- <meta property="og:image"> (1200x630)
- <link rel="canonical">
- <script type="application/ld+json"> (schema)
```

---

## VI. DEPLOYMENT & MONITORING

### Frontend (Astro)
- **Hosting**: Cloudflare Pages (automatic deployments)
- **Monitoring**: Cloudflare Analytics Engine
- **Alerts**: Performance degradation > 2s LCP

### Backend (Laravel)
- **Hosting**: Render/Railway (containerized)
- **Database**: PostgreSQL (managed)
- **Monitoring**: APM (New Relic/DataDog)
- **Logs**: Centralized logging (Sentry/LogRocket)

### CI/CD Pipeline
```yaml
On push to main:
1. Run tests (PHPUnit, Jest)
2. Lint code (PHP-CS-Fixer, Prettier)
3. Build Astro (SSG)
4. Deploy to Cloudflare Pages
5. Run performance tests
```

---

## VII. SECURITY CONSIDERATIONS

- CORS properly configured for API
- Rate limiting on contact form endpoint
- Input validation (Laravel Form Requests)
- CSRF protection disabled for stateless API
- Content Security Policy headers
- SQL injection prevention (Eloquent ORM)

---

## VIII. SCALABILITY ROADMAP

### Phase 1 (Current)
- Static blog content
- Basic contact form
- Simple API endpoints

### Phase 2 (Month 2)
- User authentication (JWT)
- Case studies database
- Comment system (moderated)

### Phase 3 (Month 3)
- Multi-language support
- Advanced filtering/search
- Community contributions

---

## IX. KEY METRICS

Track these monthly:
- **SEO**: Organic impressions, CTR, position (Search Console)
- **Performance**: LCP, FID, CLS, TTFB
- **Conversion**: Contact form submissions, email subscribers
- **Traffic**: Sessions, unique users, regional breakdown
- **Technical**: API response time, database query time, error rates

---

## Notes

This architecture prioritizes:
1. **Performance**: Every optimization counts for SEO
2. **Maintainability**: Clean code = easier scaling
3. **Scalability**: International support from day one
4. **Security**: API-first approach isolates concerns

All design decisions are intentional—no premature optimization, only proven patterns.
