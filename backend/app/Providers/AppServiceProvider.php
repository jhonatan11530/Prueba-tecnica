<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\WeatherProviderInterface;
use App\Services\Contracts\CurrencyProviderInterface;
use App\Services\OpenWeatherMapService;
use App\Services\ExchangeRateService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WeatherProviderInterface::class, OpenWeatherMapService::class);
        $this->app->bind(CurrencyProviderInterface::class, ExchangeRateService::class);
    }

    public function boot(): void
    {
        //
    }
}
