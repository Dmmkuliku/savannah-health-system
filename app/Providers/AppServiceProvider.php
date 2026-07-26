<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->app->booted(function () {
            if (! app()->runningInConsole()) {
                $forwarded = request()->headers->get('x-forwarded-host');
                if ($forwarded) {
                    $proto = request()->headers->get('x-forwarded-proto') ?: 'https';
                    URL::forceRootUrl($proto.'://'.$forwarded);
                }
            }
        });
    }
}
