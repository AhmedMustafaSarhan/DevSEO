// src/consts.ts

export const SITE_TITLE = 'DevSEO | Technical SEO for Developers';
export const SITE_DESCRIPTION = 'Expert Laravel developer specializing in Technical SEO, Core Web Vitals, and high-performance web development to boost your search engine rankings.';

export const DEFAULT_LOCALE = 'en' as const;
export const LOCALES = {
  en: {
    code: 'en',
    name: 'English',
    flag: '🇺🇸',
  },
  ar: {
    code: 'ar',
    name: 'العربية',
    flag: '🇪🇬',
  },
} as const;

export type Locale = keyof typeof LOCALES;

export const API_BASE_URL =
  import.meta.env.PUBLIC_API_URL || 'http://localhost:8000/api';

export const SITE_METADATA = {
  title: SITE_TITLE,
  description: SITE_DESCRIPTION,
  author: 'Ahmed Mustafa Sarhan',
  email: 'contact@devseo.dev',
  social: {
    twitter: 'https://twitter.com/devseodotdev',
    github: 'https://github.com/AhmedMustafaSarhan',
    linkedin: 'https://linkedin.com/in/ahmadmustafasarhan',
  },
} as const;

export const PAGINATION = {
  postsPerPage: 10,
  categoriesPerPage: 20,
  tagsPerPage: 50,
} as const;
