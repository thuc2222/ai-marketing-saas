<div class="relative">
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button
                color="gray"
                size="sm"
                :outlined="true"
            >
                @php
                    $settings = app(\App\Settings\GeneralSettings::class);
                    $currentLang = collect($settings->available_languages)->firstWhere('code', $currentLanguage);
                    $flagIcon = $currentLang['flag_icon'] ?? 'vn.svg';
                @endphp
                <img src="{{ asset('flags/' . $flagIcon) }}" alt="{{ $currentLanguage }}" class="w-4 h-4 mr-2">
                {{ strtoupper($currentLanguage) }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @php
                $settings = app(\App\Settings\GeneralSettings::class);
            @endphp
            @foreach($settings->available_languages as $lang)
                <x-filament::dropdown.list.item
                    wire:click="setLanguage('{{ $lang['code'] }}')"
                    :active="$currentLanguage === $lang['code']"
                >
                    <div class="flex items-center">
                        <img src="{{ asset('flags/' . $lang['flag_icon']) }}" alt="{{ $lang['code'] }}" class="w-4 h-4 mr-2">
                        {{ $lang['code'] === 'en' ? __('English') : __('Vietnamese') }}
                    </div>
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>