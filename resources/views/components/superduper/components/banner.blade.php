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

                        <!-- Content Container - Centered using Flexbox -->
                        <div class="relative flex items-center justify-center h-full">
                            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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