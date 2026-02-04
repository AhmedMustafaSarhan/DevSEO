<?php

namespace App\Services;

use App\Models\BlogPost;

class SEOService
{
    /**
     * Generate schema.org BlogPosting schema for SEO.
     */
    public function generateBlogSchema(BlogPost $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->getTranslation('title', 'en'),
            'description' => $post->getTranslation('meta_description', 'en'),
            'image' => $post->og_image ?? null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => route('api.authors.show', $post->author->id),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $post->canonical_url,
            ],
            'wordCount' => strlen($post->getTranslation('content', 'en')),
            'articleSection' => $post->categories->pluck('name')->first() ?? 'Technology',
            'keywords' => $post->tags->pluck('name')->pluck('en')->implode(', '),
        ];
    }

    /**
     * Calculate SEO score (0-100).
     */
    public function calculateSEOScore(BlogPost $post): int
    {
        $score = 0;

        // Title optimization (10 points)
        $titleLen = strlen($post->getTranslation('meta_title', 'en'));
        if ($titleLen >= 30 && $titleLen <= 60) {
            $score += 10;
        } elseif ($titleLen > 0) {
            $score += 5; // Partial credit
        }

        // Meta description (10 points)
        $descLen = strlen($post->getTranslation('meta_description', 'en'));
        if ($descLen >= 120 && $descLen <= 160) {
            $score += 10;
        } elseif ($descLen > 0) {
            $score += 5;
        }

        // Content length (10 points)
        $contentLen = strlen($post->getTranslation('content', 'en'));
        if ($contentLen >= 1000) {
            $score += 10;
        } elseif ($contentLen >= 500) {
            $score += 5;
        }

        // Image optimization (10 points)
        if ($post->og_image && $post->featured_image_url) {
            $score += 10;
        } elseif ($post->og_image) {
            $score += 5;
        }

        // Schema implementation (10 points)
        if ($post->schema_json) {
            $score += 10;
        }

        // Canonical URL (10 points)
        if ($post->canonical_url) {
            $score += 10;
        }

        // Categories and Tags (10 points)
        if ($post->categories->count() > 0 && $post->tags->count() > 0) {
            $score += 10;
        } elseif ($post->categories->count() > 0 || $post->tags->count() > 0) {
            $score += 5;
        }

        // Reading time (10 points)
        if ($post->reading_time_minutes > 0) {
            $score += 10;
        }

        // Multilingual content (10 points)
        $enContent = $post->getTranslation('content', 'en');
        $arContent = $post->getTranslation('content', 'ar');
        if ($enContent && $arContent && strlen($arContent) > 200) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Suggest SEO improvements.
     */
    public function suggestImprovements(BlogPost $post): array
    {
        $improvements = [];

        // Title check
        $titleLen = strlen($post->getTranslation('meta_title', 'en'));
        if ($titleLen < 30) {
            $improvements[] = 'Meta title is too short. Aim for 30-60 characters.';
        } elseif ($titleLen > 60) {
            $improvements[] = 'Meta title is too long. Keep it under 60 characters.';
        }

        // Meta description check
        $descLen = strlen($post->getTranslation('meta_description', 'en'));
        if ($descLen < 120) {
            $improvements[] = 'Meta description is too short. Aim for 120-160 characters.';
        } elseif ($descLen > 160) {
            $improvements[] = 'Meta description is too long. Keep it under 160 characters.';
        }

        // Content length
        $contentLen = strlen($post->getTranslation('content', 'en'));
        if ($contentLen < 1000) {
            $improvements[] = 'Content is too short. Aim for at least 1,000 words for better SEO.';
        }

        // Images
        if (!$post->og_image) {
            $improvements[] = 'Missing OG image. Add an image for social sharing.';
        }

        // Categories and tags
        if ($post->categories->count() === 0) {
            $improvements[] = 'No categories assigned. Add relevant categories.';
        }

        if ($post->tags->count() === 0) {
            $improvements[] = 'No tags assigned. Add relevant tags.';
        }

        // Multilingual
        $arContent = $post->getTranslation('content', 'ar');
        if (!$arContent || strlen($arContent) < 200) {
            $improvements[] = 'Arabic content is missing or incomplete. Add Arabic translation for better reach.';
        }

        return $improvements;
    }
}
