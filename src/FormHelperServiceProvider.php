<?php

namespace Plokko\FormHelper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Plokko\FormHelper\Components\FormHelperComponent;

class FormHelperServiceProvider extends ServiceProvider
{
    const PACKAGE_NAME = 'form-helper';
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
            __DIR__.'/../resources/components' => resource_path('js/components/vendor/plokko/'.self::PACKAGE_NAME),
        ],'components');

        //-- Publish config file --//
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path(self::PACKAGE_NAME.'.php'),
        ],'config');

        //-- Publish and loads translations --//
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::PACKAGE_NAME);
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/'.self::PACKAGE_NAME),
        ],'translations');

        //-- <x-form-helper> component --//
        Blade::component(config('form-helper.form-component','form-helper'), FormHelperComponent::class);
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
