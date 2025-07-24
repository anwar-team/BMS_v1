@php
$heroBanners = \App\Models\Banner\Content::whereHas('category', function($query) {
$query->where('slug', 'home-banner');
})
->active()
->orderBy('sort')
->with(['media'])
->take(5)
->get();

@endphp
{{--
<section class="section-hero">
    <div class="relative z-10 overflow-hidden">
        <div class="pb-[60px] pt-28 md:pb-20 md:pt-36 lg:pb-[100px] lg:pt-[150px] xxl:pb-[120px] xxl:pt-[185px]">
            <div class="container-default">

                @if($heroBanners->isNotEmpty())

                    <div class="swiper hero-slider">
                        <div class="swiper-wrapper">
                            @foreach($heroBanners as $banner)
                                <div class="swiper-slide" data-banner-id="{{ $banner->id }}">
<div class="grid gap-10 items-center lg:grid-cols-2 lg:gap-[74px] xxl:grid-cols-[1fr_minmax(0,_0.8fr)]">
    <!-- Hero Content Block -->
    <div class="text-center jos xl:text-left" data-jos_animation="fade-left">
        <h1 class="mb-6 font-ClashDisplay font-medium leading-[1.06] lg:text-[60px] text-color-oil xl:text-7xl xxl:text-[90px]">
            {{ $banner->title }}
        </h1>
        <p class="mb-8 text-color-oil lg:mb-[50px]">
            {!! nl2br(e($banner->description)) !!}
        </p>

        @if($banner->click_url)
        <div class="flex flex-wrap justify-center gap-6 xl:justify-start">
            <a href="{{ $banner->click_url }}"
                target="{{ $banner->click_url_target ?? '_self' }}"
                class="inline-block btn is-outline-denim is-transparent is-large is-rounded btn-animation group banner-click-tracking"
                onclick="trackBannerClick('{{ $banner->id }}')">
                <span>{{ $banner->options['button_text'] ?? 'Learn More' }}</span>
            </a>
        </div>
        @endif
    </div>
    <!-- Hero Content Block -->

    <!-- Hero Image Block -->
    <div class="jos mx-auto lg:mx-0 max-w-full sm:max-w-[80%] md:max-w-[70%] lg:max-w-full" data-jos_animation="fade-right">
        @if($banner->hasImage())
        <img src="{{ $banner->getImageUrl('large') }}"
            srcset="{{ $banner->getImageUrl('medium') }} 768w, {{ $banner->getImageUrl('large') }} 1200w"
            alt="{{ $banner->title }}"
            width="526"
            height="527"
            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
            class="w-full h-auto banner-image" />
        @else
        <img src="https://placehold.co/526x527"
            alt="Placeholder Image"
            width="526"
            height="527"
            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
            class="w-full h-auto banner-image" />
        @endif
    </div>
    <!-- Hero Image Block -->
</div>
</div>
@endforeach
</div>
</div>

@if($heroBanners->count() > 1)
<div class="mb-16 swiper-pagination hero-slider-pagination"></div>
@endif

@endif

</div>
</div>
</div>
</section>

@push('js')
<script>
    const heroSlider = new Swiper('.hero-slider', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: {{ $heroBanners->count() > 1 ? 'true' : 'false' }},
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1000,
        navigation: false,
        pagination: {
            el: '.hero-slider-pagination',
            clickable: true,
        },
        on: {
            init: function() {
                trackBannerView(this);
            },
            slideChange: function() {
                // trackBannerView(this);
            }
        }
    });

    function trackBannerView(swiper) {
        if (!swiper || !swiper.slides) return;

        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide || !activeSlide.dataset.bannerId) return;

        const bannerId = activeSlide.dataset.bannerId;

        fetch(`/api/banners/${bannerId}/impression`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            keepalive: true
        }).catch(() => {});
    }

    function trackBannerClick(bannerId) {
        fetch(`/api/banners/${bannerId}/click`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            keepalive: true
        }).catch(() => {});
    }
</script>
@endpush
--}}

<!--
 ============================================================
           MY BANNER AZIZ
============================================================ 
-->
@php
$heroBanners = \App\Models\Banner\Content::whereHas('category', function($query) {
$query->where('slug', 'home-banner');
})
->active()
->orderBy('sort')
->with(['media'])
->take(5)
->get();
@endphp

<div class="bg-white min-h-screen">
    <!-- Main Hero Section -->
    <section class="relative overflow-hidden">
        <div class="swiper hero-slider">
            <div class="swiper-wrapper">
                @foreach($heroBanners as $banner)
                <div class="swiper-slide" data-banner-id="{{ $banner->id }}">
                    <div class="relative h-screen">
                        <!-- Background Image -->
                        <img
                            src="{{ $banner->hasImage() ? $banner->getImageUrl('large') : asset('images/placeholder.jpg') }}"
                            alt="Library background"
                            class="absolute inset-0 w-full h-full object-cover object-center">

                        <!-- Dark Overlay -->
                        <div class="absolute inset-0 bg-black/30"></div>

                        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-72">
                            <div class="max-w-3xl mx-auto text-center">
                                <!-- Title and Description -->
                                <h1 class="text-5xl text-white font-bold mb-6 leading-tight">
                                    {{ $banner->title }}
                                </h1>
                                <p class="text-xl text-white mb-10">
                                    {!! nl2br(e($banner->description)) !!}
                                </p>

                                <!-- Buttons -->
                                <div class="flex flex-col sm:flex-row gap-4 mb-10 justify-center">
                                    <button class="bg-white text-green-800 px-8 py-3 rounded-3xl font-bold shadow-md">
                                        المؤلفين
                                    </button>
                                    <button class="bg-green-700 text-white px-8 py-3 rounded-3xl font-bold shadow-md relative">
                                        <span class="absolute inset-0 border-2 border-green-900 rounded-3xl"></span>
                                        محتوى الكتب
                                    </button>
                                    <button class="bg-white text-green-800 px-8 py-3 rounded-3xl font-bold shadow-md">
                                        عناوين الكتب
                                    </button>
                                </div>

                                <!-- Search Bar -->
                                <div class="max-w-xl mx-auto bg-white rounded-full px-6 py-3 flex items-center gap-3">
                                    <img src="{{ asset('images/iconly-light-search0.svg') }}" alt="Search" class="w-6 h-6">
                                    <span class="text-gray-500">إبحث في محتوى الكتب ...</span>
                                    <img src="{{ asset('images/iconly-bold-send0.svg') }}" alt="Search icon" class="w-5 h-5">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if($heroBanners->count() > 1)
        <div class="mb-16 swiper-pagination hero-slider-pagination"></div>
        @endif
    </section>
</div>

@push('js')
<script>
    const heroSlider = new Swiper('.hero-slider', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: {{ $heroBanners->count() > 1 ? 'true' : 'false' }},
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1000,
        navigation: false,
        pagination: {
            el: '.hero-slider-pagination',
            clickable: true,
        },
        on: {
            init: function() {
                trackBannerView(this);
            },
            slideChange: function() {
                // trackBannerView(this);
            }
        }
    });

    function trackBannerView(swiper) {
        if (!swiper || !swiper.slides) return;

        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide || !activeSlide.dataset.bannerId) return;

        const bannerId = activeSlide.dataset.bannerId;

        fetch(`/api/banners/${bannerId}/impression`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            keepalive: true
        }).catch(() => {});
    }

    function trackBannerClick(bannerId) {
        fetch(`/api/banners/${bannerId}/click`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            keepalive: true
        }).catch(() => {});
    }
</script>
@endpush