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
                            <h2 class="text-4xl text-green-800 font-bold">جميع الكتب</h2>
                        </div>

                        <!-- Search Box -->
                        <div class="relative max-w-md mx-auto">
                            <input type="text" placeholder="ابحث في الكتب..."
                                class="w-full px-4 py-3 pr-12 text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Search Box -->
                    </div>

                    <div class="flex flex-wrap gap-4 mb-8">
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                            الكتب المفتوحة مؤخراً
                        </button>
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                            أكثر الكتب قراءةً
                        </button>
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                            كتب مضافة حديثاً
                        </button>
                        <a href="#" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                            جميع الكتب
                        </a>
                    </div>

                    {{-- Books Table Will Go Here --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-6 py-3 text-end text-sm font-bold text-green-800">#</th>
                                        <th class="px-6 py-3 text-end text-sm font-bold text-green-800">المؤلف</th>
                                        <th class="px-6 py-3 text-end text-sm font-bold text-green-800">اسم الكتاب</th>
                                        <th class="px-6 py-3 text-end text-sm font-bold text-green-800">التصنيف</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @for ($i = 0; $i < 100; $i++)
                                        <tr>
                                        <td class="px-6 py-4 text-sm text-green-800 font-bold text-end">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 text-end">عبد الله عزام</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 text-end">أذكار الصباح والمساء</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 text-end">الأذكار والأوراد والأدعية</td>
                                        </tr>
                                        @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 flex items-center justify-between border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <button class="p-2 rounded-full hover:bg-gray-100">
                                    <img src="{{ asset('images/chevron-right-filled0.svg') }}" alt="Previous" class="w-6 h-6">
                                </button>
                                <button class="p-2 rounded-full hover:bg-gray-100">
                                    <img src="{{ asset('images/chevron-left-filled0.svg') }}" alt="Next" class="w-6 h-6">
                                </button>
                                <span class="text-sm text-gray-600">5-1 من 100</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <span class="absolute left-12 top-1/2 -translate-y-1/2 text-sm text-gray-600">10</span>
                                    <img src="{{ asset('images/arrow-drop-down-filled0.svg') }}" alt="Dropdown" class="w-6 h-6">
                                </div>
                                <span class="text-sm text-gray-600">عدد الصفوف في الصفحة:</span>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
        </main>
    </div>
</x-superduper.main>