<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper bg-[#f8f5f0]">
            <!-- أنماط الخلفية -->
            <div class="relative">
                <div class="pattern-top top-24"></div>

                <!-- المحتوى الرئيسي -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 md:py-12 lg:py-16">
                    <!-- عنوان الصفحة -->
                    <div class="mb-6 sm:mb-8 md:mb-10 lg:mb-12 z-10">
                        <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16">
                            <h2 class="text-2xl sm:text-3xl md:text-4xl text-[#5D6019] font-bold font-tajawal">معاينة كتاب: أسرار الكون</h2>
                        </div>
                    </div>

                    <!-- Header -->
                    <header class="bg-white shadow-sm py-2 sm:py-3 px-4 sm:px-6 rounded-xl mb-4 sm:mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-center w-full sm:w-auto">
                                <img src="{{ asset('images/logo-01.jpg') }}" alt="الشعار" class="h-10 sm:h-12 ml-3 sm:ml-4">
                                <div class="w-full sm:w-auto">
                                    <h1 class="text-xl sm:text-2xl font-bold text-[#5D6019] font-tajawal">كتاب أسرار الكون</h1>
                                    <p class="text-gray-600 text-base sm:text-lg">تأليف: د. محمد بن علي الحسيني</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                                <div class="flex items-center bg-[#f0e9de] rounded-full px-3 py-1 sm:px-4 sm:py-1.5 w-full sm:w-auto">
                                    <span class="text-[#39100C] font-medium text-sm sm:text-base ml-2 sm:ml-2">الصفحة</span>
                                    <span class="bg-[#5D6019] text-white rounded-full w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center font-bold text-sm sm:text-base">24</span>
                                    <span class="text-[#39100C] mx-1 sm:mx-2 text-sm sm:text-base">من</span>
                                    <span class="text-[#957717] font-bold text-sm sm:text-base">356</span>
                                </div>

                                <button class="bg-[#FF7300] hover:bg-[#e06600] text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-colors flex items-center w-full sm:w-auto justify-center mt-2 sm:mt-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4v2m-6 4h12m-6 4v2m-6-8h12a2 2 0 012 2v4a2 2 0 01-2 2H3a2 2 0 01-2-2v-4a2 2 0 012-2z" />
                                    </svg>
                                    English
                                </button>
                            </div>
                        </div>
                    </header>

                    <!-- Main Content -->
                    <div class="flex flex-col gap-4 sm:gap-6">
                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] p-2 sm:p-3">
                            <div class="flex flex-col sm:flex-row sm:flex-wrap items-center gap-2 sm:gap-3">
                                <div class="flex items-center border-b border-[#e0d9cc] pb-2 w-full sm:w-auto sm:border-0 sm:pb-0 sm:border-r sm:pr-4">
                                    <button class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A-
                                    </button>
                                    <span class="mx-1 sm:mx-2 text-[#39100C] font-medium text-sm sm:text-base">100%</span>
                                    <button class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-sm sm:text-base">
                                        A+
                                    </button>
                                </div>

                                <div class="flex items-center flex-1 min-w-[150px] w-full sm:w-auto">
                                    <input type="text" placeholder="ابحث في النص..." class="flex-1 px-3 py-1.5 sm:px-4 sm:py-2 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] font-tajawal text-sm">
                                    <button class="mr-1 sm:mr-2 bg-[#5D6019] text-white p-1.5 sm:p-2 rounded-lg hover:bg-[#4a4d13] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center border-t border-[#e0d9cc] pt-2 w-full sm:w-auto sm:border-0 sm:pt-0 sm:border-l sm:pl-4">
                                    <button class="bg-[#f0e9de] text-[#5D6019] px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg hover:bg-[#e8e0d0] transition-colors font-tajawal text-sm sm:text-base whitespace-nowrap">
                                        إظهار الحركات
                                    </button>
                                </div>

                                <div class="flex items-center w-full sm:w-auto justify-end pt-2 sm:pt-0 sm:border-l sm:pl-4">
                                    <div class="flex space-x-1 sm:space-x-2 space-x-reverse">
                                        <button class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </button>
                                        <button class="text-gray-600 hover:text-[#5D6019] p-1.5 sm:p-2 rounded-full hover:bg-[#f0e9de] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5v14a2 2 0 002 2h18a2 2 0 002-2V5a2 2 0 00-2-2H3a2 2 0 00-2 2zM7 10l5 5 5-5" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Area -->
                        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
                            <!-- Sidebar (Right) -->
                            <aside class="lg:w-72 flex-shrink-0 w-full">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] h-full">
                                    <div class="bg-[#5D6019] p-3 sm:p-4">
                                        <h2 class="text-white text-lg sm:text-xl font-bold font-tajawal">فهرس المحتويات</h2>
                                    </div>

                                    <div class="p-3 sm:p-4 max-h-[70vh] overflow-y-auto">
                                        <ul class="space-y-2">
                                            <li>
                                                <a href="#" class="text-[#5D6019] font-bold flex items-center text-base sm:text-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                    المقدمة
                                                </a>
                                                <ul class="mr-3 mt-1 sm:mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-2 sm:pr-3">
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">عن الكتاب</a></li>
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">أهمية الموضوع</a></li>
                                                </ul>
                                            </li>

                                            <li>
                                                <a href="#" class="text-[#5D6019] font-bold flex items-center text-base sm:text-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                    الفصل الأول: نشأة الكون
                                                </a>
                                                <ul class="mr-3 mt-1 sm:mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-2 sm:pr-3">
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">النظرية الكونية</a></li>
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">الأدلة العلمية</a></li>
                                                    <li class="mt-1 sm:mt-2">
                                                        <a href="#" class="text-gray-700 hover:text-[#5D6019] flex items-center text-sm sm:text-base">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                            الأبحاث الحديثة
                                                        </a>
                                                        <ul class="mr-3 mt-1 sm:mt-2 space-y-1 border-r-2 border-dashed border-[#e0d9cc] pr-2 sm:pr-3">
                                                            <li><a href="#" class="text-gray-600 hover:text-[#5D6019] block text-xs sm:text-sm">دراسة ناسا 2023</a></li>
                                                            <li><a href="#" class="text-gray-600 hover:text-[#5D6019] block text-xs sm:text-sm">أبحاث جامعة هارفارد</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li>
                                                <a href="#" class="text-[#5D6019] font-bold flex items-center text-base sm:text-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                    الفصل الثاني: الثقوب السوداء
                                                </a>
                                                <ul class="mr-3 mt-1 sm:mt-2 space-y-1 border-r-2 border-[#e0d9cc] pr-2 sm:pr-3">
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">خصائص الثقوب</a></li>
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">التفاعل مع المادة</a></li>
                                                    <li><a href="#" class="text-gray-700 hover:text-[#5D6019] block text-sm sm:text-base">الثقوب البيضاء</a></li>
                                                </ul>
                                            </li>

                                            <li>
                                                <a href="#" class="text-[#5D6019] font-bold text-base sm:text-lg">الخاتمة</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </aside>

                            <!-- Main Content Area -->
                            <main class="flex-1">
                                <!-- Book Page Content -->
                                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#e0d9cc] min-h-[50vh] sm:min-h-[60vh] md:min-h-[70vh] flex flex-col">
                                    <!-- Book Content -->
                                    <div class="flex-1 p-4 sm:p-6 md:p-8 font-tajawal text-right leading-loose text-base sm:text-lg text-[#39100C] bg-[#faf8f5]">
                                        <div class="max-w-3xl mx-auto">
                                            <h2 class="text-xl sm:text-2xl font-bold text-[#5D6019] mb-4 sm:mb-6 border-b border-[#e0d9cc] pb-3 sm:pb-4">الفصل الأول: نشأة الكون</h2>

                                            <p class="mb-4 sm:mb-6 leading-relaxed">
                                                في البداية كان الكون نقطة صغيرة كثيفة لا يُقاس حجمها، ثم حدث الانفجار العظيم الذي بدأ معه توسع الكون. تشير الأدلة العلمية إلى أن هذا الحدث وقع قبل 13.8 مليار سنة، حيث بدأ الكون في التوسع من حالة كثافة وحرارة عالية جدًا.
                                            </p>

                                            <p class="mb-4 sm:mb-6 leading-relaxed">
                                                خلال المليون عام الأولى، تشكلت الذرات البسيطة مثل الهيدروجين والهيليوم، والتي كانت هي اللبنات الأساسية لتكوين النجوم والمجرات. تطور الكون عبر مراحل متعددة، من تكوين العناقيد المجرية إلى تشكل النجوم الأولى.
                                            </p>

                                            <div class="bg-[#f8f5f0] border border-[#e0d9cc] rounded-xl p-4 sm:p-6 my-6 sm:my-8">
                                                <h3 class="text-lg sm:text-xl font-bold text-[#957717] mb-3 sm:mb-4">ملاحظة هامة:</h3>
                                                <p class="text-gray-700 text-sm sm:text-base leading-relaxed">
                                                    تشير أحدث الأبحاث من مرصد هابل إلى وجود تناقضات في نموذج الانفجار العظيم التقليدي، مما يفتح الباب أمام نظريات جديدة حول طبيعة الكون المبكر. هذه التناقضات تشمل توزيع المجرات في الفضاء والانزياح الأحمر غير المتوقع لبعض المجرات البعيدة.
                                                </p>
                                            </div>

                                            <p class="mb-4 sm:mb-6 leading-relaxed">
                                                في العقد الأخير، قدمت ملاحظات تلسكوب جيمس ويب الفضائي دليلًا جديدًا على وجود نجوم مبكرة أكثر مما كان يُعتقد سابقًا، مما يعيد تشكيل فهمنا لمرحلة تشكل النجوم الأولى. هذه النجوم التي تشكلت من العناصر الأولية فقط (الهيدروجين والهيليوم) تلعب دورًا محوريًا في تكوين العناصر الثقيلة لاحقًا.
                                            </p>

                                            <p class="leading-relaxed">
                                                يبقى السؤال الأكبر: ما الذي سبق الانفجار العظيم؟ وهل كان هناك "قبل" أصلاً؟ هذه الأسئلة تدفع العلماء إلى استكشاف نظريات مثل الكون المتعدد والزمكان الكمي، والتي قد تقدم إجابات في المستقبل القريب.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Section Progress Bar - Moved to the bottom -->
                                    <div class="px-3 py-2 sm:px-4 sm:py-3 border-t border-[#e0d9cc] bg-[#faf8f5]">
                                        <div class="flex flex-col md:flex-row items-center justify-between gap-3 sm:gap-4">
                                            <!-- Navigation Buttons -->
                                            <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse w-full md:w-auto">
                                                <button class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>

                                                <span class="text-[#39100C] font-medium text-sm sm:text-base">74/3</span>

                                                <button class="bg-[#f0e9de] hover:bg-[#e8e0d0] p-1.5 sm:p-2 rounded-full transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Page Navigation Input -->
                                            <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse w-full md:w-auto">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    max="356"
                                                    placeholder="رقم الصفحة"
                                                    class="w-16 sm:w-20 px-2 py-1 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] text-center text-sm">
                                                <span class="text-[#39100C] font-medium text-sm sm:text-base">/</span>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    max="10"
                                                    placeholder="الجزء"
                                                    class="w-12 sm:w-16 px-2 py-1 border border-[#e0d9cc] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#957717] text-center text-sm">
                                                <button class="bg-[#5D6019] text-white px-2 py-1 sm:px-3 sm:py-1.5 rounded-lg hover:bg-[#4a4d13] transition-colors text-sm">
                                                    اذهب
                                                </button>
                                            </div>

                                            <!-- Progress Slider -->
                                            <div class="w-full md:w-64">
                                                <div class="relative pt-1">
                                                    <div class="flex items-center justify-between pb-1">
                                                        <span class="text-xs sm:text-sm font-semibold inline-block py-1 px-2 uppercase rounded-full text-[#5D6019] bg-[#faf8f5]">
                                                            74% مكتمل
                                                        </span>
                                                    </div>
                                                    <div class="overflow-hidden h-2 sm:h-2.5 rounded bg-[#f0e9de] cursor-pointer">
                                                        <div
                                                            style="width: 74%"
                                                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-[#5D6019] transition-all duration-500"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Text Controls - Moved to the far right -->
                                            <div class="flex items-center space-x-1 sm:space-x-2 space-x-reverse w-full md:w-auto justify-end">
                                                <button class="bg-[#f0e9de] text-[#5D6019] px-2 py-1 sm:px-3 sm:py-1.5 rounded-lg hover:bg-[#e8e0d0] transition-colors font-tajawal text-sm whitespace-nowrap">
                                                    <span class="hidden sm:inline">إظهار</span> الحركات
                                                </button>
                                                <div class="flex items-center border-r border-[#e0d9cc] pr-2">
                                                    <button class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-xs sm:text-sm">
                                                        A-
                                                    </button>
                                                    <span class="mx-1 sm:mx-2 text-[#39100C] font-medium text-xs sm:text-sm">100%</span>
                                                    <button class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-full bg-[#f0e9de] hover:bg-[#e8e0d0] text-[#5D6019] font-bold transition-colors text-xs sm:text-sm">
                                                        A+
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</x-superduper.main>