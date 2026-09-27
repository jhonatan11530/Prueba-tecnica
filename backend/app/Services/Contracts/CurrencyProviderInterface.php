<?php
namespace App\Services\Contracts;
interface CurrencyProviderInterface {
    public function getExchangeRate(string $from, string $to): ?float;
}