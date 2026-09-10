<?php

namespace App\Providers;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with(config('app.url', ''), 'https') || request()->header('X-Forwarded-Proto') === 'https' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        Blade::component('currency', \App\View\Components\CurrencyFormat::class);

        View::composer('*', function ($view) {
            $view->with('currentCurrency', CurrencyService::getCurrentCurrency());
            $view->with('currencySymbol', CurrencyService::getCurrencyInfo()[CurrencyService::getCurrentCurrency()]['symbol'] ?? '$');
        });
    }
}
