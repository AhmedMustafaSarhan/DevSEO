<?php

namespace App\Providers;

use App\Repositories\Contracts\BlogPostRepositoryInterface;
use App\Repositories\Eloquent\BlogPostRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings for dependency injection
        $this->app->bind(
            BlogPostRepositoryInterface::class,
            BlogPostRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
