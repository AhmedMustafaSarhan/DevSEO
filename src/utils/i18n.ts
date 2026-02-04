/**
 * Internationalization Utilities
 * Handle translatable fields from Laravel API
 */

export type Locale = 'en' | 'ar';

/**
 * Get translated value from either string or translatable object
 */
export function getTranslation(
  value: string | Record<string, string> | undefined,
  locale: Locale = 'en'
): string {
  if (!value) return '';

  // If it's already a string, return it
  if (typeof value === 'string') {
    return value;
  }

  // If it's an object, get the locale-specific value
  if (typeof value === 'object') {
    return value[locale] || value['en'] || '';
  }

  return '';
}

/**
 * Format date for display
 */
export function formatDate(date: string | Date, locale: Locale = 'en'): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;

  return new Intl.DateTimeFormat(locale === 'ar' ? 'ar-EG' : 'en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(dateObj);
}

/**
 * Format reading time
 */
export function formatReadingTime(
  content: string,
  locale: Locale = 'en'
): string {
  const wordsPerMinute = 200;
  const words = content.trim().split(/\s+/).length;
  const minutes = Math.ceil(words / wordsPerMinute);

  if (locale === 'ar') {
    return `${minutes} دقيقة قراءة`;
  }

  return `${minutes} min read`;
}

/**
 * Format SEO score with color
 */
export function formatSeoScore(score: number): {
  score: number;
  color: string;
  label: string;
} {
  let color = 'text-red-600';
  let label = 'Poor';

  if (score >= 80) {
    color = 'text-green-600';
    label = 'Excellent';
  } else if (score >= 60) {
    color = 'text-yellow-600';
    label = 'Good';
  }

  return { score, color, label };
}

/**
 * Truncate text with ellipsis
 */
export function truncateText(text: string, maxLength: number = 150): string {
  if (text.length <= maxLength) {
    return text;
  }
  return text.substring(0, maxLength).trim() + '...';
}

/**
 * Get reading time estimate
 */
export function getReadingTimeEstimate(content: string): number {
  const wordsPerMinute = 200;
  const words = content.trim().split(/\s+/).length;
  return Math.ceil(words / wordsPerMinute);
}

/**
 * Convert slug to title
 */
export function slugToTitle(slug: string): string {
  return slug
    .split('-')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

/**
 * Check if content is RTL language
 */
export function isRTL(locale: Locale): boolean {
  return locale === 'ar';
}

/**
 * Get direction class for Tailwind
 */
export function getDirectionClass(locale: Locale): string {
  return isRTL(locale) ? 'rtl' : 'ltr';
}

/**
 * Build image URL with optimization
 */
export function buildImageUrl(
  url: string | undefined,
  width: number = 1200,
  height: number = 630
): string {
  if (!url) return '/og-default.png';

  // If URL is from Cloudflare or has query params, use as-is
  if (url.includes('cloudflare') || url.includes('?')) {
    return url;
  }

  // Add Cloudflare Image Optimization params
  return `${url}?w=${width}&h=${height}&fit=cover&quality=80`;
}

/**
 * Parse schema data safely
 */
export function parseSchema(schema: string | object | undefined): object | null {
  if (!schema) return null;

  if (typeof schema === 'object') {
    return schema;
  }

  try {
    return JSON.parse(schema);
  } catch {
    console.error('Failed to parse schema:', schema);
    return null;
  }
}
