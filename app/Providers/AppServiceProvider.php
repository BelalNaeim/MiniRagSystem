<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\Auth\AuthServiceInterface::class,
            \App\Services\Auth\AuthService::class
        );
        $this->app->bind(
            \App\Contracts\File\FileHandlerInterface::class,
            \App\Services\File\PdfService::class
        );
        $this->app->bind(
            \App\Contracts\Vector\VectorStoreInterface::class,
            \App\Services\Vector\QdrantService::class
        );
        $this->app->bind(
            \App\Contracts\AI\EmbeddingProviderInterface::class,
            \App\Services\AI\OllamaEmbeddingService::class
        );
        $this->app->bind(
            \App\Services\AI\OpenAiChatService::class,
            \App\Services\AI\OpenAiChatService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
