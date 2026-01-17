<?php

namespace App\Livewire;

use Livewire\Component;

class CurrencySwitcher extends Component
{
    public string $currentCurrency = 'VND';

    public function mount()
    {
        // Lấy currency từ session, mặc định là VND
        $this->currentCurrency = session('currency', 'VND');
    }

    public function setCurrency($currency)
    {
    $settings = app(\App\Settings\GeneralSettings::class);
    $allowedCodes = collect($settings->supported_currencies)->pluck('code')->toArray();

    if (in_array($currency, $allowedCodes)) {
        session(['currency' => $currency]);
        $this->currentCurrency = $currency;
        return redirect(request()->header('Referer'));
    }
    }

    public function render()
    {
        return view('livewire.currency-switcher');
    }
}