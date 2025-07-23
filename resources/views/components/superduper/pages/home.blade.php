<x-superduper.main>

    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">

            {{-- <x-superduper.components.hero /> --}}

            {{-- <x-superduper.components.value-proposition /> --}}

            {{-- <x-superduper.components.packages-plugins /> --}}

            {{-- Showcases
            <div class="relative py-8 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-background-white to-background-wheat dark:from-primary-900 dark:to-primary-800 -z-10"></div>
                
                <div class="container px-4 py-16 mx-auto">
                    <div class="mb-16 text-center">
                        <span class="inline-block px-4 py-1 mb-3 text-sm font-medium rounded-full bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-200">Content Management</span>
                        <h2 class="mb-4 font-bold">Feature-Rich Blog Platform, Ready to Publish</h2>
                        <p class="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-300">Launch your content strategy immediately with SuperDuper's integrated blog system</p>
                    </div>
                    
                    <!-- Blog Showcase -->
                    <div class="mb-16">
                        <livewire:super-duper.blog-section-slider
                            :limit="6"
                            :featured-only="false"
                            :category-slug="null"
                        />
                    </div>
                    
                    <!-- Banner Showcase -->
                    <div class="pt-8 border-t border-background-light dark:border-primary-700">
                        <div class="mb-10 text-center">
                            <span class="inline-block px-4 py-1 mb-3 text-sm font-medium rounded-full bg-secondary-100 text-secondary-800 dark:bg-secondary-900 dark:text-secondary-200">Banner Management</span>
                            <h2 class="mb-4 text-3xl font-bold">Engaging Banner System</h2>
                            <p class="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-300">Create eye-catching banners with intuitive management interface</p>
                        </div>
                        
                        <div class="max-w-5xl mx-auto overflow-hidden bg-white rounded-lg shadow-lg">
                            <x-superduper.components.banner />
                        </div>
                        
                        <div class="mt-8 text-center">
                            <div class="inline-flex items-center justify-center px-4 py-2 font-medium rounded-lg text-primary-700 bg-primary-100 dark:bg-primary-900 dark:text-primary-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Both blog and banner modules are fully customizable through our admin interface</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 -z-10" style="clip-path: polygon(0 100%, 100% 0, 100% 100%, 0% 100%);"></div>
            </div> --}}

            <!-- freq qustion
            <div class="container px-4 py-16 mx-auto">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold">Frequently Asked Questions</h2>
                    <p class="max-w-2xl mx-auto text-lg">Get answers to the most common questions about SuperDuper Starter Kit</p>
                </div>
                
                <div class="max-w-4xl mx-auto divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="py-6">
                        <div class="flex items-center justify-between cursor-pointer">
                            <h3 class="text-xl font-semibold">What makes this different from other Filament starter kits?</h3>
                        </div>
                        <div class="mt-4" x-show="open">
                            <p class="text-gray-700 dark:text-gray-300">SuperDuper provides a complete ecosystem, not just scaffolding. It includes integrated modules for content management, user management, media handling, and more. Our focus on developer experience means cleaner code organization, better documentation, and pre-built solutions for common requirements like multilingual support and SEO optimization.</p>
                        </div>
                    </div>
                    
                    <div class="py-6">
                        <div class="flex items-center justify-between cursor-pointer">
                            <h3 class="text-xl font-semibold">Can I use this for commercial projects?</h3>
                        </div>
                        <div class="mt-4" x-show="open">
                            <p class="text-gray-700 dark:text-gray-300">Yes! SuperDuper is released under the MIT license, which means you can use it for personal or commercial projects without restrictions. You're free to modify, distribute, and use it in your own work without attribution, though a shoutout is always appreciated!</p>
                        </div>
                    </div>
                    
                    <div class="py-6">
                        <div class="flex items-center justify-between cursor-pointer">
                            <h3 class="text-xl font-semibold">Do I need to know Laravel or Filament to use this?</h3>
                        </div>
                        <div class="mt-4" x-show="open">
                            <p class="text-gray-700 dark:text-gray-300">Basic familiarity with Laravel and Filament is recommended. However, the starter kit is designed to be intuitive, with thorough documentation to help you understand the structure. If you're new to Filament, this kit actually makes it easier to learn by providing working examples of best practices.</p>
                        </div>
                    </div>
                    
                    <div class="py-6">
                        <div class="flex items-center justify-between cursor-pointer">
                            <h3 class="text-xl font-semibold">How do updates work with this starter kit?</h3>
                        </div>
                        <div class="mt-4" x-show="open">
                            <p class="text-gray-700 dark:text-gray-300">Once you create a project from the starter kit, it becomes your own codebase. We regularly release updates to the template itself, but applying these to an existing project is manual. For critical updates, we provide migration guides in our documentation to help you integrate new features or security patches.</p>
                        </div>
                    </div>
                    
                    <div class="py-6">
                        <div class="flex items-center justify-between cursor-pointer">
                            <h3 class="text-xl font-semibold">Is there support available if I run into issues?</h3>
                        </div>
                        <div class="mt-4" x-show="open">
                            <p class="text-gray-700 dark:text-gray-300">Yes, you can open issues on our GitHub repository and you can often find answers to common questions in our documentation or from other developers. For dedicated support or custom development, you can contact the maintainers directly.</p>
                        </div>
                    </div>
                </div>
            </div> -->
            <!--
========================================
          My Main Aziz     
========================================
 -->
            <div class="bg-white min-h-screen">
                <!-- Main Hero Section -->
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
                </section>


                <!-- Book Categories-->
                <div class="relative">
                    <div class="pattern-top top-0"></div>
                    <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                        <div class="mb-12 z-10">
                            <div class="flex items-center gap-3 mb-8">
                                <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                                <h2 class="text-4xl text-green-800 font-bold">أقسام الكتب</h2>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @for ($i = 0; $i < 6; $i++)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                                    <div class="relative">
                                        <img
                                            src="{{ asset('images/mask-group0.svg') }}"
                                            alt="Category image"
                                            class="absolute left-0 top-0 w-32 h-32">
                                        <div class="p-8">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <h3 class="text-xl text-green-800 font-bold mb-1">العقيدة</h3>
                                                    <p class="text-sm text-gray-600">1035 كتاب</p>
                                                </div>
                                                <img src="{{ asset('images/group1.svg') }}" alt="Icon" class="w-16 h-16">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="mt-12 text-center">
                            <a href="{{ route('categories') }}" class="text-green-800 bg-white border border-green-800 px-8 py-3 rounded-full font-bold shadow-md inline-block">
                                عرض جميع الأقسام
                            </a>
                        </div>
                    </section>
                </div>

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
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full">
                            الكتب المفتوحة مؤخراً
                        </button>
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full">
                            أكثر الكتب قراءةً
                        </button>
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full">
                            كتب مضافة حديثاً
                        </button>
                        <button class="bg-green-800 text-white px-5 py-2 rounded-full">
                            جميع الكتب
                        </button>
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
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full">
                            أكثر المؤلفين قراءةً
                        </button>
                        <button class="bg-white text-green-800 border border-green-800 px-5 py-2 rounded-full">
                            مؤلفين جدد
                        </button>
                        <button class="bg-green-800 text-white px-5 py-2 rounded-full">
                            جميع المؤلفين
                        </button>
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