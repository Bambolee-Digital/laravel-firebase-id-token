<?php

namespace BamboleeDigital\LaravelFirebaseIdToken\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use BamboleeDigital\LaravelFirebaseIdToken\Auth\FirebaseGuard;
use BamboleeDigital\LaravelFirebaseIdToken\Services\FirebaseAuthService;

class FirebaseAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bambolee-firebase.php', 'bambolee-firebase');

        $this->app->singleton(FirebaseAuthService::class, function ($app) {
            return new FirebaseAuthService();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/bambolee-firebase.php' => config_path('bambolee-firebase.php'),
        ], 'config');

        Auth::extend('firebase', function ($app, $name, array $config) {
            return new FirebaseGuard(
                Auth::createUserProvider($config['provider'] ?? null),
                $app['request'],
                $app[FirebaseAuthService::class]
            );
        });

        $this->app['router']->aliasMiddleware('auth.configurable', \BamboleeDigital\LaravelFirebaseIdToken\Http\Middleware\EnhancedConfigurableAuthMiddleware::class);
    }
}