/**
 * API Utility Helpers
 * Reusable functions for common API operations
 */

import ApiClient from '@api/client';
import type { BlogPost, Category, Tag } from '@types/api';

/**
 * Get all blog posts for static generation
 */
export async function getAllBlogPosts(): Promise<BlogPost[]> {
  try {
    const response = await ApiClient.getBlogPosts(1, 100);
    return response.data;
  } catch (error) {
    console.error('Failed to fetch blog posts:', error);
    return [];
  }
}

/**
 * Get all categories for navigation
 */
export async function getAllCategories(): Promise<Category[]> {
  try {
    return await ApiClient.getCategories();
  } catch (error) {
    console.error('Failed to fetch categories:', error);
    return [];
  }
}

/**
 * Get all tags
 */
export async function getAllTags(): Promise<Tag[]> {
  try {
    return await ApiClient.getTags();
  } catch (error) {
    console.error('Failed to fetch tags:', error);
    return [];
  }
}

/**
 * Get featured posts for homepage
 */
export async function getFeaturedPosts(limit: number = 3): Promise<BlogPost[]> {
  try {
    const posts = await getAllBlogPosts();
    return posts
      .filter((post) => post.published)
      .sort(
        (a, b) =>
          new Date(b.published_at || 0).getTime() -
          new Date(a.published_at || 0).getTime()
      )
      .slice(0, limit);
  } catch (error) {
    console.error('Failed to fetch featured posts:', error);
    return [];
  }
}

/**
 * Get popular posts by views
 */
export async function getPopularPosts(limit: number = 5): Promise<BlogPost[]> {
  try {
    const posts = await getAllBlogPosts();
    return posts
      .filter((post) => post.published)
      .sort((a, b) => (b.views || 0) - (a.views || 0))
      .slice(0, limit);
  } catch (error) {
    console.error('Failed to fetch popular posts:', error);
    return [];
  }
}

/**
 * Get related posts by tags
 */
export async function getRelatedPosts(
  tags: Tag[],
  excludeSlug: string,
  limit: number = 3
): Promise<BlogPost[]> {
  try {
    const posts = await getAllBlogPosts();
    const tagSlugs = new Set(tags.map((tag) => tag.slug));

    return posts
      .filter(
        (post) =>
          post.slug !== excludeSlug &&
          post.published &&
          post.tags?.some((tag) => tagSlugs.has(tag.slug))
      )
      .slice(0, limit);
  } catch (error) {
    console.error('Failed to fetch related posts:', error);
    return [];
  }
}

/**
 * Get posts by category
 */
export async function getPostsByCategory(
  categorySlug: string
): Promise<BlogPost[]> {
  try {
    const response = await ApiClient.getBlogPostsByCategorySlug(categorySlug);
    return response.data;
  } catch (error) {
    console.error(`Failed to fetch posts for category ${categorySlug}:`, error);
    return [];
  }
}

/**
 * Get recent posts
 */
export async function getRecentPosts(limit: number = 5): Promise<BlogPost[]> {
  try {
    return await ApiClient.getRecentBlogPosts(limit);
  } catch (error) {
    console.error('Failed to fetch recent posts:', error);
    return [];
  }
}

/**
 * Search posts
 */
export async function searchPosts(query: string): Promise<BlogPost[]> {
  try {
    const response = await ApiClient.searchBlogPosts(query);
    return response.data;
  } catch (error) {
    console.error(`Failed to search posts for query "${query}":`, error);
    return [];
  }
}

/**
 * Generate dynamic routes for static generation
 */
export async function generateBlogPostRoutes(): Promise<
  Array<{ params: { slug: string }; props: { post: BlogPost } }>
> {
  try {
    const posts = await getAllBlogPosts();
    return posts
      .filter((post) => post.published)
      .map((post) => ({
        params: { slug: post.slug },
        props: { post },
      }));
  } catch (error) {
    console.error('Failed to generate blog post routes:', error);
    return [];
  }
}

/**
 * Generate category routes
 */
export async function generateCategoryRoutes(): Promise<
  Array<{ params: { slug: string }; props: { category: Category } }>
> {
  try {
    const categories = await getAllCategories();
    return categories.map((category) => ({
      params: { slug: category.slug },
      props: { category },
    }));
  } catch (error) {
    console.error('Failed to generate category routes:', error);
    return [];
  }
}

/**
 * Check API health
 */
export async function checkApiHealth(): Promise<boolean> {
  try {
    await ApiClient.healthCheck();
    return true;
  } catch {
    return false;
  }
}
