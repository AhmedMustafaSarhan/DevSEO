<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use App\Services\SEOService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate_seo')
                ->label('Recalculate SEO')
                ->icon('heroicon-m-arrow-path')
                ->action(function () {
                    $seoService = app(SEOService::class);
                    $this->record->schema_json = json_encode($seoService->generateBlogSchema($this->record));
                    $this->record->seo_score = $seoService->calculateSEOScore($this->record);
                    $this->record->save();

                    $this->notify('success', 'SEO metadata recalculated successfully!');
                }),

            Actions\Action::make('view')
                ->label('View Post')
                ->icon('heroicon-m-eye')
                ->url(function () {
                    return route('blog.show', $this->record->slug);
                })
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update SEO data before saving
        $post = $this->record;
        $post->fill($data);

        $seoService = app(SEOService::class);
        $data['schema_json'] = json_encode($seoService->generateBlogSchema($post));
        $data['seo_score'] = $seoService->calculateSEOScore($post);

        return $data;
    }
}
