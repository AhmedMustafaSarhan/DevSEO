<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories pivot
        Schema::create('blog_post_category', function (Blueprint $table) {
            $table->foreignUuid('blog_post_id')
                ->constrained('blog_posts')
                ->onDelete('cascade');
            $table->foreignUuid('category_id')
                ->constrained('categories')
                ->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            
            $table->primary(['blog_post_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_category');
    }
};
