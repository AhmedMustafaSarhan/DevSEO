<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $request->query('locale', 'en');
        $this->resource->setLocale($locale);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTranslation('title', $locale),
            'description' => $this->getTranslation('description', $locale),
            'content' => $this->getTranslation('content', $locale),
            'featured_image_url' => $this->featured_image_url,
            'og_image' => $this->og_image,
            'author' => new AuthorResource($this->author),
            'categories' => CategoryResource::collection($this->categories),
            'tags' => TagResource::collection($this->tags),
            'seo' => [
                'meta_title' => $this->getTranslation('meta_title', $locale),
                'meta_description' => $this->getTranslation('meta_description', $locale),
                'canonical_url' => $this->canonical_url,
                'schema_json' => json_decode($this->schema_json, true),
                'seo_score' => $this->seo_score,
            ],
            'metrics' => [
                'view_count' => $this->view_count,
                'reading_time_minutes' => $this->reading_time_minutes,
            ],
            'status' => [
                'is_published' => $this->is_published,
                'published_at' => $this->published_at?->toIso8601String(),
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'regions' => $this->regions,
        ];
    }
}
