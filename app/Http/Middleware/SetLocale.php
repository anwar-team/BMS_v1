<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale'));
        $availableLocales = config('app.available_locales', ['ar', 'en']);
        
        // Check if the locale is supported
        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            
            // Set the direction for RTL languages
            $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
            if (in_array($locale, $rtlLanguages)) {
                config(['app.direction' => 'rtl']);
            } else {
                config(['app.direction' => 'ltr']);
            }
        }

        return $next($request);
    }
}
