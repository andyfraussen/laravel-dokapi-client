<?php

namespace AndyFraussen\Dokapi;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class DokapiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dokapi.php' => config_path('dokapi.php'),
            ], 'dokapi-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dokapi.php', 'dokapi');

        $this->app->singleton(DokapiClient::class, function ($app) {
            $cache = $app->bound(CacheRepository::class) ? $app->make(CacheRepository::class) : null;
            return new DokapiClient($app['config']['dokapi'] ?? [], null, $cache);
        });
    }
}
