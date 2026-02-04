/**
 * i18n Routing Helper
 * Manages locale detection and routing
 */

import { DEFAULT_LOCALE, LOCALES, type Locale } from '@/consts';

/**
 * Get locale from URL path
 */
export function getLocaleFromPath(pathname: string): Locale {
  const segments = pathname.split('/').filter(Boolean);
  const firstSegment = segments[0];

  if (firstSegment === 'ar') {
    return 'ar';
  }

  return DEFAULT_LOCALE;
}

/**
 * Build localized path
 */
export function buildLocalizedPath(
  path: string,
  locale: Locale = DEFAULT_LOCALE
): string {
  // Remove leading slash
  const cleanPath = path.startsWith('/') ? path.slice(1) : path;

  // Don't prefix default locale
  if (locale === DEFAULT_LOCALE) {
    return `/${cleanPath}`;
  }

  return `/${locale}/${cleanPath}`;
}

/**
 * Switch locale for current path
 */
export function switchLocale(pathname: string, targetLocale: Locale): string {
  const currentLocale = getLocaleFromPath(pathname);

  if (currentLocale === DEFAULT_LOCALE) {
    // Remove default locale prefix (if any)
    const path = pathname.startsWith('/en/')
      ? pathname.slice(3)
      : pathname;
    return buildLocalizedPath(path, targetLocale);
  }

  // Replace locale prefix
  const pathWithoutLocale = pathname.slice(3); // Remove /ar or /en
  return buildLocalizedPath(pathWithoutLocale, targetLocale);
}

/**
 * Get all locale variants of a path
 */
export function getLocalizedPathVariants(
  path: string
): Record<Locale, string> {
  const basePathWithoutLocale = path
    .replace(/^\/en\//, '/')
    .replace(/^\/ar\//, '/');

  return {
    en: buildLocalizedPath(basePathWithoutLocale, 'en'),
    ar: buildLocalizedPath(basePathWithoutLocale, 'ar'),
  };
}

/**
 * Get hreflang alternates for SEO
 */
export function getHreflangAlternates(
  url: URL
): Array<{ lang: string; href: string }> {
  const variants = getLocalizedPathVariants(url.pathname);

  return Object.entries(variants).map(([locale, path]) => ({
    lang: locale === 'ar' ? 'ar-EG' : 'en-US',
    href: `${url.origin}${path}`,
  }));
}

/**
 * Get translation object for a locale
 */
export function getTranslationConfig(locale: Locale) {
  return LOCALES[locale];
}

/**
 * Check if locale is RTL
 */
export function isRTLLocale(locale: Locale): boolean {
  return locale === 'ar';
}

/**
 * Get HTML lang attribute
 */
export function getLangAttribute(locale: Locale): string {
  return locale === 'ar' ? 'ar-EG' : 'en-US';
}
