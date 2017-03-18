<?php

namespace clentfort\LaravelFindJsLocalizations;

use Illuminate\Support\ServiceProvider;

class ArtisanServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $configSourcePath = PathHelper::join(__DIR__, 'config.php');
        $configTargetPath = config_path('laravel-find-js-localizations.php');
        $this->publishes([$configSourcePath => $configTargetPath]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
		$this->mergeConfigFrom(
            PathHelper::join(__DIR__, 'config.php'),
            'laravel-find-js-localizations'
		);

        $this->app->singleton(
            'find-js-localizations.command.find-missing',
            function ($app) {
                return new FindMissing(
                    $app['config']['laravel-find-js-localizations']
                );
            }
        );

        $this->commands('find-js-localizations.command.find-missing');
    }
}
