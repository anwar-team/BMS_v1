{{-- إشعار الإصدار التجريبي --}}
<div id="betaNotice" class="bg-gradient-to-r from-blue-600 via-purple-600 to-blue-700 text-white py-3 px-4 shadow-lg relative overflow-hidden" dir="rtl">
    {{-- Background Animation --}}
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-10 animate-pulse"></div>
    
    <div class="container mx-auto relative z-10">
        <div class="flex items-center justify-between gap-4">
            {{-- المحتوى الرئيسي --}}
            <div class="flex items-center gap-4 flex-1">
                {{-- الأيقونة المتحركة --}}
                <div class="flex-shrink-0">
                    <div class="bg-white bg-opacity-20 rounded-full p-2 animate-bounce">
                        <span class="text-2xl">🚀</span>
                    </div>
                </div>
                
                {{-- النص --}}
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-lg">الإصدار الأولي التجريبي</h3>
                            <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full animate-pulse">BETA</span>
                        </div>
                        <div class="text-sm opacity-90">
                            <span class="hidden sm:inline">•</span>
                            <span>هذا إصدار تجريبي من المكتبة الكاملة - نرحب بملاحظاتكم!</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- الإجراءات --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- رابط الملاحظات --}}
                <button onclick="openFeedbackPanel()" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium px-3 py-2 rounded-lg transition-all duration-200 flex items-center gap-2 group">
                    <span class="text-lg group-hover:animate-pulse">💬</span>
                    <span class="hidden sm:inline">أرسل ملاحظة</span>
                </button>
                
                {{-- زر الإغلاق --}}
                <button onclick="closeBetaNotice()" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition-all duration-200 group"
                        title="إخفاء الإشعار">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- معلومات إضافية قابلة للطي --}}
        <div id="betaDetails" class="mt-3 pt-3 border-t border-white border-opacity-20 text-sm opacity-90 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📚</span>
                    <div>
                        <div class="font-medium">المحتوى</div>
                        <div class="text-xs opacity-75">آلاف الكتب والمراجع</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-lg">🔍</span>
                    <div>
                        <div class="font-medium">البحث المتقدم</div>
                        <div class="text-xs opacity-75">بحث سريع ودقيق</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚡</span>
                    <div>
                        <div class="font-medium">تحسينات مستمرة</div>
                        <div class="text-xs opacity-75">تطوير يومي</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 flex flex-wrap gap-2">
                <button onclick="toggleBetaDetails()" class="text-xs underline hover:no-underline">
                    إخفاء التفاصيل
                </button>
                <span class="text-xs opacity-50">•</span>
                <span class="text-xs opacity-75">آخر تحديث: {{ date('Y/m/d') }}</span>
            </div>
        </div>
        
        {{-- زر عرض المزيد --}}
        <div class="mt-2 text-center md:text-left">
            <button onclick="toggleBetaDetails()" 
                    id="showMoreBtn"
                    class="text-sm underline hover:no-underline opacity-75 hover:opacity-100 transition-opacity">
                عرض المزيد من التفاصيل ↓
            </button>
        </div>
    </div>
</div>

{{-- إشعار إضافي متحرك (اختياري) --}}
<div class="bg-gradient-to-r from-green-500 to-green-600 text-white py-1 text-center text-sm font-medium overflow-hidden relative">
    <div class="animate-pulse">
        <span class="opacity-75">🎯</span>
        <span class="mx-2">نعمل على تحسين تجربتك باستمرار</span>
        <span class="opacity-75">📈</span>
    </div>
</div>

<style>
    /* تحسين الرسوم المتحركة */
    @keyframes slideInDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutUp {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(-100%);
            opacity: 0;
        }
    }
    
    #betaNotice {
        animation: slideInDown 0.5s ease-out;
    }
    
    #betaNotice.closing {
        animation: slideOutUp 0.3s ease-in forwards;
    }
    
    /* تحسين للشاشات الصغيرة */
    @media (max-width: 640px) {
        #betaNotice .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<script>
    // التحكم في إشعار البيتا
    let betaNoticeVisible = true;
    
    function closeBetaNotice() {
        const notice = document.getElementById('betaNotice');
        if (notice) {
            notice.classList.add('closing');
            setTimeout(() => {
                notice.style.display = 'none';
                betaNoticeVisible = false;
                
                // حفظ حالة الإغلاق في localStorage
                localStorage.setItem('betaNoticeClosed', 'true');
                localStorage.setItem('betaNoticeClosedDate', new Date().toDateString());
            }, 300);
        }
    }
    
    function toggleBetaDetails() {
        const details = document.getElementById('betaDetails');
        const btn = document.getElementById('showMoreBtn');
        
        if (details && btn) {
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                btn.textContent = 'إخفاء التفاصيل ↑';
            } else {
                details.classList.add('hidden');
                btn.textContent = 'عرض المزيد من التفاصيل ↓';
            }
        }
    }
    
    // فتح نموذج الملاحظات (إذا كان موجوداً)
    function openFeedbackPanel() {
        if (typeof window.openFeedbackPanel === 'function') {
            window.openFeedbackPanel();
        } else {
            // إنشاء نموذج مؤقت إذا لم يكن نموذج الملاحظات موجوداً
            alert('🗣️ ملاحظاتك مهمة لنا!\n\nيمكنك إرسال ملاحظاتك عبر:\n- البريد الإلكتروني\n- وسائل التواصل الاجتماعي\n- أو من خلال الزر العائم في الصفحة');
        }
    }
    
    // التحقق من حالة الإشعار عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        const notice = document.getElementById('betaNotice');
        if (notice) {
            // التحقق إذا كان المستخدم أغلق الإشعار من قبل
            const wasClosed = localStorage.getItem('betaNoticeClosed');
            const closedDate = localStorage.getItem('betaNoticeClosedDate');
            const today = new Date().toDateString();
            
            // إظهار الإشعار مرة أخرى كل يوم أو إذا لم يتم إغلاقه من قبل
            if (wasClosed === 'true' && closedDate === today) {
                notice.style.display = 'none';
                betaNoticeVisible = false;
            }
            
            // تأثير التلاشي التلقائي بعد 10 ثوان (اختياري)
            // setTimeout(() => {
            //     if (betaNoticeVisible) {
            //         notice.style.opacity = '0.8';
            //     }
            // }, 10000);
        }
    });
    
    // إعادة إظهار الإشعار عند التحديث (اختياري)
    function showBetaNotice() {
        const notice = document.getElementById('betaNotice');
        if (notice) {
            notice.style.display = 'block';
            notice.classList.remove('closing');
            betaNoticeVisible = true;
            
            // إزالة حالة الإغلاق المحفوظة
            localStorage.removeItem('betaNoticeClosed');
            localStorage.removeItem('betaNoticeClosedDate');
        }
    }
</script>