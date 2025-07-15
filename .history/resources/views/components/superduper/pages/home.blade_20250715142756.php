<x-superduper.main>
    <div class="bg-[#ffffff] min-h-screen relative overflow-hidden">
        <!-- Hero Section -->
        <img class="w-full h-[961px] object-cover absolute left-0 top-[-129px] z-0" src="{{ asset('public/images/figma/untitled-design-7-20.png') }}" alt="hero-bg" />
        <div class="bg-[rgba(0,0,0,0.40)] w-full h-[754px] absolute left-0 top-[76px] z-10"></div>
        <div class="relative z-20 flex flex-col items-center justify-center pt-[286px] pb-10">
            <div class="flex flex-col gap-7 items-center justify-center w-full max-w-2xl">
                <h1 class="text-white text-center font-bold text-5xl leading-[50px] font-['Tajawal',sans-serif]">
                    مكتبة تكاملت موضوعاتها<br />و كتبها
                </h1>
                <p class="text-white text-center text-xl font-normal font-['Tajawal',sans-serif]">
                    اكتشف آلاف الكتب في الحديث، الفقه، الأدب، البلاغة، و التاريخ و الأنساب و غيرها الكثير متاحة لك في مكان واحد
                </p>
            </div>
            <div class="flex flex-row gap-4 items-center justify-center mt-10">
                <button class="bg-white rounded-3xl border border-[#2c6e4a] px-8 py-4 text-[#2c6e4a] text-lg font-bold font-['Tajawal',sans-serif] shadow">المؤلفين</button>
                <button class="bg-[#23593c] rounded-3xl border border-transparent px-8 py-4 text-white text-lg font-bold font-['Tajawal',sans-serif] shadow relative">
                    <span class="absolute inset-0 rounded-3xl border border-[#1e4731]" style="box-shadow: 0px 0px 0px 4px rgba(95, 168, 124, 0.25)"></span>
                    محتوى الكتب
                </button>
                <button class="bg-white rounded-3xl border border-[#2c6e4a] px-8 py-4 text-[#2c6e4a] text-lg font-bold font-['Tajawal',sans-serif] shadow">عناوين الكتب</button>
            </div>
            <div class="bg-white rounded-[40px] flex flex-row items-center justify-between w-full max-w-xl mt-6 px-6 py-3">
                <img class="w-5 h-5" src="{{ asset('public/images/figma/iconly-bold-send0.svg') }}" alt="send" />
                <div class="flex flex-row gap-2 items-center">
                    <span class="text-black text-base font-normal font-['Tajawal',sans-serif]">إبحث في محتوى الكتب ...</span>
                    <span class="w-6 h-6 relative">
                        <img class="absolute left-1 top-1" src="{{ asset('public/images/figma/iconly-light-search0.svg') }}" alt="search" />
                    </span>
                </div>
            </div>
        </div>
        <!-- Patterns Section -->
        <div class="flex flex-row gap-0 items-center justify-center absolute left-1/2 top-[830px] opacity-40 -translate-x-1/2 z-10">
            <img class="w-[323px] h-[444px]" src="{{ asset('public/images/figma/pattern-ff-18-e-023-50.svg') }}" />
            <img class="w-[323px] h-[446px]" src="{{ asset('public/images/figma/pattern-ff-18-e-023-60.svg') }}" />
            <img class="w-[323px] h-[444px]" src="{{ asset('public/images/figma/pattern-ff-18-e-023-70.svg') }}" />
            <img class="w-[323px] h-[444px]" src="{{ asset('public/images/figma/pattern-ff-18-e-023-71.svg') }}" />
        </div>
        <!-- Book Categories Section -->
        <section class="flex flex-col gap-6 items-start justify-start w-[1170px] mx-auto mt-[1010px]">
            <div class="flex flex-col gap-10 items-end justify-start w-full">
                <div class="flex flex-row gap-3 items-center justify-start">
                    <div class="text-[#2c6e4a] text-right font-bold text-[41px] leading-[60px] flex items-center justify-end">أقسام الكتب</div>
                    <div class="w-[60px] h-[60px] ml-2">
                        <img src="{{ asset('public/images/figma/group0.svg') }}" />
                    </div>
                </div>
                <div class="flex flex-col gap-4 w-full">
                    <div class="flex flex-row gap-8 w-full">
                        <!-- Category Cards -->
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#FCF6F4] to-[#fff] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">العقيدة</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">1035 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group1.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group0.svg') }}" />
                        </div>
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#FCF6F4] to-[#fff] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">فقه عام</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">1194 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group2.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group1.svg') }}" />
                        </div>
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#FCF6F4] to-[#fff] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">علوم القرآن</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">1386 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group3.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group2.svg') }}" />
                        </div>
                    </div>
                    <div class="flex flex-row gap-8 w-full">
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#FCF6F4] to-[#fff] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">كتب إسلامية عامة</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">1412 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group4.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group3.svg') }}" />
                        </div>
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#fff] to-[#FCF6F4] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">الأذكار والأوراد والأدعية</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">123 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group5.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group4.svg') }}" />
                        </div>
                        <div class="rounded-[10px] border border-[#e8e8e9] p-8 flex flex-col gap-2.5 items-end bg-gradient-to-br from-[#FCF6F4] to-[#fff] shadow">
                            <div class="flex flex-row gap-6 items-center w-[304px]">
                                <div class="flex flex-col gap-1 items-end">
                                    <div class="text-[#2c6e4a] text-right font-bold text-lg leading-[26px] w-[220px]">بحوث ومسائل فقهية</div>
                                    <div class="text-[rgba(79,81,83,0.80)] text-right text-sm leading-[18px] font-medium">3126 كتاب</div>
                                </div>
                                <div class="w-[60px] h-[60px]">
                                    <img src="{{ asset('public/images/figma/group6.svg') }}" />
                                </div>
                            </div>
                            <img class="w-[124px] h-[124px] absolute left-0 top-0" src="{{ asset('public/images/figma/mask-group5.svg') }}" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded border border-transparent px-8 py-4 flex flex-row gap-1 items-center justify-center w-[183px] h-12 mt-6">
                <img class="w-6 h-6" src="{{ asset('public/images/figma/arrow-circle-left0.svg') }}" />
                <span class="text-[#2c6e4a] text-lg font-bold">عرض جميع الأقسام</span>
            </div>
        </section>
        <!-- Books Section -->
        <section class="flex flex-col gap-10 items-end justify-start w-[1170px] mx-auto mt-[100px]">
            <div class="flex flex-row gap-3 items-center justify-start">
                <div class="text-[#2c6e4a] text-right font-bold text-[41px] leading-[60px] flex items-center justify-end">الكتب</div>
                <div class="w-[60px] h-[60px] ml-2">
                    <img src="{{ asset('public/images/figma/group7.svg') }}" />
                </div>
            </div>
            <div class="flex flex-row gap-5 items-start">
                <button class="bg-white rounded border border-[#2c6e4a] px-5 py-2 text-[#2c6e4a] text-base font-bold">الكتب المفتوحة مؤخراً</button>
                <button class="bg-white rounded border border-[#2c6e4a] px-5 py-2 text-[#2c6e4a] text-base font-bold">أكثر الكتب قراءةً</button>
                <button class="bg-white rounded border border-[#2c6e4a] px-5 py-2 text-[#2c6e4a] text-base font-bold">كتب مضافة حديثاً</button>
                <button class="bg-[#2c6e4a] rounded px-5 py-2 text-white text-base font-bold">جميع الكتب</button>
            </div>
            <div class="bg-white rounded border border-[#e8e8e9] flex flex-col gap-0 items-end justify-start w-full overflow-hidden mt-6">
                <div class="flex flex-col gap-0 w-full">
                    <div class="bg-[#f1f8f3] border-b border-[#e8e8e9] flex flex-row gap-0 items-center justify-end w-full">
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">التصنيف</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">اسم الكتاب</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">المؤلف</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">#</div>
                    </div>
                    <!-- Example rows -->
                    @for ($i = 1; $i <= 6; $i++)
                    <div class="border-b border-[#e8e8e9] flex flex-row gap-0 items-center justify-end w-full">
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">الأذكار والأوراد والأدعية</div>
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">أذكار الصباح والمساء</div>
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">عبد الله عزام</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">{{ $i }}</div>
                    </div>
                    @endfor
                </div>
                <div class="flex flex-row items-center justify-between w-full p-4">
                    <div class="flex flex-row gap-2 items-center">
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/chevron-left-filled0.svg') }}" />
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/chevron-right-filled0.svg') }}" />
                    </div>
                    <div class="text-gray-600">5-1 من 100</div>
                    <div class="flex flex-row gap-2 items-center">
                        <span class="text-gray-600">عدد الصفوف في الصفحة:</span>
                        <span class="text-gray-800">10</span>
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/arrow-drop-down-filled0.svg') }}" />
                    </div>
                </div>
            </div>
        </section>
        <!-- Authors Section -->
        <section class="flex flex-col gap-10 items-end justify-start w-[1170px] mx-auto mt-[100px] mb-20">
            <div class="flex flex-row gap-3 items-center justify-start">
                <div class="text-[#2c6e4a] text-right font-bold text-[41px] leading-[60px] flex items-center justify-end">المؤلفين</div>
                <div class="w-[60px] h-[60px] ml-2">
                    <img src="{{ asset('public/images/figma/group8.svg') }}" />
                </div>
            </div>
            <div class="flex flex-row gap-5 items-start">
                <button class="bg-white rounded border border-[#2c6e4a] px-5 py-2 text-[#2c6e4a] text-base font-bold">أكثر المؤلفين قراءةً</button>
                <button class="bg-white rounded border border-[#2c6e4a] px-5 py-2 text-[#2c6e4a] text-base font-bold">مؤلفين جدد</button>
                <button class="bg-[#2c6e4a] rounded px-5 py-2 text-white text-base font-bold">جميع المؤلفين</button>
            </div>
            <div class="bg-white rounded border border-[#e8e8e9] flex flex-col gap-0 items-end justify-start w-full overflow-hidden mt-6">
                <div class="flex flex-col gap-0 w-full">
                    <div class="bg-[#f1f8f3] border-b border-[#e8e8e9] flex flex-row gap-0 items-center justify-end w-full">
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">التصنيف</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">اسم الكتاب</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">المؤلف</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">#</div>
                    </div>
                    @for ($i = 1; $i <= 6; $i++)
                    <div class="border-b border-[#e8e8e9] flex flex-row gap-0 items-center justify-end w-full">
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">الأذكار والأوراد والأدعية</div>
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">أذكار الصباح والمساء</div>
                        <div class="flex-1 p-4 text-gray-800 text-right text-sm">عبد الله عزام</div>
                        <div class="flex-1 p-4 text-[#2c6e4a] text-right font-bold text-sm">{{ $i }}</div>
                    </div>
                    @endfor
                </div>
                <div class="flex flex-row items-center justify-between w-full p-4">
                    <div class="flex flex-row gap-2 items-center">
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/chevron-left-filled1.svg') }}" />
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/chevron-right-filled1.svg') }}" />
                    </div>
                    <div class="text-gray-600">5-1 من 100</div>
                    <div class="flex flex-row gap-2 items-center">
                        <span class="text-gray-600">عدد الصفوف في الصفحة:</span>
                        <span class="text-gray-800">10</span>
                        <img class="w-6 h-6" src="{{ asset('public/images/figma/arrow-drop-down-filled1.svg') }}" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-superduper.main>
