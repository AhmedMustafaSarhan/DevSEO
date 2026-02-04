# Astro Frontend Implementation Guide

## Overview

The DevSEO frontend has been built with **Astro.js** for maximum performance and SEO optimization. This document outlines the complete implementation, architecture, and usage patterns.

## 🏗️ Architecture

### Tech Stack
- **Framework**: Astro 5.17.1 (Static Site Generation)
- **Styling**: Tailwind CSS 3.4.1
- **Type Safety**: TypeScript 5.3.3
- **API Integration**: Native Fetch API
- **i18n**: Custom routing system (/en, /ar)

### Project Structure

```
src/
├── api/
│   └── client.ts           # API client for Laravel backend
├── types/
│   └── api.ts              # TypeScript types matching Laravel schema
├── utils/
│   ├── i18n.ts             # Translation & localization helpers
│   ├── i18n-routing.ts     # i18n routing utilities
│   └── api-helpers.ts      # API call wrappers & static generation
├── components/
│   ├── SEO.astro           # Reusable SEO component with meta tags
│   ├── Header.astro        # Navigation header
│   ├── Footer.astro        # Footer
│   └── [...other components]
├── layouts/
│   ├── Main.astro          # Primary layout with SEO
│   └── BlogPost.astro      # Blog post template
├── pages/
│   ├── index.astro         # Homepage (/en)
│   ├── blog/
│   │   ├── index.astro     # Blog listing
│   │   └── [slug].astro    # Dynamic blog posts (SSG)
│   ├── ar/
│   │   ├── index.astro     # Arabic homepage
│   │   └── blog/[slug].astro
│   └── about.astro
├── styles/
│   └── global.css          # Global styles & CSS variables
└── consts.ts               # Site constants
```

## 🔌 API Integration

### API Client Usage

```typescript
import ApiClient from '@api/client';

// Fetch blog posts
const posts = await ApiClient.getBlogPosts(page, limit, region);

// Get single post by slug
const post = await ApiClient.getBlogPostBySlug('seo-guide');

// Get categories
const categories = await ApiClient.getCategories();

// Submit contact form
await ApiClient.submitContactForm({
  name: 'John Doe',
  email: 'john@example.com',
  subject: 'Topic',
  message: 'Your message',
  region: 'US',
});
```

### API Endpoints

All endpoints are defined in `src/api/client.ts`:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/blog` | GET | List blog posts (paginated) |
| `/blog/{slug}` | GET | Get single blog post |
| `/blog/category/{slug}` | GET | Get posts by category |
| `/blog/search?q=...` | GET | Search posts |
| `/blog/recent?limit=5` | GET | Get recent posts |
| `/blog/{slug}/seo` | GET | Get SEO metadata |
| `/categories` | GET | List all categories |
| `/tags` | GET | List all tags |
| `/contact` | POST | Submit contact form |
| `/health` | GET | Health check |

## 🌍 Internationalization

### Locale Routing

The site supports **English** (/en) and **Arabic** (/ar):

```
/                    → English homepage (default)
/blog                → English blog listing
/blog/[slug]         → English blog post

/ar                  → Arabic homepage
/ar/blog             → Arabic blog listing
/ar/blog/[slug]      → Arabic blog post
```

### Using i18n Utilities

```typescript
import { getLocaleFromPath, buildLocalizedPath, switchLocale } from '@utils/i18n-routing';
import { getTranslation, formatDate, isRTL } from '@utils/i18n';

// Get current locale from URL
const locale = getLocaleFromPath(Astro.url.pathname); // 'en' or 'ar'

// Build localized path
const path = buildLocalizedPath('/blog', 'ar'); // '/ar/blog'

// Switch locale for current path
const arUrl = switchLocale(Astro.url.pathname, 'ar');

// Get translation from translatable field
const title = getTranslation(post.title, locale);

// Format date for locale
const dateString = formatDate(post.created_at, locale);

// Check if RTL
const rtl = isRTL(locale); // true for 'ar'
```

### Translatable Fields

Fields from Laravel API that support translations (using Spatie Translatable):

```typescript
interface BlogPost {
  title: string | Record<string, string>; // { en: '...', ar: '...' }
  description: string | Record<string, string>;
  content: string | Record<string, string>;
  meta_title?: string | Record<string, string>;
  meta_description?: string | Record<string, string>;
}
```

**Getting translations**:

```astro
---
const title = getTranslation(post.title, locale);
// Returns English or Arabic title based on locale
---
```

## 🚀 Static Site Generation

### Dynamic Routes with getStaticPaths

Blog posts are pre-rendered at build time for maximum performance:

```astro
---
export const getStaticPaths: GetStaticPaths = async () => {
  const routes = await generateBlogPostRoutes();
  return routes.map(route => ({
    params: { slug: route.params.slug },
    props: { post: route.props.post },
  }));
};
---
```

**Build Process**:
1. `npm run build` starts the build
2. `getStaticPaths` fetches all published posts from API
3. Astro generates static HTML for each post
4. Build creates optimal directory structure
5. Deploy to Cloudflare Pages (no serverless needed)

## 📝 SEO Implementation

### SEO Component

The `<SEO />` component renders all required meta tags:

```astro
<SEO
  title="Article Title"
  description="Article description"
  image="/og-image.png"
  ogImage="/og-image.png"
  canonical="https://devseo.dev/blog/article"
  schema={{
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    // ... structured data
  }}
  locale="en"
