<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Force HTTPS scheme for generated URLs in production. Behind Nginx + Certbot,
        // requests arrive as HTTP from the proxy unless TrustProxies is configured;
        // forcing the scheme guarantees absolute URLs (password reset emails, OG tags)
        // never leak http://.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Rate limit login attempts: 5 per minute, keyed by email + IP.
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = str($request->input('email', ''))->lower() . '|' . $request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
