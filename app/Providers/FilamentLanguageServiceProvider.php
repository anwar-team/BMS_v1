<?php

namespace App\Providers;

use App\Livewire\LanguageSwitcher;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FilamentLanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the Livewire component
        Livewire::component('language-switcher', LanguageSwitcher::class);

        // Add language switcher to the topbar
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn (): string => Blade::render('@livewire(\'language-switcher\')')
        );

        // Set HTML attributes based on current locale
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            function (): string {
                $locale = App::getLocale();
                $direction = $locale === 'ar' ? 'rtl' : 'ltr';
                
                return "
                    <script>
                        document.documentElement.setAttribute('lang', '{$locale}');
                        document.documentElement.setAttribute('dir', '{$direction}');
                    </script>
                ";
            }
        );
    }
}
