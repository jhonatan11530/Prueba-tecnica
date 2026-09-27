<?php
namespace App\Services;

use App\Services\Contracts\WeatherProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenWeatherMapService implements WeatherProviderInterface {
    
    /**
     * Consulta el clima actual llamando a la API de OpenWeatherMap.
     * Al igual que el servicio de monedas, implementa protección con Caché y Timeouts.
     */
    public function getCurrentTemperature(string $cityName, string $countryCode): ?float {
        $apiKey = env('WEATHER_API_KEY');
        if (!$apiKey) {
            Log::warning('WEATHER_API_KEY no configurada. Imposible consultar el clima.');
            return null;
        }

        // Memorizamos la temperatura en caché por 10 minutos para ahorrar cuota de API
        return Cache::remember("weather_{$cityName}_{$countryCode}", 600, function () use ($cityName, $countryCode, $apiKey) {
            try {
                $response = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => "{$cityName},{$countryCode}",
                    'appid' => $apiKey,
                    'units' => 'metric' // Aseguramos que la temperatura retorne en Celsius
                ]);

                if ($response->successful()) {
                    return (float) $response->json('main.temp');
                } else {
                    Log::error('Error de OpenWeatherMap: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Falla en la red al consultar Weather API: ' . $e->getMessage());
            }

            return null;
        });
    }
}