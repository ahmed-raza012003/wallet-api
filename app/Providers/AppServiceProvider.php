<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('files', function () {
            return new Filesystem();
        });
    }

    public function boot()
    {
        //
    }
}