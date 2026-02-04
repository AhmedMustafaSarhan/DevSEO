import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';
import mdx from '@astrojs/mdx';

export default defineConfig({
  site: 'https://devseo.dev',
  
  // Performance & Output
  output: 'static', // Pure SSG for maximum performance
  build: {
    format: 'directory', // Optimized output structure
    assets: '_astro',
    inlineStylesheets: 'auto',
  },

  // Image optimization
  image: {
    service: {
      entrypoint: 'astro/assets/services/sharp',
    },
    domains: ['api.devseo.dev'],
  },

  // i18n routing
  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'ar'],
    routing: {
      prefixDefaultLocale: false,
      redirectToDefaultLocale: true,
    },
  },

  // Security headers & Performance
  vite: {
    ssr: {
      external: ['sharp'],
    },
    build: {
      minify: 'terser',
      target: 'esnext',
    },
  },

  integrations: [
    tailwind({
      applyBaseStyles: true,
      nesting: true,
    }),
    mdx(),
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
