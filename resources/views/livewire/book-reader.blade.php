<!-- =================== بداية مكون القارئ =================== -->
{{-- Livewire Component: <book-reader> — عنصر جذري واحد فقط --}}
<div id="book-reader" class="relative overflow-x-hidden" >
    <!-- 🖌️ الأنماط الزخرفية (تتحرك مع التمرير لأنها absolute) -->
    <div class="full-bg-patterns pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <!-- النقوش الجانبية -->
        <div class="side-patterns absolute top-20 right-0 flex flex-col gap-10 h-[calc(100%-80px)]">
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-23" src="{{ asset('storage/icon/pattern-ff-18-e-023-20.svg') }}" alt="" />
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-33" src="{{ asset('storage/icon/pattern-ff-18-e-023-30.svg') }}" alt="" />
            <img class="w-[120px] max-w-[180px] min-w-[80px] opacity-43" src="{{ asset('storage/icon/pattern-ff-18-e-023-40.svg') }}" alt="" />
        </div>
        <!-- النقوش المركزية بعرض كامل -->
        <div class="center-patterns absolute top-20 left-1/2 -translate-x-1/2 flex gap-0 opacity-40">
            @foreach (['50','60','70','71','60','70','71'] as $id)
                <img src="{{ asset('storage/icon/pattern-ff-18-e-023-'.$id.'.svg') }}" alt="" class="pattern" />
            @endforeach
        </div>
    </div>

    <!-- 🏗️ المحتوى الرئيسي (فوق النقوش) -->
    <main class="relative z-10 mx-auto w-full max-w-screen-xl px-4 sm:px-6 lg:px-8 pt-32">
        <!-- ️🎯 عنوان + أدوات على طريقة Figma -->
        <section class="flex flex-col gap-6 items-end justify-start text-right mb-16">
            <!-- العنوان + الأيقونة -->
            <div class="flex flex-row gap-3 items-center justify-start flex-wrap">
                <h1 class="font-tajawal font-bold text-[41px] leading-[60px] text-[#2c6e4a] flex flex-wrap">
                    {{ $book->title }}
                    @if($mainAuthors->isNotEmpty())
                        <span class="font-normal mx-1">[{{ $mainAuthors->first()->full_name }}]</span>
                    @endif
                </h1>
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
</div>

@push('styles')
<style>
    /* إعدادات الحجم للنقوش المركزية */
    .center-patterns .pattern{width:18vw;min-width:120px;max-width:340px;height:auto;flex-shrink:0;}
    @media(max-width:900px){.center-patterns .pattern{width:28vw;min-width:80px;max-width:180px;}.side-patterns img{width:80px!important;min-width:50px!important;max-width:100px!important;}}
    @media(max-width:600px){.center-patterns .pattern{width:40vw;min-width:60px;max-width:120px;}.side-patterns img{width:50px!important;min-width:30px!important;max-width:60px!important;}}
</style>
@endpush
<!-- =================== نهاية مكون القارئ =================== -->
