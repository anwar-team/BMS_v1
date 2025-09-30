<?php

namespace App\Helpers;

class LanguageHelper
{
    /**
     * Get available languages configuration
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'ar' => [
                'name' => 'العربية',
                'native' => 'العربية',
                'flag' => '🇸🇦',
                'code' => 'AR',
                'rtl' => true,
                'locale' => 'ar'
            ],
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
                'code' => 'EN',
                'rtl' => false,
                'locale' => 'en'
            ]
        ];
    }

    /**
     * Get current language info
     */
    public static function getCurrentLanguage(): array
    {
        $currentLocale = app()->getLocale();
        $languages = self::getAvailableLanguages();
        
        return $languages[$currentLocale] ?? $languages['en'];
    }

    /**
     * Check if current language is RTL
     */
    public static function isRtl(): bool
    {
        return self::getCurrentLanguage()['rtl'] ?? false;
    }

    /**
     * Get language direction
     */
    public static function getDirection(): string
    {
        return self::isRtl() ? 'rtl' : 'ltr';
    }

    /**
     * Get supported locales
     */
    public static function getSupportedLocales(): array
    {
        return config('app.available_locales', ['ar', 'en']);
    }

    /**
     * Check if locale is supported
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::getSupportedLocales());
    }
}