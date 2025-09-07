{{-- Banner Component - Now receives data from Livewire instead of querying directly --}}
@props(['banners' => collect([])])

{{-- Debug info - remove after testing --}}

<div class="bg-white min-h-screen">
    <!-- Main Hero Section -->
    <section class="relative overflow-hidden">
        @if ($banners->count() > 0)
            <!-- Swiper Container with Livewire protection -->
            <div wire:ignore>
                <div class="swiper myHomeSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($banners as $banner)
                            <div class="swiper-slide" data-banner-id="{{ $banner->id }}">
                                <div class="relative h-screen">
                                    <!-- Background Image -->
                                    <img src="{{ $banner->hasImage() ? $banner->getImageUrl('large') : asset('images/placeholder.jpg') }}"
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
                                                <div
                                                    class="flex flex-col sm:flex-row gap-2 sm:gap-4 mb-6 sm:mb-10 justify-center px-4 sm:px-0">
                                                    <button
                                                        class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-4 sm:px-8 py-2 sm:py-3 rounded-2xl sm:rounded-3xl text-sm sm:text-base font-bold shadow-md">
                                                        المؤلفين
                                                    </button>
                                                    <button
                                                        class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-4 sm:px-8 py-2 sm:py-3 rounded-2xl sm:rounded-3xl text-sm sm:text-base font-bold shadow-md">
                                                        محتوى الكتب
                                                    </button>
                                                    <button
                                                        class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-4 sm:px-8 py-2 sm:py-3 rounded-2xl sm:rounded-3xl text-sm sm:text-base font-bold shadow-md">
                                                        عناوين الكتب
                                                    </button>
                                                </div>

                                                <!-- Search Bar -->
                                                {{-- action="{{ route('search') }}" method="GET" --}}
                                                <form class="max-w-xs sm:max-w-xl mx-auto px-4 sm:px-0">
                                                    <div
                                                        class="relative bg-white rounded-full px-4 sm:px-6 py-2 sm:py-3 flex items-center gap-2 sm:gap-3 shadow-lg">
                                                        <img src="{{ asset('images/iconly-light-search0.svg') }}"
                                                            alt="Search" class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0">
                                                        <input type="text" name="q"
                                                            placeholder="إبحث في محتوى الكتب ..."
                                                            class="w-full bg-transparent border-none focus:ring-0 text-sm sm:text-base text-gray-700 placeholder-gray-500 placeholder:text-xs sm:placeholder:text-sm">
                                                        <button type="submit"
                                                            class="absolute left-2 sm:left-4 p-1.5 sm:p-2 rounded-full transition-all duration-300 hover:bg-green-100 hover:scale-110 active:scale-95">
                                                            <img src="{{ asset('images/iconly-bold-send0.svg') }}"
                                                                alt="Search icon" class="w-4 h-4 sm:w-5 sm:h-5">
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Swiper Navigation and Pagination -->
                    @if ($banners->count() > 1)
                        <div class="swiper-pagination myHomeSwiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    @endif
                </div>
            </div>
        @else
            <div class="h-screen flex items-center justify-center bg-gray-100">
                <p class="text-gray-500 text-xl">لا توجد بانرز للعرض</p>
            </div>
        @endif
    </section>
</div>

@push('js')
    <script>
        console.log('Banner script loaded');

        // Swiper initialization function with destroy/recreate pattern
        window.initHomeSwiper = function() {
            console.log('initHomeSwiper called');

            // Destroy existing instance if it exists
            if (window.homeSwiper && typeof window.homeSwiper.destroy === 'function') {
                console.log('Destroying existing swiper');
                window.homeSwiper.destroy(true, true);
                window.homeSwiper = null;
            }

            // Wait for DOM to be ready
            const swiperElement = document.querySelector('.myHomeSwiper');
            if (!swiperElement) {
                console.warn('Swiper element not found');
                return;
            }

            console.log('Swiper element found, initializing...');
            console.log('Banners count from PHP:', {{ $banners->count() }});

            // Check if Swiper is available
            if (typeof Swiper === 'undefined') {
                console.error('Swiper is not loaded!');
                return;
            }

            // Initialize new Swiper instance
            window.homeSwiper = new Swiper('.myHomeSwiper', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: {{ $banners->count() > 1 ? 'true' : 'false' }},
                autoplay: {{ $banners->count() > 1 ? '{ delay: 2500, disableOnInteraction: false }' : 'false' }},
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 1000,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.myHomeSwiper-pagination',
                    clickable: true,
                },
                on: {
                    init: function() {
                        console.log('Swiper initialized successfully');
                        trackBannerView(this);
                    },
                    slideChange: function() {
                        console.log('Slide changed to:', this.activeIndex);
                        // trackBannerView(this);
                    }
                }
            });

            console.log('Swiper instance created:', window.homeSwiper);
        };

        // Initialize when DOM is ready
        function initSwiperWhenReady() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(() => window.initHomeSwiper && window.initHomeSwiper(), 100);
                });
            } else {
                setTimeout(() => window.initHomeSwiper && window.initHomeSwiper(), 100);
            }
        }

        // Multiple initialization strategies
        initSwiperWhenReady();

        // Initialize on Livewire load (for SPA navigation)
        document.addEventListener('livewire:load', function() {
            console.log('Livewire loaded, initializing swiper');
            setTimeout(() => window.initHomeSwiper && window.initHomeSwiper(), 200);
        });

        // Initialize on Livewire navigated (Livewire v3)
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated, initializing swiper');
            setTimeout(() => window.initHomeSwiper && window.initHomeSwiper(), 200);
        });

        // Re-initialize when banners data changes
        window.addEventListener('init-swiper', function() {
            console.log('init-swiper event received');
            setTimeout(() => window.initHomeSwiper && window.initHomeSwiper(), 100);
        });

        // Banner tracking functions (unchanged)
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
