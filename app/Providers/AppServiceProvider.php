<?php

namespace App\Providers;

use App\Services\ClaudeReviewService;
use App\Services\GitHubService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GitHubService::class);
        $this->app->singleton(ClaudeReviewService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render はプロキシ裏で SSL 終端するため、本番では route()/url() を
        // 明示的に https 生成にする（http:// 生成→httpsページからの fetch が
        // mixed content でブロックされ Analyze が "Failed to fetch" になるのを根治）。
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
