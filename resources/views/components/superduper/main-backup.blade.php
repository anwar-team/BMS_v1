<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Site Settings Meta Tags -->
    @if(isset($siteSettings))
        <title>{{ $title ?? $siteSettings->site_name }}</title>
        <meta name="description" content="{{ $description ?? $siteSettings->site_description }}">
        <meta name="keywords" content="{{ $keywords ?? $siteSettings->site_keywords }}">
        
        @if($siteSettings->site_favicon)
            <link rel="icon" type="image/x-icon" href="{{ Storage::url($siteSettings->site_favicon) }}">
        @endif
        
        @if($siteSettings->site_logo)
            <meta property="og:image" content="{{ Storage::url($siteSettings->site_logo) }}">
        @endif
        
        <meta property="og:title" content="{{ $title ?? $siteSettings->site_name }}">
        <meta property="og:description" content="{{ $description ?? $siteSettings->site_description }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title ?? $siteSettings->site_name }}">
        <meta name="twitter:description" content="{{ $description ?? $siteSettings->site_description }}">
        
        @if($siteSettings->site_logo)
            <meta name="twitter:image" content="{{ Storage::url($siteSettings->site_logo) }}">
        @endif
    @endif

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Custom Styles -->
    @stack('css')

</head>

<body>
    {{ $slot }}

    @livewireScripts
    @stack('js')
</body>

</html>
