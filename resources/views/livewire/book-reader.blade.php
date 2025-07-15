<!-- =================== بداية مكون القارئ =================== -->
<div>

    <!-- =================== رأس الصفحة/التنقل =================== -->
    <div style="background: #ffffff; position: relative; overflow: hidden;">

        <!-- شريط التنقل العلوي -->
        <div style="background: #ffffff; border-style: solid; border-color: #e8e8e9; border-width: 0px 0px 1px 0px; padding: 16px 135px 16px 135px; display: flex; flex-direction: column; gap: 10px; align-items: flex-start; justify-content: flex-start; width: 100%; position: relative;">
            <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; align-self: stretch; flex-shrink: 0; position: relative; width: 100%;">
                <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; flex-shrink: 0; position: relative; width: 100%;">
                    <!-- روابط التنقل الرئيسية -->
                    <div style="display: flex; flex-direction: row; gap: 24px; align-items: center; justify-content: flex-start; flex-shrink: 0; position: relative;">
                        <div style="color: var(--neutral-dark-1, #0f0f0f); text-align: left; font-family: 'Tajawal-Regular', sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; position: relative;">
                            الكتب
                        </div>
                        <div style="background: var(--neutral-line, #e8e8e9); flex-shrink: 0; width: 1px; height: 24px; position: relative;"></div>
                        <div style="color: var(--neutral-dark-1, #0f0f0f); text-align: left; font-family: 'Tajawal-Regular', sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; position: relative;">
                            الأقسام
                        </div>
                        <div style="background: var(--neutral-line, #e8e8e9); flex-shrink: 0; width: 1px; height: 24px; position: relative;"></div>
                        <div style="color: var(--neutral-dark-1, #0f0f0f); text-align: left; font-family: 'Tajawal-Regular', sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; position: relative;">
                            عن المكتبة
                        </div>
                        <div style="background: var(--neutral-line, #e8e8e9); flex-shrink: 0; width: 1px; height: 24px; position: relative;"></div>
                        <div style="display: flex; flex-direction: column; gap: 0px; align-items: flex-start; justify-content: center; flex-shrink: 0; position: relative;">
                            <div style="color: var(--neutral-dark-1, #0f0f0f); text-align: left; font-family: 'Tajawal-Regular', sans-serif; font-size: 16px; line-height: 24px; font-weight: 400; position: relative;">
                                الرئيسية
                            </div>
                            <div style="margin-top: -2px; border-style: solid; border-color: #2c6e4a; border-width: 2px 0 0 0; align-self: stretch; flex-shrink: 0; height: 0px; position: relative;"></div>
                        </div>
                    </div>

                    <!-- شعارات المكتبة -->
                    <div style="display: flex; flex-direction: row; gap: 8px; align-items: center; justify-content: flex-end; flex-shrink: 0; position: relative;">
                        <img style="flex-shrink: 0; width: 145px; height: 44px; position: relative; object-fit: cover; aspect-ratio: 145/44;" src="{{ asset('storage/icon/untitled-design-7-20.png') }}" alt="Logo 1" />
                        <img style="flex-shrink: 0; width: 44px; height: 44px; position: relative; object-fit: cover; aspect-ratio: 1;" src="{{ asset('storage/icon/untitled-design-8-10.png') }}" alt="Logo 2" />
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== أنماط الخلفية (زخارف) =================== -->
        <main class="relative overflow-x-hidden" dir="rtl">
    <!-- 🖼️ زخارف الخلفية (تتحرك مع التمرير لأنها absolute داخل الـ main) -->
    <div class="absolute inset-0 -z-10 pointer-events-none overflow-hidden">
        <!-- العمود الجانبي للنقوش -->
        <div class="absolute top-20 left-0 flex flex-col gap-10">
            <img class="opacity-25 w-20 sm:w-24 lg:w-40" src="{{ asset('storage/icon/pattern-ff-18-e-023-20.svg') }}" alt="" />
            <img class="opacity-35 w-20 sm:w-24 lg:w-40" src="{{ asset('storage/icon/pattern-ff-18-e-023-30.svg') }}" alt="" />
            <img class="opacity-45 w-20 sm:w-24 lg:w-40" src="{{ asset('storage/icon/pattern-ff-18-e-023-40.svg') }}" alt="" />
        </div>

        <!-- صف أفقي وسط الصفحة -->
        <div class="absolute top-20 left-1/2 -translate-x-1/2 flex opacity-40 whitespace-nowrap">
            @php $svgs = ['50','60','70','71','60','70','71']; @endphp
            @foreach($svgs as $num)
                <img class="w-[18vw] min-w-[120px] max-w-[340px] shrink-0 md:w-[28vw] md:min-w-[80px] md:max-w-[180px] sm:w-[40vw] sm:min-w-[60px] sm:max-w-[120px]" src="{{ asset('storage/icon/pattern-ff-18-e-023-' . $num . '.svg') }}" alt="" />
            @endforeach
        </div>
    </div>


