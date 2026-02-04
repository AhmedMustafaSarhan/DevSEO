<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags table
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('name'); // { "en": "...", "ar": "..." }
            $table->string('slug')->unique();
            $table->string('color')->nullable(); // Hex color
            $table->timestamps();
            
            $table->index('slug');
        });

        // Tags pivot
        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->foreignUuid('blog_post_id')
                ->constrained('blog_posts')
                ->onDelete('cascade');
            $table->foreignUuid('tag_id')
                ->constrained('tags')
                ->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            
            $table->primary(['blog_post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('tags');
    }
};
