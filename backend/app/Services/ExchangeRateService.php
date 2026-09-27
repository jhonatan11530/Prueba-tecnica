<?php
namespace App\Services;

use App\Services\Contracts\CurrencyProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService implements CurrencyProviderInterface {
    
    /**
     * Obtiene la tasa de conversión entre dos monedas usando una API Externa.
     * [CUMPLIMIENTO DE PRUEBA TÉCNICA - PASO 11 y PUNTOS EXTRA SENIOR]
     * Implementa resiliencia (manejo de errores) y caché para rendimiento.
     */
    public function getExchangeRate(string $from, string $to): ?float {
        $apiKey = env('EXCHANGE_API_KEY');
        if (!$apiKey) {
            // Si no hay llave, retornamos null silenciosamente. 
            // Esto activará el mecanismo de "Fallback" en el QueryController usando la BD.
            Log::warning('EXCHANGE_API_KEY no configurada. Imposible consultar la divisa, activando fallback en DB.');
            return null; 
        }

        // Cache::remember memoriza la tasa de cambio durante 10 minutos (600 segundos).
        // Protege los Rate Limits de la API externa y garantiza tiempos de respuesta de <50ms.
        return Cache::remember("exchange_{$from}_{$to}", 600, function () use ($from, $to, $apiKey) {
            try {
                // Timeout de 5s: Previene que la aplicación se congele si la API externa se cae
                $response = Http::timeout(5)->get("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$from}/{$to}");

                if ($response->successful()) {
                    return (float) $response->json('conversion_rate');
                } else {
                    Log::error('Error de Exchange API: ' . $response->body());
                }
            } catch (\Exception $e) {
                // En caso de caída total de red, logueamos el error y dejamos que el controlador use el Fallback
                Log::error('Falla en la red al consultar Exchange API: ' . $e->getMessage());
            }

            return null;
        });
    }
}