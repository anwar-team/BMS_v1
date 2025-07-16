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
<!--------------------------------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------------------------------->

        <!-- قسم المعلومات والروابط في الفوتر -->
        <div class="text-white"> <!-- text-white: لون الخط أبيض -->
            <!-- مسافة داخلية رأسية حسب حجم الشاشة -->
            <div class="py-[60px] lg:py-20"> <!-- py: padding-y، lg:py-20: تخصيص المسافة للشاشات الكبيرة -->
                <!-- حاوية مركزية للمحتوى -->
                 
                <div class="container-default">
                    <!-- شبكة لتقسيم الفوتر إلى أعمدة متعددة حسب حجم الشاشة -->
                    <div class="grid gap-x-8 gap-y-10 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-[1fr_repeat(4,_auto)] xl:gap-x-10 xxl:gap-x-[134px]">
                        <!-- grid: تفعيل الشبكة، gap-x-8: مسافة أفقية بين الأعمدة، gap-y-10: مسافة رأسية بين الصفوف، sm:grid-cols-2: عمودين للشاشات الصغيرة، md:grid-cols-3: ثلاثة أعمدة للمتوسطة، lg:grid-cols: توزيع مخصص للأعمدة للشاشات الكبيرة، xl و xxl: تخصيص المسافات للأكبر -->
                        <!-- العمود الأول: معلومات العلامة التجارية والوصف ووسائل التواصل -->
                        <div class="flex flex-col gap-y-7 md:col-span-3 lg:col-span-1">
                            <!-- flex flex-col: ترتيب العناصر عموديًا، gap-y-7: مسافة رأسية بين العناصر، md:col-span-3: يمتد على 3 أعمدة في الشاشات المتوسطة، lg:col-span-1: يمتد على عمود واحد في الكبيرة -->
                            <!-- شعار الموقع مع رابط للصفحة الرئيسية -->
                            


<div class="bg-[#1a3a2a] h-[372px] relative overflow-hidden">
  <div
    class="flex flex-col gap-[39px] items-center justify-start w-[1170px] absolute left-[135px] top-10"
  >
    <div
      class="flex flex-col gap-10 items-start justify-start self-stretch shrink-0 relative"
    >
      <div
        class="flex flex-row gap-[175px] items-start justify-center self-stretch shrink-0 relative"
      >
        <div class="shrink-0 w-[164px] h-[207px] relative">
          <a href="{{ route('home') }}">
                                <!-- جلب بيانات الشعار والاسم من الإعدادات العامة أو إعدادات الموقع -->
                                @php
                                    $brandLogo = $generalSettings->brand_logo ?? null; // شعار العلامة التجارية
                                    $brandName = $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper'); // اسم العلامة التجارية
                                    $footerLogo = $siteSettings->footer_logo ?? $brandLogo; // شعار الفوتر
                                @endphp

                                <!-- عرض الشعار إذا كان موجود -->
                                @if($footerLogo)
                                    <img src="{{ Storage::url($footerLogo) }}" alt="{{ $brandName }}" width="400" height="auto" />
                                @endif
                            </a>
        </div>
      </div>
      <div
        class="border-solid border-neutral-dark-6 border-t border-r-[0] border-b-[0] border-l-[0] self-stretch shrink-0 h-0 relative"
        style="
          margin-top: -1px;
          opacity: 0.2;
          transform-origin: 0 0;
          transform: rotate(0deg) scale(1, 1);
        "
      ></div>
    </div>

  </div>
</div>

                            <!-- وصف الموقع والبريد الإلكتروني ووسائل التواصل -->
                            <div>
                                <!-- وصف مختصر للموقع 
                                <div class="lg:max-w-[416px]"> 
                                    {{ $siteSettings->description ?? '' }}
                                </div>
                                -->

                                <!-- رابط البريد الإلكتروني 
                                <a href="mailto:{{ $siteSettings->company_email ?? 'yourdemo@email.com' }}"
                                    class="block my-6 transition-all duration-300 underline-offset-4 hover:underline">
                                    {{ $siteSettings->company_email ?? 'yourdemo@email.com' }}
                                </a>
                                -->
                                <!-- أيقونات وسائل التواصل الاجتماعي -->
                                <div class="flex flex-wrap gap-5"> <!-- flex: ترتيب أفقي، flex-wrap: التفاف العناصر، gap-5: مسافة بين الأيقونات -->
                                    <!-- جلب روابط وأيقونات وسائل التواصل من إعدادات الموقع -->
                                    @php
                                        $socialLinks = [
                                            'facebook' => $siteSocialSettings->facebook_url ?? null, // رابط فيسبوك
                                            'twitter' => $siteSocialSettings->twitter_url ?? null, // رابط تويتر
                                            'instagram' => $siteSocialSettings->instagram_url ?? null, // رابط انستجرام
                                            'linkedin' => $siteSocialSettings->linkedin_url ?? null, // رابط لينكدإن
                                            'youtube' => $siteSocialSettings->youtube_url ?? null, // رابط يوتيوب
                                            'tiktok' => $siteSocialSettings->tiktok_url ?? null, // رابط تيك توك
                                        ];

                                        $faIcons = [
                                            'twitter' => 'fa-brands fa-x-twitter', // أيقونة تويتر
                                            'facebook' => 'fa-brands fa-facebook-f', // أيقونة فيسبوك
                                            'instagram' => 'fa-brands fa-instagram', // أيقونة انستجرام
                                            'linkedin' => 'fa-brands fa-linkedin-in', // أيقونة لينكدإن
                                            'youtube' => 'fa-brands fa-youtube', // أيقونة يوتيوب
                                            'tiktok' => 'fa-brands fa-tiktok', // أيقونة تيك توك
                                        ];
                                    @endphp

                                    @foreach($socialLinks as $platform => $url)
                                        @if(!empty($url))
                                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                                class="flex h-[30px] w-[30px] items-center justify-center rounded-[50%] bg-white bg-opacity-5 text-sm text-white transition-all duration-300 hover:bg-color-pale-gold hover:text-color-denim-darkblue"
                                                aria-label="{{ $platform }}">
                                                <i class="{{ $faIcons[$platform] ?? 'fa-brands fa-'.$platform }}"></i>
                                            </a>
                                        @endif
                                    @endforeach

                                    @if(empty(array_filter($socialLinks)))
                                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer"
                                            class="flex h-[30px] w-[30px] items-center justify-center rounded-[50%] bg-white bg-opacity-5 text-sm text-white transition-all duration-300 hover:bg-color-pale-gold hover:text-color-denim-darkblue"
                                            aria-label="twitter">
                                            <i class="fa-brands fa-x-twitter"></i>
                                        </a>
                                        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"
                                            class="flex h-[30px] w-[30px] items-center justify-center rounded-[50%] bg-white bg-opacity-5 text-sm text-white transition-all duration-300 hover:bg-color-pale-gold hover:text-color-denim-darkblue"
                                            aria-label="facebook">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

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

                        {{-- # TODO: Create Menu Module --}}
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

                        {{-- # TODO: Create Menu Module --}}
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
                    </div>
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
    <div
      class="text-[#ffffff] text-center font-['NotoSansArabic-Regular',_sans-serif] text-sm font-normal relative self-stretch"
      style="letter-spacing: -0.02em"
    >
      © حقوق الطبع والنشر 2025. جميع الحقوق محفوظة.
    </div>