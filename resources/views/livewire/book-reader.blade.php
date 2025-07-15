{{-- Livewire Component: <book-reader> — عنصر جذري واحد فقط --}}
<div id="book-reader" class="relative overflow-x-hidden" >
    <!-- =================== رأس الصفحة/التنقل =================== -->
    <header style="background:#ffffff;position:relative;overflow:hidden;">
        <!-- شريط التنقل العلوي -->
        <div style="background:#ffffff;border-style:solid;border-color:#e8e8e9;border-width:0 0 1px 0;padding:16px 135px;display:flex;flex-direction:column;gap:10px;width:100%;">
            <div style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;">
                <div style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;width:100%;">
                    <!-- روابط التنقل الرئيسية -->
                    <nav style="display:flex;flex-direction:row;gap:24px;align-items:center;">
                        <span style="color:#0f0f0f;font-family:'Tajawal-Regular',sans-serif;font-size:16px;line-height:24px;font-weight:400;">الكتب</span>
                        <span style="background:#e8e8e9;width:1px;height:24px;"></span>
                        <span style="color:#0f0f0f;font-family:'Tajawal-Regular',sans-serif;font-size:16px;line-height:24px;font-weight:400;">الأقسام</span>
                        <span style="background:#e8e8e9;width:1px;height:24px;"></span>
                        <span style="color:#0f0f0f;font-family:'Tajawal-Regular',sans-serif;font-size:16px;line-height:24px;font-weight:400;">عن المكتبة</span>
                        <span style="background:#e8e8e9;width:1px;height:24px;"></span>
                        <div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;">
                            <span style="color:#0f0f0f;font-family:'Tajawal-Regular',sans-serif;font-size:16px;line-height:24px;font-weight:400;">الرئيسية</span>
                            <span style="margin-top:-2px;border-top:2px solid #2c6e4a;width:100%;"></span>
                        </div>
                    </nav>
                    <!-- شعارات المكتبة -->
                    <div style="display:flex;flex-direction:row;gap:8px;align-items:center;justify-content:flex-end;">
                        <img style="width:145px;height:44px;object-fit:cover;" src="{{ asset('storage/icon/untitled-design-7-20.png') }}" alt="Logo 1" />
                        <img style="width:44px;height:44px;object-fit:cover;" src="{{ asset('storage/icon/untitled-design-8-10.png') }}" alt="Logo 2" />
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- 🖌️ الأنماط الزخرفية (تتحرك مع التمرير لأنها absolute) -->
    <div class="full-bg-patterns pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <!-- النقوش الجانبية -->
        <div class="side-patterns absolute top-20 right-0 flex flex-col gap-10 h-[calc(100%-80px)]">
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-23" src="{{ asset('storage/icon/pattern-ff-18-e-023-20.svg') }}" alt="" />
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-33" src="{{ asset('storage/icon/pattern-ff-18-e-023-30.svg') }}" alt="" />
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-43" src="{{ asset('storage/icon/pattern-ff-18-e-023-40.svg') }}" alt="" />
        </div>

        <!-- النقوش المركزية بعرض كامل -->
        <div class="center-patterns absolute top-20 left-1/2 -translate-x-1/2 flex gap-0 opacity-30 bg-neutral-200">
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-50.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-60.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-70.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-71.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-60.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-70.svg') }}" alt="" class="pattern" />
            <img src="{{ asset('storage/icon/pattern-ff-18-e-023-71.svg') }}" alt="" class="pattern" />
        </div>
    </div>

    <!-- 🏗️ المحتوى الرئيسي (فوق النقوش) -->
    <main class="relative z-10 mx-auto w-full max-w-screen-xl px-4 sm:px-6 lg:px-8 pt-32">

         <!-- ️🎯 عنوان + أدوات على طريقة Figma -->
         <section class="flex flex-col gap-6 items-end justify-start text-right mb-16">
            <!-- العنوان + الأيقونة -->
            <div class="flex flex-row gap-3 items-center justify-start flex-wrap">
            <div class="text-center justify-center">

                <span class="text-green-700 text-4xl font-bold font-['Tajawal'] leading-[60px]"> 
                {{ $book->title }}
                @if($mainAuthors->isNotEmpty())
                <span class="font-normal mx-1">[{{ $mainAuthors->first()->full_name }}]</span>
                @endif
                </span>
                
                </div>
                <img src="{{ asset('storage/icon/group0.svg') }}" alt="Book Icon" class="w-[60px] h-[60px] shrink-0" />
            </div>

            <!-- شريط الأدوات + البحث -->
            <div class="flex flex-row gap-6 items-center justify-start w-full max-w-3xl flex-wrap">
                <!-- الأدوات -->
                <div class="flex flex-row gap-6 items-start">
                    <img src="{{ asset('storage/icon/group1.svg') }}" alt="Bookmark" class="w-[20.89px] h-6" />
                    <img src="{{ asset('storage/icon/group2.svg') }}" alt="Share" class="w-6 h-6" />
                    <img src="{{ asset('storage/icon/group3.svg') }}" alt="Download" class="w-6 h-6" />
                </div>

                <!-- مربع البحث -->
                <div class="relative flex-1 h-[42px]">
                    <input type="text" placeholder="ابحث ..." class="w-full h-full rounded-md border border-[#d9d9d9] pr-12 pl-4 text-right font-tajawal focus:ring-2 focus:ring-[#2c6e4a]" />
                    <img src="{{ asset('storage/icon/iconly-light-search0.svg') }}" class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6" alt="Search" />
                </div>
            </div>
        </section>

        <!-- 👇 باقي المحتوى يوضع هنا (الفهرس + الصفحات ...) -->
        {{ $slot ?? '' }}
    </main>

<!---------footer---------------------->

<footer class="footer" style="background-color: #1A3A2A;">
    <div class="footer-content">
        <div class="flex justify-center">

            <div class="footer-logo">
                <img src="{{ asset('storage/icon/logo-01.png') }}" alt="Logo" class="w-full h-full  ">
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-copyright">
            © حقوق الطبع والنشر {{ date('Y') }}. جميع الحقوق محفوظة.
        </div>
    </div>
</footer>

<!------------------------------------>
</div>

@push('styles')
<style>
    /* إعدادات الحجم للنقوش المركزية */
    .center-patterns .pattern {
        width: 18vw;
        min-width: 120px;
        max-width: 340px;
        height: auto;
        flex-shrink: 0;
    }
    @media (max-width: 900px) {
        .center-patterns .pattern {
            width: 28vw;
            min-width: 80px;
            max-width: 180px;
        }
        .side-patterns img {
            width: 80px !important;
            min-width: 50px !important;
            max-width: 100px !important;
        }
    }
    @media (max-width: 600px) {
        .center-patterns .pattern {
            width: 40vw;
            min-width: 60px;
            max-width: 120px;
        }
        .side-patterns img {
            width: 50px !important;
            min-width: 30px !important;
            max-width: 60px !important;
        }
    }
</style>
@endpush
