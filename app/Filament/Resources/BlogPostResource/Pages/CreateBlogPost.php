<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Services\SEOService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate SEO schema and score on creation
        $post = new BlogPost();
        $post->fill($data);

        $seoService = app(SEOService::class);
        $data['schema_json'] = json_encode($seoService->generateBlogSchema($post));
        $data['seo_score'] = $seoService->calculateSEOScore($post);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
