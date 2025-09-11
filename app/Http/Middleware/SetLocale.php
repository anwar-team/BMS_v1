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
        
        // Check if the locale is supported
        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
            
            // Set the direction for RTL languages
            if ($locale === 'ar') {
                config(['app.direction' => 'rtl']);
            } else {
                config(['app.direction' => 'ltr']);
            }
        }

        return $next($request);
    }
}
