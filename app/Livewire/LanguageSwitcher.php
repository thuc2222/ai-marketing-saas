<?php

namespace App\Livewire;

use Livewire\Component;
use App\Settings\GeneralSettings;

class LanguageSwitcher extends Component
{
    public string $currentLanguage = 'vi';

    public function mount()
    {
        // Lấy language từ session, mặc định từ settings
        $settings = app(GeneralSettings::class);
        $this->currentLanguage = session('locale', $settings->default_language ?? 'vi');
    }

    public function setLanguage($language)
    {
        $settings = app(GeneralSettings::class);
        $allowedCodes = collect($settings->available_languages)->pluck('code')->toArray();

        if (in_array($language, $allowedCodes)) {
            session(['locale' => $language]);
            $this->currentLanguage = $language;
            return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}