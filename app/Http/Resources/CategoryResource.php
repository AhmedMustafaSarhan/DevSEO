<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->getTranslation('name', $locale),
            'description' => $this->getTranslation('description', $locale),
            'seo' => [
                'meta_title' => $this->getTranslation('meta_title', $locale),
                'meta_description' => $this->getTranslation('meta_description', $locale),
                'schema_json' => json_decode($this->schema_json, true),
            ],
            'parent_id' => $this->parent_id,
            'post_count' => $this->blogPosts()->count(),
        ];
    }
}
