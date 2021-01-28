<?php

namespace Alfrasc\MatomoTracker;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class LaravelMatomoTrackerServiceProvider extends ServiceProvider
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $this->request = $request;

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/matomotracker.php', 'matomotracker');

        // Register the service the package provides.
        $this->app->singleton('laravelmatomotracker', function ($app) {
            return new LaravelMatomoTracker(
                $this->request,
                Config::get('matomotracker.idSite'),
                Config::get('matomotracker.url'),
                Config::get('matomotracker.tokenAuth')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravelmatomotracker'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/matomotracker.php' => config_path('matomotracker.php'),
        ], 'matomotracker.config');
    }
}
