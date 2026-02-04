/**
 * API Client for Laravel Backend
 * Handles all communication with the backend API
 */

import type {
  BlogPost,
  Category,
  Tag,
  PaginatedResponse,
  ContactSubmission,
} from '@types/api';

const API_BASE_URL = import.meta.env.PUBLIC_API_URL || 'http://localhost:8000/api';

export class ApiClient {
  private static baseUrl = API_BASE_URL;

  static async fetch<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    try {
      const url = `${this.baseUrl}${endpoint}`;
      const response = await fetch(url, {
        headers: {
          'Content-Type': 'application/json',
          ...options.headers,
        },
        ...options,
      });

      if (!response.ok) {
        throw new Error(`API Error: ${response.status} ${response.statusText}`);
      }

      return response.json();
    } catch (error) {
      console.error(`API request failed: ${endpoint}`, error);
      throw error;
    }
  }

  /**
   * Blog Posts
   */

  static async getBlogPosts(
    page: number = 1,
    limit: number = 10,
    region?: string
  ): Promise<PaginatedResponse<BlogPost>> {
    const query = new URLSearchParams({
      page: page.toString(),
      per_page: limit.toString(),
      ...(region && { region }),
    });
    return this.fetch<PaginatedResponse<BlogPost>>(`/blog?${query}`);
  }

  static async getBlogPostBySlug(slug: string): Promise<BlogPost> {
    return this.fetch<BlogPost>(`/blog/${slug}`);
  }

  static async getBlogPostsByCategorySlug(
    categorySlug: string,
    page: number = 1
  ): Promise<PaginatedResponse<BlogPost>> {
    const query = new URLSearchParams({
      page: page.toString(),
    });
    return this.fetch<PaginatedResponse<BlogPost>>(
      `/blog/category/${categorySlug}?${query}`
    );
  }

  static async searchBlogPosts(
    q: string,
    page: number = 1
  ): Promise<PaginatedResponse<BlogPost>> {
    const query = new URLSearchParams({
      q,
      page: page.toString(),
    });
    return this.fetch<PaginatedResponse<BlogPost>>(`/blog/search?${query}`);
  }

  static async getRecentBlogPosts(limit: number = 5): Promise<BlogPost[]> {
    const query = new URLSearchParams({
      limit: limit.toString(),
    });
    return this.fetch<BlogPost[]>(`/blog/recent?${query}`);
  }

  static async getBlogPostSeoData(slug: string): Promise<object> {
    return this.fetch<object>(`/blog/${slug}/seo`);
  }

  /**
   * Categories
   */

  static async getCategories(): Promise<Category[]> {
    return this.fetch<Category[]>('/categories');
  }

  static async getCategoryBySlug(slug: string): Promise<Category> {
    return this.fetch<Category>(`/categories/${slug}`);
  }

  /**
   * Tags
   */

  static async getTags(): Promise<Tag[]> {
    return this.fetch<Tag[]>('/tags');
  }

  static async getTagBySlug(slug: string): Promise<Tag> {
    return this.fetch<Tag>(`/tags/${slug}`);
  }

  /**
   * Contact Submissions
   */

  static async submitContactForm(data: {
    name: string;
    email: string;
    subject: string;
    message: string;
    phone?: string;
    region: string;
  }): Promise<ContactSubmission> {
    return this.fetch<ContactSubmission>('/contact', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  /**
   * Health Check
   */

  static async healthCheck(): Promise<{ status: string; timestamp: string }> {
    return this.fetch<{ status: string; timestamp: string }>('/health');
  }
}

export default ApiClient;
