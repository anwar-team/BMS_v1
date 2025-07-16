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
        <div class="bg-white horizontal-line"></div> <!-- bg-white: خلفية بيضاء، horizontal-line: كلاس مخصص للخط -->

        <!-- قسم المعلومات والروابط في الفوتر -->
        <div class="text-white"> <!-- text-white: لون الخط أبيض -->
            <!-- مسافة داخلية رأسية حسب حجم الشاشة -->
            <div class="pt-[2rem] pb-[2rem] lg:pt-10 lg:pb-10"> <!-- py: padding-y، lg:py-20: تخصيص المسافة للشاشات الكبيرة -->
                <!-- حاوية مركزية للمحتوى -->
                 
<div class="container-default">
    <div class="flex justify-center">
        @php
            $brandLogo  = $generalSettings->brand_logo ?? null;
            $brandName  = $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper');
            $footerLogo = $siteSettings->footer_logo   ?? $brandLogo;
        @endphp

        @if ($footerLogo)
            <a href="{{ route('home') }}" class="block">
                <img src="{{ Storage::url($footerLogo) }}"
                     alt="{{ $brandName }}"
                     width="400"   {{-- الحجم الذي استخدمته في الكود الأصلي --}}
                     height="auto" />
            </a>
        @endif
    </div>
</div>
 
                        
                         {{--
                        <div class="flex flex-col gap-y-7">
                            <div class="text-xl font-semibold capitalize">
                                Main
                            </div>
                            @php
                                use Datlechin\FilamentMenuBuilder\Models\Menu;
                                $footerMenu = Menu::location('footer');
                            @endphp
                            <ul class="flex flex-col gap-y-[10px] capitalize">
                                @if($footerMenu)
                                    @foreach($footerMenu->menuItems as $item)
                                        <li>
                                            <a href="{{ $item->url }}" @if($item->target) target="{{ $item->target }}" @endif
                                                class="transition-all duration-300 ease-linear hover:opcity-100 underline-offset-4 opacity-80 hover:underline">
                                                {{ $item->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li>
                                        <a href="{{ route('home') }}"
                                            class="transition-all duration-300 ease-linear hover:opcity-100 underline-offset-4 opacity-80 hover:underline">Home</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        --}}

                         {{--
                        <div class="flex flex-col gap-y-6">
                            <div class="text-xl font-semibold capitalize">
                                Sample Pages
                            </div>
                            @php
                                $footerOthers = Menu::location('footer-2');
                            @endphp
                            <ul class="flex flex-col gap-y-[10px] capitalize">
                                @if($footerOthers)
                                    @foreach($footerOthers->menuItems as $item)
                                        <li>
                                            <a href="{{ $item->url }}" @if($item->target) target="{{ $item->target }}" @endif
                                                class="transition-all duration-300 ease-linear hover:opcity-100 underline-offset-4 opacity-80 hover:underline">
                                                {{ $item->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        --}}
                        {{-- # TODO: Create Menu Module 
                        <div class="flex flex-col gap-y-6">
                            <div class="text-xl font-semibold capitalize">
                                Resources
                            </div>
                            @php
                                $footerOthers = Menu::location('footer-3');
                            @endphp
                            <ul class="flex flex-col gap-y-[10px] capitalize">
                                @if($footerOthers)
                                    @foreach($footerOthers->menuItems as $item)
                                        <li>
                                            <a href="{{ $item->url }}" @if($item->target) target="{{ $item->target }}" @endif
                                                class="transition-all duration-300 ease-linear hover:opcity-100 underline-offset-4 opacity-80 hover:underline">
                                                {{ $item->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        --}}

                        {{-- # TODO: Create Menu Module 
                        <div class="flex flex-col gap-y-6">
                            <div class="text-xl font-semibold capitalize">
                                Community
                            </div>
                            @php
                                $footerOthers = Menu::location('footer-4');
                            @endphp
                            <ul class="flex flex-col gap-y-[10px] capitalize">
                                @if($footerOthers)
                                    @foreach($footerOthers->menuItems as $item)
                                        <li>
                                            <a href="{{ $item->url }}" @if($item->target) target="{{ $item->target }}" @endif
                                                class="transition-all duration-300 ease-linear hover:opcity-100 underline-offset-4 opacity-80 hover:underline">
                                                {{ $item->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        --}}
                    
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
