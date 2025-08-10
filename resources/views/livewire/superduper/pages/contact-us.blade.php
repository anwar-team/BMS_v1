<div x-data x-init="
    $nextTick(() => {
        window.addEventListener('successMessageShown', () => {
            setTimeout(() => {
                $wire.set('success', false);
            }, 5000);
        });
    })
">
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">
            <!-- يمكنك وضع أنماط الخلفية هنا إذا أردت -->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!-- نهاية أنماط الخلفية -->

                <!-- هذا هو المكان الذي تضع فيه محتواك الرئيسي -->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    
                    <!-- قسم رسالتنـا -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">رسالتنـا</h2>
                        </div>
                    </div>

                    <!-- Section: رسالتنـا -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8 mb-16">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Card 1 -->
                            <div class="bg-white p-6 border border-gray-200 rounded-lg">
                                <div class="flex justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-primary-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-primary-800 text-center">إثراء المحتوى العربي والاسلامي</h4>
                                <p class="mt-2 text-gray-600 text-center">
                                    نُقدّم محتوى عربي إسلامي غني ومتنوع يلبي احتياجات الباحثين والمُهتمين بالثقافة والتراث الإسلامي.
                                </p>
                            </div>

                            <!-- Card 2 -->
                            <div class="bg-white p-6 border border-gray-200 rounded-lg">
                                <div class="flex justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-primary-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-primary-800 text-center">توفير محتوى ديني موثوق</h4>
                                <p class="mt-2 text-gray-600 text-center">
                                    نحرص على تقديم محتوى ديني مدقق ومُراجَع من مصادر موثوقة مثل الكتب المُعتبرة وأقوال العلماء الأجلاء.
                                </p>
                            </div>

                            <!-- Card 3 -->
                            <div class="bg-white p-6 border border-gray-200 rounded-lg">
                                <div class="flex justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-primary-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-semibold text-primary-800 text-center">نشر المعرفة الإسلامية بأسلوب حديث</h4>
                                <p class="mt-2 text-gray-600 text-center">
                                    نستخدم أساليب تكنولوجية مبتكرة لجعل المعرفة الإسلامية سهلة الفهم والوصول عبر منصات رقمية متنوعة.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- نهاية قسم رسالتنـا -->
                    <!-- قسم تواصل معنا -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <h2 class="text-4xl text-green-800 font-bold">تواصل معنا</h2>
                        </div>
                    </div>

                    <!-- محتوى صفحتك الفعلي يذهب هنا. على سبيل المثال، جدول، بطاقات، نصوص، إلخ. -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                        <!-- محتوى الـ div هذا سيتم توسيطه وتحديد عرضه بواسطة الـ section الأبوية -->
    <div class="section-contact-info">
        <!-- Section Space -->
        <div class="py-12 md:py-16 lg:py-20">
            <!-- Section Container -->
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="grid items-start grid-cols-1 gap-16 lg:grid-cols-2 lg:gap-20 xl:gap-24">
                    <!-- Contact Info List - Left Column -->
                    <div class="flex flex-col">
                        <!-- Section Title -->
                        <div class="mb-4 md:mb-16">
                            <h2 class="mb-3 text-3xl font-bold text-primary-800">تواصل معنا</h2>
                            <p class="max-w-xl text-gray-600">
                                هل لديك أسئلة حول خدماتنا أو تحتاج إلى مساعدة؟ نحن هنا لمساعدتك!
                                تواصل معنا من خلال أي من القنوات أدناه.
                            </p>
                        </div>

                        <!-- Contact Cards -->
                        <div class="space-y-4">
                            <!-- Chat with us -->
                            <div class="flex items-start py-4 transition-all duration-300 bg-white">
                                <div class="mr-4 shrink-0">
                                    <div class="p-2 text-primary-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="ml-2 text-xl font-semibold text-primary-800">
                                        تحدث معنا
                                    </h3>
                                    <p class="mt-1 ml-4 text-gray-600">
                                        نحن في انتظارك لمساعدتك كل يوم من الاثنين إلى الجمعة من 9 صباحاً
                                        إلى 5 مساءً {{ $siteSettings->timezone ?? 'UTC' }}.
                                    </p>
                                </div>
                            </div>

                            <!-- Give us a call -->
                            <div class="flex items-start py-4 transition-all duration-300 bg-white">
                                <div class="mr-4 shrink-0">
                                    <div class="p-2 text-primary-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="ml-2 text-xl font-semibold text-primary-800">
                                        اتصل بنا
                                    </h3>
                                    <p class="mt-1 ml-4 text-gray-600">
                                        اتصل بنا على
                                        <a href="tel:{{ $siteSettings->company_phone ?? '+1234567890' }}"
                                           class="font-semibold transition-colors text-primary-600 hover:text-primary-800">
                                            {{ $siteSettings->company_phone ?? '+1234567890' }}
                                        </a>.
                                        كل يوم من الاثنين إلى الجمعة من 9 صباحاً إلى 5 مساءً.
                                    </p>
                                </div>
                            </div>

                            <!-- Email Us -->
                            <div class="flex items-start py-4 transition-all duration-300 bg-white">
                                <div class="mr-4 shrink-0">
                                    <div class="p-2 text-primary-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="ml-2 text-xl font-semibold text-primary-800">
                                        راسلنا
                                    </h3>
                                    <p class="mt-1 ml-4 text-gray-600">
                                        أرسل لنا بريداً إلكترونياً على
                                        <a href="mailto:{{ $siteSettings->company_email ?? 'contact@starter-kit.com' }}"
                                           class="font-semibold underline text-primary-600 hover:text-primary-800 underline-offset-4">
                                            {{ $siteSettings->company_email ?? 'contact@starter-kit.com' }}
                                        </a>
                                        وستحصل على رد خلال 24 ساعة.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Block - Right Column -->
                    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-md md:p-8">
                        @if($success)
                            <div class="p-6 mb-4 border border-green-200 rounded-lg bg-green-50">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 rounded-full shrink-0 bg-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <h3 class="ml-3 text-xl font-semibold text-green-800">تم إرسال الرسالة بنجاح!</h3>
                                </div>
                                <p class="mb-5 text-green-700">
                                    شكراً لتواصلك معنا. لقد استلمنا رسالتك وسنرد عليك قريباً.
                                </p>
                                <button wire:click="resetForm"
                                    class="inline-flex items-center px-5 py-3 text-white transition-colors rounded-lg bg-success hover:bg-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    إرسال رسالة أخرى
                                </button>
                            </div>
                        @else
                            <div class="mb-6">
                                <h2 class="mb-2 text-2xl font-bold text-primary-800">
                                    أرسل لنا رسالة
                                </h2>
                                <p class="text-gray-600">
                                    املأ النموذج أدناه وسنرد عليك في أقرب وقت ممكن.
                                </p>
                            </div>

                            <form wire:submit.prevent="submit" class="flex flex-col gap-4">
                                <!-- First name and Last name -->
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="firstname" class="block mb-1 text-sm font-medium text-gray-700">الاسم الأول</label>
                                        <input type="text" wire:model.blur="firstname" id="firstname"
                                            placeholder="أحمد"
                                            class="w-full px-4 py-2 bg-background-light rounded-md placeholder:text-gray-400 focus:outline-none transition-all
                                            @error('firstname')
                                                border border-error focus:border-error focus:ring-1 focus:ring-error/20
                                            @else
                                                border border-gray-300 focus:border-primary-600 focus:ring-1 focus:ring-primary-600
                                            @enderror" />
                                        @error('firstname')
                                            <span class="block mt-1 text-sm text-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="lastname" class="block mb-1 text-sm font-medium text-gray-700">اسم العائلة</label>
                                        <input type="text" wire:model.blur="lastname" id="lastname"
                                            placeholder="محمد"
                                            class="w-full px-4 py-2 bg-background-light rounded-md placeholder:text-gray-400 focus:outline-none transition-all
                                            @error('lastname')
                                                border border-error focus:border-error focus:ring-1 focus:ring-error/20
                                            @else
                                                border border-gray-300 focus:border-primary-600 focus:ring-1 focus:ring-primary-600
                                            @enderror" />
                                        @error('lastname')
                                            <span class="block mt-1 text-sm text-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Email address -->
                                <div>
                                    <label for="email" class="block mb-1 text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                                    <input type="email" wire:model.blur="email" id="email"
                                        placeholder="your@email.com"
                                        class="w-full px-4 py-2 bg-background-light rounded-md placeholder:text-gray-400 focus:outline-none transition-all
                                        @error('email')
                                            border border-error focus:border-error focus:ring-1 focus:ring-error/20
                                        @else
                                            border border-gray-300 focus:border-primary-600 focus:ring-1 focus:ring-primary-600
                                        @enderror" />
                                    @error('email')
                                        <span class="block mt-1 text-sm text-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone number -->
                                <div>
                                    <label for="phone" class="block mb-1 text-sm font-medium text-gray-700">رقم الهاتف (اختياري)</label>
                                    <input type="tel" wire:model.blur="phone" id="phone"
                                        placeholder="+966 50 123 4567"
                                        class="w-full px-4 py-2 transition-all border border-gray-300 rounded-md bg-background-light placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:border-primary-600 focus:ring-primary-600" />
                                </div>

                                <!-- Company information -->
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="company" class="block mb-1 text-sm font-medium text-gray-700">المؤسسة (اختياري)</label>
                                        <input type="text" wire:model.blur="company" id="company"
                                            placeholder="اسم مؤسستك"
                                            class="w-full px-4 py-2 transition-all border border-gray-300 rounded-md bg-background-light placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:border-primary-600 focus:ring-primary-600" />
                                    </div>
                                    <div>
                                        <label for="employees" class="block mb-1 text-sm font-medium text-gray-700">حجم المؤسسة</label>
                                        <select wire:model.blur="employees" id="employees"
                                            class="w-full px-4 py-2 text-gray-700 transition-all border border-gray-300 rounded-md bg-background-light focus:outline-none focus:ring-1 focus:border-primary-600 focus:ring-primary-600">
                                            <option value="">اختر حجم المؤسسة</option>
                                            @foreach($employeeOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div>
                                    <label for="subject" class="block mb-1 text-sm font-medium text-gray-700">الموضوع</label>
                                    <input type="text" wire:model.blur="subject" id="subject"
                                        placeholder="ما موضوع رسالتك؟"
                                        class="w-full px-4 py-2 bg-background-light rounded-md placeholder:text-gray-400 focus:outline-none transition-all
                                        @error('subject')
                                            border border-error focus:border-error focus:ring-1 focus:ring-error/20
                                        @else
                                            border border-gray-300 focus:border-primary-600 focus:ring-1 focus:ring-primary-600
                                        @enderror" />
                                    @error('subject')
                                        <span class="block mt-1 text-sm text-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Message -->
                                <div>
                                    <label for="message" class="block mb-1 text-sm font-medium text-gray-700">الرسالة</label>
                                    <textarea wire:model.blur="message" id="message"
                                        placeholder="اكتب رسالتك هنا..."
                                        rows="5"
                                        class="w-full px-4 py-2 bg-background-light rounded-md placeholder:text-gray-400 focus:outline-none transition-all
                                        @error('message')
                                            border border-error focus:border-error focus:ring-1 focus:ring-error/20
                                        @else
                                            border border-gray-300 focus:border-primary-600 focus:ring-1 focus:ring-primary-600
                                        @enderror"></textarea>
                                    @error('message')
                                        <span class="block mt-1 text-sm text-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Submit button -->
                                <div class="pt-2">
                                    <button type="submit"
                                        class="relative w-full px-6 py-3 font-medium text-white transition-colors duration-200 rounded-md bg-primary-800 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="cursor-wait opacity-90"
                                        wire:target="submit">

                                        <!-- Normal state -->
                                        <span wire:loading.remove wire:target="submit">
                                            إرسال الرسالة
                                        </span>

                                        <!-- Loading state -->
                                        <span wire:loading wire:target="submit" class="inline-flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                <div aria-hidden="true" style="position:absolute; left:-9999px;"><input type="text" wire:model="company_website" name="company_website" tabindex="-1" autocomplete="off"></div>
                            </form>

                            @if(session('error'))
                                <div class="flex items-start p-4 mt-5 text-red-700 border border-red-200 rounded-md bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-error mr-3 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
                    </div>
                    </div> <!-- إغلاق div الـ pattern لقسم تواصل معنا -->
                </section>
            </div>
        </main>
    </div>
</div>