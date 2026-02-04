<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasFactory, HasUuids, Sluggable, HasTranslations, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_image',
        'schema_json',
        'region',
        'featured_image_url',
        'reading_time_minutes',
        'seo_score',
        'published_at',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'title' => 'json',
        'description' => 'json',
        'content' => 'json',
        'excerpt' => 'json',
        'meta_title' => 'json',
        'meta_description' => 'json',
        'schema_json' => 'json',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'view_count' => 'integer',
        'seo_score' => 'integer',
    ];

    protected array $translatable = [
        'title',
        'description',
        'content',
        'excerpt',
        'meta_title',
        'meta_description',
    ];

    // Sluggable Configuration
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'blog_post_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag');
    }

    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(PerformanceMetric::class);
    }

    // Query Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region)
            ->orWhere('region', 'GLOBAL');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('published_at');
    }

    // Accessors
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }
}
