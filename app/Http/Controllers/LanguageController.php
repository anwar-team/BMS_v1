<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    public function switch(Request $request, $language)
    {
        $availableLocales = config('app.available_locales', ['ar', 'en']);
        
        if (in_array($language, $availableLocales)) {
            Session::put('locale', $language);
        }
        
        return Redirect::back();
    }
}
