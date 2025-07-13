<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $favicon = $generalSettings->site_favicon ?? null;
        $brandLogo = $generalSettings->brand_logo ?? null;
        $siteName = $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'المكتبة المتكاملة');
        
        $title = $title ?? $siteName;
    @endphp

    <!-- SEO Meta Tags -->
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $pageDescription ?? $siteSettings->description ?? 'المكتبة المتكاملة' }}">
    <meta name="keywords" content="{{ $metaKeywords ?? 'كتب, مكتبة, إسلامية, قراءة, مؤلفين' }}">
    
    <!-- Favicon -->
    @if($favicon)
        <link rel="shortcut icon" href="{{ Storage::url($favicon) }}" type="image/x-icon">
    @else
        <link rel="shortcut icon" href="{{ asset('superduper/img/favicon.png') }}" type="image/x-icon">
    @endif

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $pageDescription ?? $siteSettings->description ?? 'المكتبة المتكاملة' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($brandLogo)
        <meta property="og:image" content="{{ Storage::url($brandLogo) }}">
    @endif
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $pageDescription ?? $siteSettings->description ?? 'المكتبة المتكاملة' }}">
    @if($brandLogo)
        <meta name="twitter:image" content="{{ Storage::url($brandLogo) }}">
    @endif

    <!-- Theme CSS via Vite -->
    @vite(['resources/css/app.css'])

    <!-- Icon Font -->
    <link rel="stylesheet" href="{{ asset('superduper/fonts/iconfonts/font-awesome/stylesheet.css') }}">
    
    <!-- Site font -->
    <link rel="stylesheet" href="{{ asset('superduper/fonts/webfonts/public-sans/stylesheet.css') }}" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('superduper/css/vendors/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('superduper/css/vendors/jos.css') }}" />
    <link rel="stylesheet" href="{{ asset('superduper/css/style.min.css') }}" />
    
    <!-- Custom RTL CSS for Arabic -->
    <link rel="stylesheet" href="{{ asset('superduper/css/rtl-custom.css') }}" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('css')
    @livewireStyles

    <!-- Custom CSS -->
    @if(isset($scriptSettings->custom_css))
        <style>
            {!! $scriptSettings->custom_css !!}
        </style>
    @endif

    <!-- Header scripts -->
    @if(isset($scriptSettings->header_scripts))
        {!! $scriptSettings->header_scripts !!}
    @endif
</head>

<body>
    <!-- Body start scripts -->
    @if(isset($scriptSettings->body_start_scripts))
        {!! $scriptSettings->body_start_scripts !!}
    @endif

    @if(isset($siteSettings->is_maintenance) && $siteSettings->is_maintenance)
        <div class="maintenance-mode">
            <div class="container">
                <h1>Site Under Maintenance</h1>
                <p>We're currently performing maintenance. Please check back soon.</p>
            </div>
        </div>
    @else
        <x-superduper.header />

        <main>
            {{ $slot }}
        </main>

        <x-superduper.footer />
    @endif

    <!-- Vite compiled JS -->
    @vite(['resources/js/app.js'])

    <!-- Vendor js -->
    <script src="{{ asset('superduper/js/vendors/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('superduper/js/vendors/fslightbox.js') }}"></script>
    <script src="{{ asset('superduper/js/vendors/jos.min.js') }}"></script>
    <script src="{{ asset('superduper/js/main.js') }}"></script>

    @livewireScripts

    <!-- Custom JS -->
    @if(isset($scriptSettings->custom_js))
        <script>
            {!! $scriptSettings->custom_js !!}
        </script>
    @endif

    <!-- Footer scripts -->
    @if(isset($scriptSettings->footer_scripts))
        {!! $scriptSettings->footer_scripts !!}
    @endif

    <!-- Body end scripts -->
    @if(isset($scriptSettings->body_end_scripts))
        {!! $scriptSettings->body_end_scripts !!}
    @endif

    @stack('js')
</body>

</html>
