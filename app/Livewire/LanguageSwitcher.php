<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageSwitcher extends Component
{
    public $currentLanguage;
    public $availableLanguages = [
        'ar' => [
            'name' => 'العربية',
            'flag' => '🇸🇦',
            'dir' => 'rtl'
        ],
        'en' => [
            'name' => 'English',
            'flag' => '🇺🇸',
            'dir' => 'ltr'
        ]
    ];

    public function mount()
    {
        $this->currentLanguage = Session::get('locale', config('app.locale'));
    }

    public function switchLanguage($language)
    {
        if (array_key_exists($language, $this->availableLanguages)) {
            Session::put('locale', $language);
            $this->currentLanguage = $language;
            
            // Redirect to refresh the page with new language
            return redirect()->to(request()->fullUrl());
        }
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
