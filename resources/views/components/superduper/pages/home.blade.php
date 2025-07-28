<x-superduper.main>

    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">

            <!--
========================================
          My Main Aziz     
========================================
 -->
            <!-- <div class="bg-white min-h-screen">
                 Main Hero Section 
                <section class="relative overflow-hidden">
                    <img
                        src="{{ asset('images/whats-app-image-2025-03-20-at-1-58-04-pm-10.png') }}"
                        alt="Library background"
                        class="absolute inset-0 w-full h-96 object-cover">
                    <div class="absolute inset-0 bg-black/30"></div>

                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20">
                        <div class="max-w-3xl mx-auto text-center">
                            <h1 class="text-5xl text-white font-bold mb-6 leading-tight">
                                مكتبة تكاملت موضوعاتها<br>وكتبها
                            </h1>
                            <p class="text-xl text-white mb-10">
                                اكتشف آلاف الكتب في الحديث، الفقه، الأدب، البلاغة، و التاريخ و الأنساب و غيرها الكثير متاحة لك في مكان واحد
                            </p>

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

                            <div class="max-w-xl mx-auto bg-white rounded-full px-6 py-3 flex items-center gap-3">
                                <img src="{{ asset('images/iconly-light-search0.svg') }}" alt="Search" class="w-6 h-6">
                                <span class="text-gray-500">إبحث في محتوى الكتب ...</span>
                                <img src="{{ asset('images/iconly-bold-send0.svg') }}" alt="Search icon" class="w-5 h-5">
                            </div>
                        </div>
                    </div>
                </section> -->
            <x-superduper.components.banner />


            <!-- Book Categories-->
            <x-superduper.components.categories-section :limit="6" :showViewAll="true" />

    <!-- Books Table -->
    <!-- background pattern-->
    <div class="relative">
        <div class="pattern-top top-10"></div>
        <!-- end of background pattern-->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group7.svg') }}" alt="Icon" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">الكتب</h2>
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
                <a href="{{ route('all') }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
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
                            @for ($i = 0; $i < 6; $i++)
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

    <!-- Authors Section -->
    <!-- background pattern-->
    <div class="relative">
        <div class="pattern-top top-10"></div>
        <!-- end of background pattern-->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ asset('images/group8.svg') }}" alt="Icon" class="w-16 h-16">
                <h2 class="text-4xl text-green-800 font-bold">المؤلفين</h2>
            </div>

            <div class="flex flex-wrap gap-4 mb-8">
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    أكثر المؤلفين قراءةً
                </button>
                <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    مؤلفين جدد
                </button>
                <a href="{{ route('all') }}" class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full transition-colors duration-300 hover:bg-green-800 hover:text-white">
                    جميع المؤلفين
                </a>
            </div>

            {{-- Authors Table Will Go Here --}}
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
                            @for ($i = 0; $i < 6; $i++)
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
                            <img src="{{ asset('images/chevron-right-filled1.svg') }}" alt="Previous" class="w-6 h-6">
                        </button>
                        <button class="p-2 rounded-full hover:bg-gray-100">
                            <img src="{{ asset('images/chevron-left-filled1.svg') }}" alt="Next" class="w-6 h-6">
                        </button>
                        <span class="text-sm text-gray-600">5-1 من 100</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <span class="absolute left-12 top-1/2 -translate-y-1/2 text-sm text-gray-600">10</span>
                            <img src="{{ asset('images/arrow-drop-down-filled1.svg') }}" alt="Dropdown" class="w-6 h-6">
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