<?php

namespace App\Providers;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Blade;
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
        Blade::component('currency', \App\View\Components\CurrencyFormat::class);

        View::composer('*', function ($view) {
            $view->with('currentCurrency', CurrencyService::getCurrentCurrency());
            $view->with('currencySymbol', CurrencyService::getCurrencyInfo()[CurrencyService::getCurrentCurrency()]['symbol'] ?? '$');
        });
    }
}
