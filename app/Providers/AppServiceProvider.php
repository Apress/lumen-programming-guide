<?php

namespace App\Providers;

use DB;
use Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        if (env('DB_LOGGING', false) === true) {
            DB::listen(function($query) {
                Log::info($query->sql, $query->bindings, $query->time);
            });
        }
    }
}
