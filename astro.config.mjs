// astro.config.mjs
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://devseo-10d.pages.dev', // غير السطر ده بالظبط
  integrations: [sitemap()],
});
