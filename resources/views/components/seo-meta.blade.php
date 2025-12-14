@props([
    'title' => config('app.name'),
    'description' => 'مكتبة شاملة للكتب الإسلامية والعربية مع نظام بحث متقدم',
    'keywords' => 'كتب إسلامية, كتب عربية, مكتبة, بحث في الكتب, التراث الإسلامي',
    'image' => asset('images/logo.png'),
    'type' => 'website',
    'author' => 'Al-Maktaba Al-Kamila'
])

{{-- Basic Meta Tags --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

{{-- SEO Meta Tags --}}
<title>{{ $title }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="{{ $author }}">
<meta name="robots" content="index, follow">
<meta name="language" content="Arabic">
<meta name="revisit-after" content="7 days">

{{-- Open Graph Meta Tags (Facebook, LinkedIn) --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="ar_AR">

{{-- Twitter Card Meta Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
<meta name="twitter:site" content="@anwaralolmaa">

{{-- Additional Meta Tags --}}
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#10b981">
<link rel="canonical" href="{{ url()->current() }}">

{{-- Favicon --}}
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

{{-- AI and Search Engine Specific --}}
<meta name="ai-content-declaration:version" content="1.0.0">
<meta name="ai-content-declaration:level" content="none">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

{{-- Schema.org JSON-LD --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ config('app.name') }}",
  "alternateName": "المكتبة الكاملة",
  "description": "{{ $description }}",
  "url": "{{ config('app.url') }}",
  "sameAs": [
    "https://alkamelah.com",
    "https://alkamelah.net",
    "https://alkamelah.org"
  ],
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ config('app.url') }}/ultra-fast-search?query={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  },
  "inLanguage": "ar",
  "publisher": {
    "@type": "Organization",
    "name": "{{ config('app.name') }}",
    "url": "{{ config('app.url') }}",
    "sameAs": [
      "https://alkamelah.com",
      "https://alkamelah.net",
      "https://alkamelah.org"
    ]
  }
}
</script>
