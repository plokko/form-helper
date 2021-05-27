<?php

namespace Plokko\FormHelper;

use Illuminate\Support\ServiceProvider;

class FormHelperServiceProvider extends ServiceProvider
{
    const PACKAGE_NAME='form-helper';
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views',self::PACKAGE_NAME);
        //-- Publish Views --//
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/'.self::PACKAGE_NAME),
        ],'views');

        //-- Publish Vue components --//
        $this->publishes([
            __DIR__.'/../resources/js/components' => resource_path('js/components/vendor/'.self::PACKAGE_NAME),
        ],'components');

        //-- Publish config file --//
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path(self::PACKAGE_NAME.'.php'),
        ],'config');

        //-- Publish and loads translations --//
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::PACKAGE_NAME);
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/'.self::PACKAGE_NAME),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /// Merge default config ///
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', self::PACKAGE_NAME
        );
    }

    public function provides()
    {
        return [
            FormHelper::class,
        ];
    }
}
