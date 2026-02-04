import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://devseo-10d.pages.dev',
  
  // Performance & Output
  output: 'static', // Pure SSG for maximum performance
  build: {
    format: 'file', // Optimized output structure
  },

  // Image optimization
  image: {
    service: {
      entrypoint: 'astro/assets/services/sharp',
    },
  },

  // Security headers
  vite: {
    ssr: {
      external: ['sharp'], // Prevent sharp from being bundled
    },
  },

  integrations: [
    sitemap({
      // Sitemap configuration for crawler optimization
      changefreq: 'weekly',
      priority: 0.8,
      lastmod: new Date(),
      // International sitemaps
      i18n: {
        defaultLocale: 'en',
        locales: {
          en: 'en-US',
          ar: 'ar-EG',
        },
      },
    }),
  ],
});