<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

        <!-- =================== المحتوى الرئيسي =================== -->
        <div style="display: flex; flex-direction: column; gap: 24px; align-items: flex-end; justify-content: flex-start; width: 1170px; position: relative; right: 135px; top: 150px; margin: 0 auto; padding-bottom: 100px;">
            <div style="display: flex; flex-direction: column; gap: 40px; align-items: flex-end; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative;">
                <!-- عنوان الكتاب مع الأيقونة -->
                <div style="display: flex; flex-direction: row; gap: 12px; align-items: center; justify-content: flex-start; flex-shrink: 0; position: relative;">
                    <div style="text-align: right; font-family: 'Tajawal-Bold', sans-serif; font-size: 41px; line-height: 60px; font-weight: 700; position: relative; display: flex; align-items: center; justify-content: flex-end;">
                        <span>
                            <span>{{ $book->title }}</span>
                            @if($mainAuthors->count() > 0)
                                <span>[</span>
                                <span>{{ $mainAuthors->first()->full_name }}</span>
                                <span>]</span>
                            @endif
                        </span>
                    </div>
                    <img style="flex-shrink: 0; width: 60px; height: 60px; position: relative; overflow: visible;" src="{{ asset('storage/icon/group0.svg') }}" alt="Book Icon" />
                </div>

                <!-- أدوات البحث وأدوات أخرى -->
                <div style="display: flex; flex-direction: column; gap: 16px; align-items: flex-start; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative;">
                    <div style="display: flex; flex-direction: row; gap: 24px; align-items: center; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative;">
                        <!-- أدوات (إشارات مرجعية، مشاركة، تحميل) -->
                        <div style="display: flex; flex-direction: row; gap: 24px; align-items: flex-start; justify-content: flex-start; flex-shrink: 0; position: relative;">
                            <img style="flex-shrink: 0; width: 20.89px; height: 24px; position: relative; overflow: visible;" src="{{ asset('storage/icon/group1.svg') }}" alt="Bookmark" />
                            <div style="flex-shrink: 0; width: 24px; height: 24px; position: relative; overflow: hidden;">
                                <img style="width: 83.33%; height: 83.33%; position: absolute; right: 8.33%; left: 8.33%; bottom: 8.33%; top: 8.33%; overflow: visible;" src="{{ asset('storage/icon/group2.svg') }}" alt="Share" />
                            </div>
                            <div style="flex-shrink: 0; width: 24px; height: 24px; position: relative; overflow: hidden;">
                                <img style="width: 100%; height: 75%; position: absolute; right: 0%; left: 0%; bottom: 12.5%; top: 12.5%; overflow: visible;" src="{{ asset('storage/icon/group3.svg') }}" alt="Download" />
                            </div>
                        </div>

                        <!-- مربع البحث -->
                        <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end; justify-content: flex-start; flex: 1; height: 42px; position: relative;">
                            <div style="background: #ffffff; border-radius: 6px; border-style: solid; border-color: #d9d9d9; border-width: 1px; align-self: stretch; flex-shrink: 0; height: 42px; position: relative;"></div>
                            <div style="display: flex; flex-direction: row; gap: 8px; align-items: flex-end; justify-content: flex-end; flex-shrink: 0; position: absolute; right: 12.11px; top: 50%; translate: 0 -50%;">
                                <div style="color: var(--neutral-dark-4, #3a3b3e); text-align: left; font-family: 'Tajawal-Regular', sans-serif; font-size: 14px; line-height: 20px; font-weight: 400; position: relative; display: flex; align-items: center; justify-content: flex-start;">
                                    ابحث ...
                                </div>
                                <div style="flex-shrink: 0; width: 24px; height: 24px; position: relative; overflow: hidden;">
                                    <img style="height: auto; position: absolute; left: 2.78px; top: 2.78px; overflow: visible;" src="{{ asset('storage/icon/iconly-light-search0.svg') }}" alt="Search" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- منطقة المحتوى مع الشريط الجانبي -->
                    <div style="display: flex; flex-direction: row; gap: 12px; align-items: flex-start; justify-content: flex-end; flex-shrink: 0; position: relative; width: 100%;">
                        <!-- منطقة المحتوى الرئيسية -->
                        <div style="display: flex; flex-direction: column; gap: 30px; align-items: flex-start; justify-content: flex-start; flex-shrink: 0; width: 774px; position: relative;">
                            <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative;">
                                <!-- لوحة محتوى الكتاب -->
                                <div style="background: var(--background-paper-elevation-1, #ffffff); border-radius: 4px; border-style: solid; border-color: #e8e8e9; border-width: 1px 1px 0px 1px; display: flex; flex-direction: column; gap: 0px; align-items: flex-end; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative; box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2), 0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12); overflow: hidden;">
                                    <div style="display: flex; flex-direction: column; gap: 0px; align-items: flex-start; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative;">
                                        <div style="display: flex; flex-direction: row; gap: 0px; align-items: center; justify-content: flex-end; align-self: stretch; flex-shrink: 0; position: relative;">
                                            <div style="background: var(--library-clickablelayer, rgba(0, 0, 0, 0)); padding: 16px; display: flex; flex-direction: row; gap: 0px; align-items: center; justify-content: flex-end; flex: 1; position: relative;">
                                                <div style="display: flex; flex-direction: column; gap: 0px; align-items: flex-end; justify-content: flex-start; flex: 1; position: relative;">
                                                    <div style="display: flex; flex-direction: column; gap: 0px; align-items: flex-start; justify-content: flex-start; flex-shrink: 0; position: relative;">
                                                        <!-- عنوان الفصل ومحتوى الصفحة -->
                                                        <div style="color: var(--text-primary, rgba(0, 0, 0, 0.87)); text-align: right; font-family: 'Tajawal-Regular', sans-serif; font-size: 16px; line-height: 143%; letter-spacing: 0.17px; font-weight: var(--fontweightregular, 400); position: relative; width: 100%;">
                                                            @if($activeChapter)
                                                                <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">{{ $activeChapter->title }}</h2>
                                                            @endif
                                                            
                                                            @if($activePage)
                                                                <div class="rtl font-naskh leading-relaxed text-right">
                                                                    {!! $activePage->content !!}
                                                                </div>
                                                            @else
                                                                <div class="text-center py-12">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                    </svg>
                                                                    <p class="mt-4 text-gray-500">لم يتم العثور على محتوى لهذا الفصل</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- أزرار التنقل بين الصفحات -->
                                        @if($activePage)
                                        <div style="padding: 16px; display: flex; flex-direction: row; gap: 16px; align-items: center; justify-content: space-between; align-self: stretch; flex-shrink: 0; position: relative; width: 100%; border-top: 1px solid #e8e8e9;">
                                            <button wire:click="previousPage" style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #2c6e4a;">
                                                <img src="{{ asset('storage/icon/chevron-left-filled0.svg') }}" alt="Previous" />
                                                <span>الصفحة السابقة</span>
                                            </button>
                                            
                                            <div style="color: #666; font-size: 14px;">
                                                صفحة {{ $activePage->page_number }} من {{ $book->pages_count }}
                                            </div>
                                            
                                            <button wire:click="nextPage" style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #2c6e4a;">
                                                <span>الصفحة التالية</span>
                                                <img src="{{ asset('storage/icon/chevron-right-filled0.svg') }}" alt="Next" />
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- الشريط الجانبي: فهرس الفصول -->
                        <div style="display: flex; flex-direction: column; gap: 12px; align-items: flex-end; justify-content: flex-start; flex-shrink: 0; width: 384px; position: relative;">
                            <div style="background: var(--background-paper-elevation-1, #ffffff); border-radius: 4px; border-style: solid; border-color: #e8e8e9; border-width: 1px; padding: 16px; display: flex; flex-direction: column; gap: 12px; align-items: flex-start; justify-content: flex-start; align-self: stretch; flex-shrink: 0; position: relative; box-shadow: 0px 2px 1px -1px rgba(0, 0, 0, 0.2), 0px 1px 1px 0px rgba(0, 0, 0, 0.14), 0px 1px 3px 0px rgba(0, 0, 0, 0.12);">
                                <div style="color: var(--text-primary, rgba(0, 0, 0, 0.87)); text-align: right; font-family: 'Tajawal-Bold', sans-serif; font-size: 16px; line-height: 150%; font-weight: 700; position: relative; align-self: stretch;">
                                    فهرس الكتاب
                                </div>
                                
                                <div style="height: 1px; background: #e0e0e0; width: 100%; margin: 4px 0;"></div>
                                
                                <div style="max-height: 500px; overflow-y: auto; width: 100%;">
                                    @foreach($chapters as $chapter)
                                        <div style="margin-bottom: 8px;">
                                            <!-- الفصل الرئيسي -->
                                            <div 
                                                wire:click="selectChapter({{ $chapter->id }})"
                                                style="display: flex; justify-content: space-between; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px; {{ $activeChapter && $activeChapter->id === $chapter->id ? 'background-color: #f1f8f3; color: #2c6e4a;' : 'background-color: transparent;' }}"
                                            >
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span style="font-size: 14px;">{{ $chapter->title }}</span>
                                                </div>
                                                
                                                @if($chapter->children->count() > 0)
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="{{ $activeChapter && $activeChapter->id === $chapter->id ? 'transform: rotate(90deg);' : '' }}">
                                                        <path d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            
                                            <!-- الفصول الفرعية -->
                                            @if($chapter->children->count() > 0 && $activeChapter && ($activeChapter->id === $chapter->id || $activeChapter->parent_id === $chapter->id))
                                                <div style="margin-right: 16px; padding-right: 8px; border-right: 2px solid #e0e0e0;">
                                                    @foreach($chapter->children as $subChapter)
                                                        <div 
                                                            wire:click="selectChapter({{ $subChapter->id }})"
                                                            style="display: flex; align-items: center; padding: 6px; cursor: pointer; border-radius: 4px; margin-top: 4px; {{ $activeChapter && $activeChapter->id === $subChapter->id ? 'background-color: #f1f8f3; color: #2c6e4a;' : 'background-color: transparent;' }}"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M9 5l7 7-7 7"></path>
                                                            </svg>
                                                            <span style="font-size: 12px; margin-right: 8px;">{{ $subChapter->title }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================== تذييل الصفحة =================== -->
        <div style="width: 100%; margin-top: 80px; position: relative; clear: both; padding-top: 100px;">
            <footer class="footer" style="background-color: #f8f9fa; padding: 30px 0; text-align: center; border-top: 1px solid #e8e8e9; width: 100%;">
                <div class="footer-content" style="max-width: 1170px; margin: 0 auto; padding: 0 20px;">
                    <div class="flex justify-center">
                        <div class="footer-logo" style="width: 120px; height: 120px; margin: 0 auto 20px auto; overflow: hidden; border-radius: 50%;">
                            <img src="{{ asset('images/figma/logo.jpg') }}" alt="Logo" class="w-full h-full" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>
                    <div class="footer-divider" style="height: 1px; background-color: #e8e8e9; margin: 20px 0;"></div>
                    <div class="footer-copyright" style="font-family: 'Tajawal', sans-serif; color: #666; font-size: 14px;">
                        © حقوق الطبع والنشر {{ date('Y') }}. جميع الحقوق محفوظة.
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- =================== أنماط CSS مخصصة =================== -->
    <style>
        /* شريط تمرير مخصص للمتصفحات التي تدعم webkit */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }
        
        /* تعديلات خاصة بالاتجاه من اليمين لليسار */
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        /* الخطوط */
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
        }
        
        .font-tajawal {
            font-family: 'Tajawal', sans-serif;
        }
        
        .font-naskh {
            font-family: 'Noto Naskh Arabic', serif;
        }
        
        /* أنماط خاصة بالتذييل */
        .footer {
            clear: both;
            position: relative;
            z-index: 10;
        }
    </style>
</div>
</main>
<!-- =================== نهاية مكون القارئ =================== --> 