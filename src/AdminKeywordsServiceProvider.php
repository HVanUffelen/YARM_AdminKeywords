<?php

namespace Yarm\Adminkeywords;

use Illuminate\Support\ServiceProvider;

class AdminKeywordsServiceProvider extends ServiceProvider{

    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views','adminkeywords');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/config/AdminKeywords.php','adminkeywords');
        $this->publishes([
            //__DIR__ . '/config/bookshelf.php' => config_path('bookshelf.php'),
            //__DIR__ . '/views' => resource_path('views/vendor/adminkeywords'),
            // Assets
            __DIR__ . '/js' => resource_path('js/vendor'),
        ],'adminkeywords');


        //after every update
        //run   php artisan vendor:publish --provider="Yarm\AdminKeywords\AdminKeywordsServiceProvider" --tag="adminkeywords" --force
    }

    public function register()
    {

    }

}
