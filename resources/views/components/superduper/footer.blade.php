<!-- بداية الفوتر: عنصر رئيسي للفوتر الخاص بالموقع -->
<footer class="section-footer"> <!-- section-footer: كلاس مخصص للفوتر -->
    <!-- خلفية الفوتر بلون أزرق غامق -->
    <div class="bg-color-denim-darkblue"> <!-- bg-color-denim-darkblue: كلاس مخصص للون الخلفية -->
        <!-- ضبط العنصر ليكون فوق العناصر الأخرى باستخدام z-index -->


        {{--  <!-- الجزء العلوي من ال footer الاصلي-->

            <div class="relative z-10"> <!-- relative: لتحديد موضع العنصر بشكل نسبي، z-10: لجعل العنصر فوق العناصر الأخرى -->
                <!-- مسافات داخلية للفوتر (padding) حسب حجم الشاشة -->
                <div class="pb-[60px] pt-20 lg:pb-20 lg:pt-[100px] xl:pt-[120px]"> <!-- pb: padding-bottom، pt: padding-top، lg و xl: تخصيص المسافات للشاشات الكبيرة -->
                <!-- حاوية مركزية للمحتوى -->
                <div class="container-default"> <!-- container-default: كلاس مخصص لتوسيط وتحديد عرض الحاوية -->
                    <!-- استخدام Flexbox لترتيب العناصر عموديًا وتوسيطها -->
                    <div class="flex flex-col items-center justify-center gap-16"> <!-- flex: تفعيل الفلكس، flex-col: ترتيب عمودي، items-center: توسيط أفقي، justify-center: توسيط عمودي، gap-16: مسافة بين العناصر -->
                    <!-- تحديد أقصى عرض للنص الرئيسي -->
                    <div class="max-w-[720px]"> <!-- max-w-[720px]: أقصى عرض 720 بكسل -->
                        <!-- عنوان رئيسي للفوتر مع تنسيقات Tailwind -->
                        <h2 class="text-3xl font-medium leading-loose text-center text-gray-100 lg:text-5xl xl:text-4xl">
                        <!-- text-3xl: حجم الخط كبير، font-medium: وزن الخط متوسط، leading-loose: تباعد الأسطر، text-center: محاذاة وسط، text-gray-100: لون الخط رمادي فاتح، lg:text-5xl و xl:text-4xl: تغيير حجم الخط للشاشات الكبيرة -->
                        Feel proud of everything you <br/> 
                        <!-- إبراز كلمة Start و SuperDuper بلون وحجم مميز -->
                        <span class="text-5xl font-bold text-secondary-600">Start</span> with <span class="text-5xl font-bold text-secondary-600">SuperDuper</span> <!-- text-5xl: حجم خط أكبر، font-bold: خط عريض، text-secondary-600: لون مخصص -->
                        </h2>
                    </div>
                    <!-- زر دعوة لاتخاذ إجراء (CTA) -->
                    <a href="{{ $siteSettings->footer_cta_button_url ?? '#' }}"
                        class="inline-block border border-gray-900 btn bg-secondary-700">
                        <!-- inline-block: عرض الزر بشكل كتلة، border: إضافة حدود، border-gray-900: لون الحدود رمادي غامق، btn: كلاس مخصص للأزرار، bg-secondary-700: لون خلفية مخصص -->
                        <span>Get started— it's free</span>
                    </a>
                    </div>
                </div>
                </div>
            </div>
        --}}



        <!-- خط أفقي أبيض للفصل بين الأقسام -->
        <div class="bg-[#1a3a2a] h-[372px] relative overflow-hidden flex items-center justify-center">
            <div class="flex flex-col gap-[39px] items-center justify-start w-full max-w-[1170px] absolute left-1/2 top-10 -translate-x-1/2">
            <div class="flex flex-col gap-10 items-start justify-start w-full relative">
                <div class="flex flex-row gap-[175px] items-start justify-center w-full relative">
                <div class="shrink-0 w-[164px] h-[207px] relative flex items-center justify-center">
                    @php
                    $brandLogo = $generalSettings->brand_logo ?? null;
                    $brandName = $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper');
                    $footerLogo = $siteSettings->footer_logo ?? $brandLogo;
                    @endphp
                    @if($footerLogo)
                    <img class="h-auto absolute left-0 top-0 overflow-visible" src="{{ Storage::url($footerLogo) }}" alt="{{ $brandName }}" width="164" height="207" />
                    @endif
                </div>
                </div>
                <div class="border-t border-neutral-dark-6 self-stretch h-0 relative" style="margin-top: -1px; opacity: 0.2;"></div>
            </div>
            <div class="text-[#ffffff] text-center font-['NotoSansArabic-Regular',_sans-serif] text-sm font-normal relative w-full" style="letter-spacing: -0.02em">
                &copy; حقوق الطبع والنشر {{ date('Y') }}. جميع الحقوق محفوظة.
            </div>
            </div>
        </div>

        <div class="bg-white bg-opacity-5">
            <div class="py-[18px]">
                <div class="container-default">
                    <div class="text-center text-white text-opacity-80">
                        &copy; Copyright {{ date('Y') }}, {{ $siteSettings->copyright_text ?? 'All Rights Reserved' }}
                        {{ $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
