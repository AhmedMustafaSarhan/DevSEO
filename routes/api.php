<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\ContactController;

Route::prefix('api')
    ->middleware(['throttle:60,1'])
    ->group(function () {
        
        // Blog Posts
        Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');
        Route::get('/blog/{slug}', [BlogPostController::class, 'show'])->name('blog.show');
        Route::get('/blog/category/{categorySlug}', [BlogPostController::class, 'byCategory'])->name('blog.by-category');
        Route::get('/blog/search', [BlogPostController::class, 'search'])->name('blog.search');
        Route::get('/blog/recent', [BlogPostController::class, 'recent'])->name('blog.recent');
        Route::get('/blog/{slug}/seo', [BlogPostController::class, 'seoData'])->name('blog.seo');

        // Contact
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        // Admin routes (require auth)
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/contact/{id}', [ContactController::class, 'show'])->name('contact.show');
            Route::patch('/contact/{id}/status/{status}', [ContactController::class, 'update'])->name('contact.update');
        });

        // Health check
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ]);
        })->name('health');
    });
