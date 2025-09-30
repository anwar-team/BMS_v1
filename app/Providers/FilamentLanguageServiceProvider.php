<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FilamentLanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Add language switcher to the topbar using a regular Blade component
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_END,
            fn (): string => Blade::render('<x-language-switcher />')
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
