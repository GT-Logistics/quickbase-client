<?php
/*
 * Copyright (c) 2023 GT Logistics.
 */

namespace Gtlogistics\QuickbaseClient\Bridge\Laravel;

use Gtlogistics\QuickbaseClient\QuickbaseClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class QuickbaseClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/config.php' => config_path('quickbase.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'quickbase');

        $this->app->singleton(QuickbaseClient::class, function (Application $app) {
            return new QuickbaseClient(
                $app->get(ClientInterface::class),
                $app->get(RequestFactoryInterface::class),
                $app->get(UriFactoryInterface::class),
                $app->get(StreamFactoryInterface::class),
                config('quickbase.token'),
                config('quickbase.realm'),
                config('quickbase.base_uri'),
            );
        });
    }
}
