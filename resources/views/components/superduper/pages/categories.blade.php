<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">
            <!-- background pattern-->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <div class="pattern-top top-80"></div>
                <!-- end of background pattern-->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">أقسام الكتب</h2>
                        </div>

                        <!-- Search Box -->
                        <div class="relative max-w-md mx-auto">
                            <input type="text" placeholder="ابحث في الأقسام..."
                                class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Search Box -->
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @for ($i = 0; $i < 50; $i++)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="relative">
                                <img
                                    src="{{ asset('images/mask-group0.svg') }}"
                                    alt="Category image"
                                    class="absolute left-0 top-0 w-32 h-32">
                                <div class="p-8">
                                    <div class="flex justify-around items-center">
                                        <img src="{{ asset('images/group1.svg') }}" alt="Icon" class="w-16 h-16">
                                        <div>
                                            <h3 class="text-xl text-green-800 font-bold mb-1">العقيدة</h3>
                                            <p class="text-sm text-gray-600">1035 كتاب</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    @endfor
            </div>
            </section>
    </div>
    </main>
    </div>
</x-superduper.main>