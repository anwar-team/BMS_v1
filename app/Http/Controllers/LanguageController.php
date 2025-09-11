<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    public function switch(Request $request, $language)
    {
        if (in_array($language, ['ar', 'en'])) {
            Session::put('locale', $language);
        }
        
        return Redirect::back();
    }
}
