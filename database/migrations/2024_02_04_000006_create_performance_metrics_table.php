<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blog_post_id')
                ->nullable()
                ->constrained('blog_posts')
                ->onDelete('cascade');
            
            // Core Web Vitals
            $table->decimal('lcp', 5, 2)->nullable(); // Largest Contentful Paint
            $table->decimal('fid', 5, 2)->nullable(); // First Input Delay
            $table->decimal('cls', 5, 3)->nullable(); // Cumulative Layout Shift
            
            // Additional Metrics
            $table->decimal('page_load_time', 5, 2)->nullable();
            $table->decimal('time_to_first_byte', 5, 2)->nullable();
            
            // Context
            $table->string('region')->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->nullable();
            $table->string('browser')->nullable();
            
            $table->timestamp('measured_at')->useCurrent();
            
            // Indexes
            $table->index('blog_post_id');
            $table->index('region');
            $table->index('measured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