/>
```

### Generated Meta Tags

```html
<!-- Basic -->
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="..." />
<meta name="robots" content="index, follow" />

<!-- Canonical -->
<link rel="canonical" href="https://devseo.dev/..." />

<!-- Open Graph (Facebook/LinkedIn) -->
<meta property="og:type" content="website" />
<meta property="og:title" content="..." />
<meta property="og:description" content="..." />
<meta property="og:image" content="..." />

<!-- Twitter Cards -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:title" content="..." />
<meta property="twitter:description" content="..." />
<meta property="twitter:image" content="..." />

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
  { "@context": "https://schema.org", ... }
</script>

<!-- Internationalization -->
<link rel="alternate" hreflang="ar" href="..." />
<link rel="alternate" hreflang="en" href="..." />
```

### Dynamic Sitemap

Automatically generated at `sitemap-0.xml` with:
- All published blog posts
- Categories and tags
- Change frequency (weekly)
- Priority levels
- Multi-language variants

## 💅 Styling

### Tailwind CSS

Tailwind is configured with:

```javascript
{
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,ts,tsx}'],
  theme: {
    extend: {
      colors: { /* Primary: Blue, Secondary: Purple */ },
      typography: { /* Article typography styles */ },
    }
  },
  plugins: ['@tailwindcss/typography', '@tailwindcss/forms'],
}
```

### Global Styles

```css
/* /src/styles/global.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom utility classes */
.container-max { @apply max-w-6xl mx-auto px-4; }
```

### Using Tailwind in Astro

```astro
<div class="max-w-4xl mx-auto px-4 py-12">
  <h1 class="text-4xl font-bold mb-6 text-gray-900">
    Title
  </h1>
  <p class="text-lg text-gray-600 leading-relaxed">
    Content
  </p>
</div>
```

## 🎯 Performance Targets

### Core Web Vitals

- **LCP** (Largest Contentful Paint): < 1.5s
- **FID** (First Input Delay): < 100ms
- **CLS** (Cumulative Layout Shift): < 0.1

### Bundle Metrics

- **Total JS**: < 50KB (gzipped)
- **Total CSS**: < 30KB (gzipped, after Tailwind purging)
- **HTML**: < 100KB

### Lighthouse Scores

Target: **100/100** across all categories:
- Performance
- Accessibility
- Best Practices
- SEO

See [LIGHTHOUSE_OPTIMIZATION.md](LIGHTHOUSE_OPTIMIZATION.md) for detailed optimization strategies.

## 🛠️ Development

### Setup

```bash
# Install dependencies (Tailwind, Astro, etc. are already in package.json)
npm install

# Create .env file
cp .env.example .env
# Edit .env with your API URL
```

### Commands

```bash
# Development server (http://localhost:3000)
npm run dev

# Build for production
npm run build

# Preview built site
npm run preview

# Type checking
npm run type-check

# Linting
npm run lint
```

### Environment Variables

```env
PUBLIC_API_URL=http://localhost:8000/api
PUBLIC_SITE_URL=http://localhost:3000
NODE_ENV=development
PUBLIC_ENABLE_SEARCH=true
```

## 📦 Building & Deployment

### Build Output

```bash
npm run build
# Creates /dist directory with:
# - Pre-rendered HTML files
# - Optimized CSS/JS in /_astro/
# - Static assets (images, fonts)
# - Sitemap and robots.txt
```

### Deployment to Cloudflare Pages

```bash
# Push to GitHub
git push origin main

# Cloudflare automatically:
# 1. Runs "npm run build"
# 2. Deploys /dist to CDN
# 3. Sets cache headers
# 4. Enables automatic HTTPS
```

### Production Checklist

- [ ] Set correct `site:` in astro.config.mjs
- [ ] Verify `PUBLIC_API_URL` points to production API
- [ ] Review meta tags on key pages
- [ ] Test dynamic routes (blog posts)
- [ ] Verify i18n routing works
- [ ] Run Lighthouse audit
- [ ] Test on real 4G device
- [ ] Setup analytics/monitoring

## 🐛 Troubleshooting

### API Connection Issues

```
Error: "API Error: 404 Not Found"
→ Check PUBLIC_API_URL in .env
→ Verify Laravel backend is running
→ Check CORS headers on API
```

### Build Fails on getStaticPaths

```
Error: "Cannot read property 'map' of undefined"
→ Verify API is returning valid JSON
→ Check blog posts have 'published' = true
→ Add error handling in api-helpers
```

### Images Not Loading

```
→ Verify image URLs are absolute (https://...)
→ Check Cloudflare Image Optimization is enabled
→ Ensure image has explicit width/height
```

### RTL Layout Issues

```
→ Use isRTL(locale) to conditionally apply flexbox direction
→ Use flex-row-reverse for RTL layouts
→ Test in Arabic mode at /ar
```

## 📚 Additional Resources

- [Astro Documentation](https://docs.astro.build)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [Web.dev SEO Guide](https://web.dev/lighthouse-seo/)
- [Core Web Vitals Guide](https://web.dev/vitals/)

---

**Status**: ✅ Implementation Complete
**Last Updated**: February 4, 2024
**Performance Score**: 100/100 (Target)
