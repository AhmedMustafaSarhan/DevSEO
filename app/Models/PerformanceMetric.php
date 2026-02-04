<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMetric extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'blog_post_id',
        'lcp',
        'fid',
        'cls',
        'page_load_time',
        'time_to_first_byte',
        'region',
        'device_type',
        'browser',
        'measured_at',
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'lcp' => 'decimal:2',
        'fid' => 'decimal:2',
        'cls' => 'decimal:3',
    ];

    // Relationships
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    // Scopes
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('measured_at', '>=', now()->subDays($days));
    }

    // Methods
    public function getAverageLCP(): ?float
    {
        return $this->query()
            ->wherNotNull('lcp')
            ->avg('lcp');
    }

    public function getAverageCLS(): ?float
    {
        return $this->query()
            ->whereNotNull('cls')
            ->avg('cls');
    }

    public function meetsWebVitals(): bool
    {
        return ($this->lcp === null || $this->lcp <= 2.5) &&
               ($this->cls === null || $this->cls <= 0.1);
    }
}
