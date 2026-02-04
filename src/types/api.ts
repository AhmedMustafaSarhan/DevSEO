/**
 * API Response Types
 * Matches Laravel backend schema
 */

export interface BlogPost {
  id: string;
  slug: string;
  title: string | Record<string, string>; // translatable
  description: string | Record<string, string>; // translatable
  content: string | Record<string, string>; // translatable
  featured_image_url?: string;
  og_image?: string;
  author_id: string;
  author?: Author;
  region: 'EG' | 'US' | 'GLOBAL';
  views: number;
  published: boolean;
  published_at?: string;
  seo_score: number;
  meta_title?: string | Record<string, string>;
  meta_description?: string | Record<string, string>;
  canonical_url?: string;
  schema_json?: object;
  categories?: Category[];
  tags?: Tag[];
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: string;
  slug: string;
  name: string | Record<string, string>; // translatable
  description?: string | Record<string, string>; // translatable
  parent_id?: string;
  parent?: Category;
  meta_title?: string | Record<string, string>;
  meta_description?: string | Record<string, string>;
  posts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Tag {
  id: string;
  slug: string;
  name: string | Record<string, string>; // translatable
  posts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Author {
  id: string;
  name: string;
  email: string;
  region: 'EG' | 'US' | 'INTL';
  created_at: string;
  updated_at: string;
}

export interface ContactSubmission {
  id: string;
  name: string;
  email: string;
  phone?: string;
  subject: string;
  message: string;
  region: string;
  status: 'new' | 'in_progress' | 'resolved' | 'spam';
  created_at: string;
}

export interface SEOData {
  title: string;
  description: string;
  image?: string;
  canonical?: string;
  schema?: object;
  og_image?: string;
  article_published?: string;
  article_modified?: string;
  article_author?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
