@php
    $settings = app(\App\Settings\GeneralSettings::class);
    $currencies = $settings->supported_currencies ?? [];
@endphp

<div class="flex items-center">
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <button class="flex items-center gap-x-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition">
                <span>{{ session('currency', 'VND') }}</span>
                <x-heroicon-m-chevron-down class="w-4 h-4 text-gray-400" />
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach($currencies as $currency)
                <x-filament::dropdown.list.item 
                    wire:click="setCurrency('{{ $currency['code'] }}')" 
                    icon="heroicon-o-banknotes"
                >
                    {{ $currency['code'] }} ({{ $currency['symbol'] }})
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>