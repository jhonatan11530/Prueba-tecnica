<?php
namespace App\Services\Contracts;
interface WeatherProviderInterface {
    public function getCurrentTemperature(string $cityName, string $countryCode): ?float;
}