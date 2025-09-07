<div class="page-wrapper relative z-[1]" dir="rtl">
    <main class="relative overflow-hidden main-wrapper ">
        <!-- background pattern-->
        <div class="relative">
            <div class="pattern-top top-24"></div>
            <div class="pattern-top top-80"></div>
            <!-- end of background pattern-->
            <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">

                {{-- Header Section --}}
                <div class="mb-12 z-10">
                    <div class="flex items-center gap-3 mb-8">
                        <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                        <h2 class="text-4xl text-green-800 font-bold">{{ $title }}</h2>
                    </div>

                    {{-- Search Box --}}
                    <div class="relative max-w-md mx-auto">
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="{{ $type === 'authors' ? 'ابحث في المؤلفين...' : 'ابحث في الكتب...' }}"
                            class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <button class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="w-5 h-5 text-gray-400 hover:text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Filter Buttons --}}
                <div class="flex flex-wrap gap-4 mb-8">
                    {{-- عرض رابط للعودة إلى القسم إذا كان المستخدم يتصفح قسماً معيناً --}}
                    @if ($currentSection)
                        <span class="bg-green-100 text-green-800 px-5 py-2 rounded-full">
                            قسم: {{ $currentSection->name }}
                        </span>
                    @endif
                </div>

                {{-- Data Table --}}
                <x-data-table :data="$this->data" :type="$type" emptyMessage="جرب البحث بكلمات أخرى"
                    :showPerPage="true" />

            </section>
        </div>
    </main>
</div>
