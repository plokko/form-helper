<?php

namespace Plokko\FormHelper;

use Illuminate\Support\ServiceProvider;

class FormHelperServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadViewsFrom(__DIR__.'/../resources/views','form-helper');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/form-helper'),
        ],'views');
        $this->publishes([
            __DIR__.'/../resources/js/components' => resource_path('js/components/vendor/form-helper'),
        ],'components');

        //-- Publish config file --//
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('form-helper.php'),
        ],'config');
dd('wtf');
        /*
        ///--- Console commands ---///
        if ($this->app->runningInConsole())
        {
            $this->commands([
                GenerateCommand::class,
            ]);
        }
        */
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
            __DIR__.'/../config/config.php', 'form-helper'
        );

        /*
        // Facade accessor
        $this->app->bind(LocaleManager::class, function($app) {
            return new LocaleManager();
        });

        ///Blade directive
        Blade::directive('locales', function ($locale=null) {
            $lm = \App::make(LocaleManager::class);
            $urls = $lm->listLocaleUrls();
            return '<script src="<?php echo optional('.(var_export($urls,true)).')['.($locale?var_export($locale,true):'App::getLocale()').']; ?>" ></script>';
        });
        */

    }

    public function provides()
    {
        return [
            FormHelper::class,
        ];
    }
}
