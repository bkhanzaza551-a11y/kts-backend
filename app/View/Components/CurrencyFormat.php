<?php

namespace App\View\Components;

use App\Services\CurrencyService;
use Illuminate\View\Component;

class CurrencyFormat extends Component
{
    public float $amount;
    public string $formatted;
    public string $currency;

    public function __construct(float $amount, ?string $from = null)
    {
        $this->amount = $amount;
        $this->currency = CurrencyService::getCurrentCurrency();
        $this->formatted = CurrencyService::formatAmount($amount, $from ?? 'USD');
    }

    public function render()
    {
        return '<span class="currency-value" data-amount="{{ $amount }}">{{ $formatted }}</span>';
    }
}
