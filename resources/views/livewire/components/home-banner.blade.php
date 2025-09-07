<div class="bg-white min-h-screen">
    <!-- Main Hero Section -->
    <section class="relative overflow-hidden">
        {{-- عزل DOM عن Livewire مع تهيئة Swiper الآمنة --}}
        <div wire:ignore x-data x-init="$nextTick(() => window.initHomeSwiper && window.initHomeSwiper())">
            <div class="swiper myHomeSwiper">
                <div class="swiper-wrapper">
                    @foreach($banners as $banner)
                    <div class="swiper-slide" data-banner-id="{{ $banner->id }}" onclick="trackBannerClick({{ $banner->id }})">
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
                                            <button class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-8 py-3 rounded-3xl font-bold shadow-md">
                                                المؤلفين
                                            </button>
                                            <button class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-8 py-3 rounded-3xl font-bold shadow-md">
                                                محتوى الكتب
                                            </button>
                                            <button class="bg-white text-green-800 border border-green-800 transition-colors duration-300 hover:bg-green-800 hover:text-white px-8 py-3 rounded-3xl font-bold shadow-md">
                                                عناوين الكتب
                                            </button>
                                        </div>

                                        <!-- Search Bar -->
                                        <form action="{{ route('search') }}" method="GET" class="max-w-xl mx-auto">
                                            <div class="relative bg-white rounded-full px-6 py-3 flex items-center gap-3">
                                                <img src="{{ asset('images/iconly-light-search0.svg') }}" alt="Search" class="w-6 h-6">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    placeholder="إبحث في محتوى الكتب ..."
                                                    class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-500">
                                                <button type="submit" class="absolute left-4 p-2 rounded-full transition-all duration-300 hover:bg-green-100 hover:scale-110 active:scale-95">
                                                    <img src="{{ asset('images/iconly-bold-send0.svg') }}" alt="Search icon" class="w-5 h-5">
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
            </div>

            @if($banners->count() > 1)
            <div class="mb-16 myHomeSwiper-pagination"></div>
            @endif
        </div>
    </section>
</div>