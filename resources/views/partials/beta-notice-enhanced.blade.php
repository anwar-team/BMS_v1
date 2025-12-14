{{-- إشعار إصدار تجريبي مُحسن مع إحصائيات --}}
<div id="enhancedBetaNotice" class="bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-700 text-white shadow-xl relative overflow-hidden border-b-4 border-yellow-400" dir="rtl">
    {{-- تأثير خلفي متحرك --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-5 animate-pulse"></div>
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-green-400 to-blue-400 animate-pulse"></div>
    
    <div class="container mx-auto py-4 px-4 relative z-10">
        {{-- الصف الأول: المعلومات الأساسية --}}
        <div class="flex items-center justify-between gap-4 mb-3">
            <div class="flex items-center gap-4 flex-1">
                {{-- شعار متحرك --}}
                <div class="flex-shrink-0 relative">
                    <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full p-3 shadow-lg animate-bounce">
                        <span class="text-2xl">🚀</span>
                    </div>
                    <div class="absolute -top-1 -right-1 bg-red-500 rounded-full w-5 h-5 flex items-center justify-center">
                        <span class="text-xs font-bold animate-pulse">β</span>
                    </div>
                </div>
                
                {{-- النصوص الرئيسية --}}
                <div class="flex-1">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-2">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold">🎯 الإصدار الأولي التجريبي</h2>
                            <div class="flex gap-1">
                                <span class="bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded-full animate-pulse">BETA v1.0</span>
                                <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">جديد</span>
                            </div>
                        </div>
                        <div class="text-sm opacity-90 lg:mr-4">
                            <span class="hidden lg:inline text-yellow-300">●</span>
                            <span class="font-medium">هذا إصدار تجريبي من المكتبة الكاملة - تجربتك وملاحظاتك تساعدنا في التطوير!</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- أزرار الإجراءات --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="openFeedbackPanel()" 
                        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl group">
                    <span class="text-lg group-hover:animate-pulse">💬</span>
                    <span class="hidden sm:inline">أرسل ملاحظة</span>
                </button>
                
                <button onclick="toggleEnhancedDetails()" 
                        id="detailsToggleBtn"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium px-3 py-2 rounded-lg transition-all duration-200 flex items-center gap-1">
                    <span class="text-sm">تفاصيل</span>
                    <svg id="detailsArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <button onclick="closeEnhancedBetaNotice()" 
                        class="bg-red-500 bg-opacity-60 hover:bg-opacity-80 text-white p-2 rounded-lg transition-all duration-200 group"
                        title="إخفاء الإشعار">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- الصف الثاني: إحصائيات سريعة --}}
        <div class="flex flex-wrap items-center justify-center gap-4 text-sm opacity-90 mb-3">
            <div class="flex items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-full">
                <span>📚</span>
                <span class="font-medium">آلاف الكتب</span>
            </div>
            <div class="flex items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-full">
                <span>⚡</span>
                <span class="font-medium">بحث سريع</span>
            </div>
            <div class="flex items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-full">
                <span>🔄</span>
                <span class="font-medium">تحديث مستمر</span>
            </div>
            @if(\App\Models\FeedbackComplaint::count() > 0)
                <div class="flex items-center gap-2 bg-green-500 bg-opacity-60 px-3 py-1 rounded-full">
                    <span>💬</span>
                    <span class="font-medium">{{ \App\Models\FeedbackComplaint::count() }} ملاحظة مستلمة</span>
                </div>
            @endif
        </div>
        
        {{-- التفاصيل القابلة للطي --}}
        <div id="enhancedBetaDetails" class="hidden">
            <div class="border-t border-white border-opacity-20 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    {{-- إحصائيات --}}
                    <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                        <div class="text-2xl mb-2">📖</div>
                        <div class="font-bold text-lg">{{ number_format(rand(5000, 15000)) }}</div>
                        <div class="text-xs opacity-75">كتاب ومرجع</div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                        <div class="text-2xl mb-2">👥</div>
                        <div class="font-bold text-lg">{{ number_format(rand(1000, 5000)) }}</div>
                        <div class="text-xs opacity-75">زائر يومياً</div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                        <div class="text-2xl mb-2">🔍</div>
                        <div class="font-bold text-lg">{{ number_format(rand(10000, 50000)) }}</div>
                        <div class="text-xs opacity-75">عملية بحث</div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4 text-center">
                        <div class="text-2xl mb-2">⭐</div>
                        <div class="font-bold text-lg">{{ \App\Models\FeedbackComplaint::count() }}</div>
                        <div class="text-xs opacity-75">ملاحظة وشكوى</div>
                    </div>
                </div>
                
                {{-- ميزات قادمة --}}
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 bg-opacity-20 rounded-lg p-4 mb-4">
                    <h4 class="font-bold mb-3 flex items-center gap-2">
                        <span class="text-xl">🚀</span>
                        <span>ميزات قادمة في الإصدارات المستقبلية</span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-green-400">✓</span>
                            <span>بحث متقدم بالذكاء الاصطناعي</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-green-400">✓</span>
                            <span>حفظ المفضلات والملاحظات</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400">⏳</span>
                            <span>تطبيق للهواتف الذكية</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400">⏳</span>
                            <span>مشاركة النصوص والاقتباسات</span>
                        </div>
                    </div>
                </div>
                
                {{-- معلومات التواصل --}}
                <div class="flex flex-wrap items-center justify-between gap-4 text-xs opacity-75">
                    <div class="flex flex-wrap gap-4">
                        <span>آخر تحديث: {{ date('Y/m/d') }}</span>
                        <span>•</span>
                        <span>الإصدار: v1.0-beta</span>
                        <span>•</span>
                        <span>البيئة: تجريبية</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="showBetaFeedback()" class="underline hover:no-underline">تقييم الإصدار</button>
                        <span>•</span>
                        <button onclick="reportBug()" class="underline hover:no-underline">إبلاغ عن خطأ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // التحكم في الإشعار المُحسن
    let enhancedBetaVisible = true;
    
    function closeEnhancedBetaNotice() {
        const notice = document.getElementById('enhancedBetaNotice');
        if (notice) {
            notice.style.transform = 'translateY(-100%)';
            notice.style.opacity = '0';
            setTimeout(() => {
                notice.style.display = 'none';
                enhancedBetaVisible = false;
                localStorage.setItem('enhancedBetaNoticeClosed', 'true');
                localStorage.setItem('enhancedBetaClosedDate', new Date().toDateString());
            }, 300);
        }
    }
    
    function toggleEnhancedDetails() {
        const details = document.getElementById('enhancedBetaDetails');
        const arrow = document.getElementById('detailsArrow');
        const btn = document.getElementById('detailsToggleBtn');
        
        if (details && arrow) {
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
                btn.innerHTML = '<span class="text-sm">إخفاء</span>' + btn.innerHTML.split('</span>')[1];
            } else {
                details.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
                btn.innerHTML = '<span class="text-sm">تفاصيل</span>' + btn.innerHTML.split('</span>')[1];
            }
        }
    }
    
    function showBetaFeedback() {
        if (typeof openFeedbackPanel === 'function') {
            openFeedbackPanel();
            // تعبئة النموذج تلقائياً
            setTimeout(() => {
                const typeRadio = document.querySelector('input[name="type"][value="feedback"]');
                const subjectField = document.getElementById('subject');
                if (typeRadio) typeRadio.checked = true;
                if (subjectField) subjectField.value = 'تقييم الإصدار التجريبي';
            }, 500);
        } else {
            alert('💭 شاركنا رأيك في الإصدار التجريبي!\n\nما رأيك في:\n- سرعة البحث\n- سهولة الاستخدام\n- التصميم والألوان\n- المحتوى المتاح');
        }
    }
    
    function reportBug() {
        if (typeof openFeedbackPanel === 'function') {
            openFeedbackPanel();
            setTimeout(() => {
                const typeRadio = document.querySelector('input[name="type"][value="complaint"]');
                const subjectField = document.getElementById('subject');
                if (typeRadio) typeRadio.checked = true;
                if (subjectField) subjectField.value = 'إبلاغ عن خطأ في الإصدار التجريبي';
            }, 500);
        } else {
            alert('🐛 هل واجهت مشكلة؟\n\nيرجى إخبارنا عن:\n- الصفحة التي حدث فيها الخطأ\n- ما كنت تفعله عندما حدث\n- رسالة الخطأ (إن وجدت)\n- نوع المتصفح المستخدم');
        }
    }
    
    // تحميل الإعدادات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        const notice = document.getElementById('enhancedBetaNotice');
        if (notice) {
            const wasClosed = localStorage.getItem('enhancedBetaNoticeClosed');
            const closedDate = localStorage.getItem('enhancedBetaClosedDate');
            const today = new Date().toDateString();
            
            // إخفاء إذا تم إغلاقه اليوم
            if (wasClosed === 'true' && closedDate === today) {
                notice.style.display = 'none';
                enhancedBetaVisible = false;
            } else {
                // إضافة تأثير التحميل
                notice.style.transform = 'translateY(-100%)';
                notice.style.opacity = '0';
                setTimeout(() => {
                    notice.style.transition = 'all 0.5s ease-out';
                    notice.style.transform = 'translateY(0)';
                    notice.style.opacity = '1';
                }, 100);
            }
        }
    });
</script>

<style>
    #enhancedBetaNotice {
        transition: all 0.3s ease-in-out;
    }
    
    /* تحسينات للشاشات الصغيرة */
    @media (max-width: 768px) {
        #enhancedBetaNotice .container {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        #enhancedBetaNotice h2 {
            font-size: 1.1rem;
        }
    }
</style>