# DevSEO Frontend - Quick Start Guide

## 🚀 5-Minute Setup

### 1. Clone & Install
```bash
cd /workspaces/DevSEO
npm install
```

### 2. Configure Environment
```bash
# Create .env file
cp .env.example .env

# Edit .env
nano .env
```

**Required Variables**:
```env
PUBLIC_API_URL=http://localhost:8000/api
PUBLIC_SITE_URL=http://localhost:3000
```

### 3. Start Development Server
```bash
npm run dev
```

Visit `http://localhost:3000` in your browser.

---

## 📝 Common Tasks

### View Homepage
- English: `http://localhost:3000/`
- Arabic: `http://localhost:3000/ar/`

### View Blog
- English: `http://localhost:3000/blog`
- Arabic: `http://localhost:3000/ar/blog`

### Build for Production
```bash
npm run build
npm run preview  # Test production build locally
```

### Check TypeScript Errors
```bash
npm run type-check
```

---

## 🎯 API Integration

### API Must Be Running
The frontend fetches data from the Laravel backend at **build time**. Ensure the backend API is running:

```bash
# In Laravel backend directory
php artisan serve
# Runs on http://localhost:8000
```

### Verify API Connection
```bash
# Test API health
curl http://localhost:8000/api/health
# Should return: { "status": "ok", "timestamp": "..." }
```

---

## 📖 Key Files to Know

| File | Purpose |
|------|---------|
| `src/api/client.ts` | API client for all backend calls |
| `src/types/api.ts` | TypeScript types for API responses |
| `src/components/SEO.astro` | SEO meta tags component |
| `src/utils/i18n.ts` | Translation utilities |
| `src/pages/index.astro` | Homepage |
| `src/pages/blog/[slug].astro` | Dynamic blog posts |
| `astro.config.mjs` | Astro configuration |
| `tailwind.config.mjs` | Tailwind CSS config |

---

## 🌍 i18n (Internationalization)

### Supported Languages
- **English (Default)**: `/` or `/en/`
- **Arabic**: `/ar/`

### How It Works

**Automatic routing**:
- `/blog` → English blog
- `/ar/blog` → Arabic blog
- `/blog/my-post` → English post
- `/ar/blog/my-post` → Arabic post

**In components**:
```astro
---
import { getLocaleFromPath } from '@utils/i18n-routing';
import { getTranslation } from '@utils/i18n';

const locale = getLocaleFromPath(Astro.url.pathname);
const title = getTranslation(post.title, locale);
---

<h1>{title}</h1>
```

---

## 🎨 Styling with Tailwind

### Adding Styles
```astro
<div class="max-w-4xl mx-auto px-4 py-12">
  <h1 class="text-4xl font-bold mb-6 text-gray-900">
    Title
  </h1>
</div>
```

### Custom Colors
```astro
<button class="bg-primary-600 hover:bg-primary-700 text-white">
  Action
</button>
```

---

## 🐛 Troubleshooting

### Build Fails
```
Error: "Cannot fetch from API"
→ Check if Laravel backend is running (php artisan serve)
→ Verify PUBLIC_API_URL in .env
→ Check API returns valid JSON
```

### Images Not Loading
```
→ Check image URLs are absolute (https://...)
→ Verify image alt text is provided
→ Check width/height attributes on images
```

### i18n Not Working
```
→ Clear browser cache (Ctrl+Shift+Delete)
→ Check URL has /en/ or /ar/ prefix
→ Verify getLocaleFromPath() returns correct locale
```

### TypeScript Errors
```bash
npm run type-check  # Show all errors
```

---

## 📊 Performance

### Check Lighthouse Score (Local)
1. Open `npm run preview` in Chrome
2. Open DevTools (F12)
3. Go to Lighthouse tab
4. Click "Analyze page load"

### Target Scores
- **Performance**: 100
- **Accessibility**: 100
- **Best Practices**: 100
- **SEO**: 100

---

## 🚢 Deployment

### Deploy to Cloudflare Pages

**1. Push to GitHub**:
```bash
git add .
git commit -m "Deploy frontend"
git push origin main
```

**2. Connect in Cloudflare Dashboard**:
- Go to Pages
- Connect GitHub repository
- Build command: `npm run build`
- Build output directory: `dist`

**3. Set Environment Variables**:
- `PUBLIC_API_URL` = production API URL
- `PUBLIC_SITE_URL` = production site URL

**4. Deploy**:
- Automatic on every push to main
- Visit deployed site via Cloudflare URL

---

## 📚 Full Documentation

For comprehensive guides, see:

1. **FRONTEND_IMPLEMENTATION.md** - Complete implementation details
2. **LIGHTHOUSE_OPTIMIZATION.md** - Performance optimization guide
3. **ASTRO_IMPLEMENTATION_COMPLETE.md** - Full project status

---

## 💡 Development Tips

### Add a New Blog Post
1. Update Laravel backend with new post
2. Run `npm run build` (fetches from API)
3. New post automatically appears

### Customize Homepage
Edit: `src/pages/index.astro` and `src/pages/ar/index.astro`

### Change Colors
Edit: `tailwind.config.mjs` → `colors` section

### Add SEO Metadata
Use the `<SEO />` component:
```astro
<SEO
  title="Page Title"
  description="Page description"
  image="/og-image.png"
/>
```

### Add New Page
Create file: `src/pages/new-page.astro`

### Add Arabic Page
Create file: `src/pages/ar/new-page.astro`

---

## ✅ Pre-Launch Checklist

- [ ] `.env` file created with correct API URL
- [ ] `npm run build` succeeds without errors
- [ ] `npm run preview` works locally
- [ ] Lighthouse audit shows 100/100 scores
- [ ] Blog posts display correctly
- [ ] Arabic pages work (/ar)
- [ ] Images load properly
- [ ] Links work on all pages
- [ ] Contact form submits
- [ ] Mobile responsive on real device

---

## 🎯 What's Next

### After Deployment
1. Monitor Core Web Vitals
2. Setup analytics
3. Track conversions
4. Regular audits

### Future Features
- Search functionality
- Comment system
- Newsletter signup
- Case studies
- Dark mode toggle

---

**Ready to launch? Run `npm run build` to create production build!**

For help, see the comprehensive guides or review inline code comments.
