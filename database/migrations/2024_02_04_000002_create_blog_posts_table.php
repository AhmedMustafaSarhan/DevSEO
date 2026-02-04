<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('slug')->unique();
            
            // Translatable content
            $table->json('title'); // { "en": "...", "ar": "..." }
            $table->json('description');
            $table->json('content');
            $table->json('excerpt');
            
            // SEO Fields
            $table->json('meta_title');
            $table->json('meta_description');
            $table->string('canonical_url');
            $table->string('og_image')->nullable();
            $table->json('schema_json'); // Schema.org BlogPosting
            
            // Content Management
            $table->enum('region', ['EG', 'US', 'GLOBAL'])->default('GLOBAL');
            $table->string('featured_image_url')->nullable();
            $table->integer('reading_time_minutes')->default(5);
            $table->integer('seo_score')->default(0);
            
            // Publishing
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            
            // Tracking
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('slug');
            $table->index('published_at');
            $table->index('status');
            $table->index('region');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
