{{-- رسالة الترحيب - تظهر مرة واحدة عند أول زيارة --}}
<div id="welcomeModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center p-4" style="backdrop-filter: blur(5px);">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl transform transition-all" dir="rtl">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-800 to-green-900 text-white p-8 rounded-t-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-5xl">🎉</span>
                        <h2 class="text-3xl font-bold">مرحباً بك في مكتبة المتكاملة</h2>
                    </div>
                    <button onclick="closeWelcomeModal()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-green-100 text-lg">بحمد الله تعالى، تم إطلاق الموقع الجديد لمكتبة المتكاملة!</p>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-8">
            {{-- Features --}}
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="text-2xl">📚</span>
                    <span>ماذا نقدم؟</span>
                </h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="text-green-800 text-xl mt-1">✓</span>
                        <p class="text-gray-700">بحث فوري وسريع في آلاف الكتب الإسلامية والعربية</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-green-800 text-xl mt-1">✓</span>
                        <p class="text-gray-700">فلترة متقدمة حسب القسم، المؤلف، والتاريخ</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-green-800 text-xl mt-1">✓</span>
                        <p class="text-gray-700">واجهة عصرية وسهلة الاستخدام</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-green-800 text-xl mt-1">✓</span>
                        <p class="text-gray-700">محتوى موثوق ومُنظم بدقة</p>
                    </div>
                </div>
            </div>

            {{-- Important Notice --}}
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-r-4 border-orange-400 p-6 rounded-lg mb-6">
                <div class="flex items-start gap-3">
                    <span class="text-3xl">⚠️</span>
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">ملاحظة هامة</h4>
                        <p class="text-gray-700 leading-relaxed">
                            هذا <strong class="text-orange-600">إطلاق تجريبي أولي</strong>، وقد تواجه بعض المشاكل التقنية أو الأخطاء. 
                            نحن نعمل باستمرار على تحسين الخدمة وإصلاح المشاكل.
                        </p>
                        <p class="text-gray-600 mt-2">
                            نرحب بملاحظاتكم وشكاويكم لتحسين تجربتكم في الموقع.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="closeWelcomeModal()" class="flex-1 bg-green-800 hover:bg-green-900 text-white font-bold py-4 px-6 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <span class="flex items-center justify-center gap-2">
                        <span>بدء التصفح</span>
                        <span class="text-xl">🚀</span>
                    </span>
                </button>
                <button onclick="openFeedbackFromWelcome()" class="flex-1 bg-white hover:bg-gray-50 text-green-800 font-bold py-4 px-6 rounded-lg border-2 border-green-800 transition-all duration-300 hover:shadow-lg">
                    <span class="flex items-center justify-center gap-2">
                        <span>إرسال ملاحظة</span>
                        <span class="text-xl">💬</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // فحص إذا كانت هذه أول زيارة
    window.addEventListener('DOMContentLoaded', function() {
        const hasVisited = localStorage.getItem('hasVisitedBefore');
        
        if (!hasVisited) {
            // إظهار Modal بعد ثانية واحدة
            setTimeout(function() {
                const modal = document.getElementById('welcomeModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }, 1000);
        }
    });

    function closeWelcomeModal() {
        const modal = document.getElementById('welcomeModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        // حفظ أن المستخدم زار الموقع
        localStorage.setItem('hasVisitedBefore', 'true');
    }

    function openFeedbackFromWelcome() {
        closeWelcomeModal();
        
        // فتح نموذج الملاحظات بعد إغلاق Modal
        setTimeout(function() {
            if (typeof openFeedbackPanel === 'function') {
                openFeedbackPanel();
            }
        }, 300);
    }

    // إغلاق Modal عند الضغط خارجه
    document.getElementById('welcomeModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeWelcomeModal();
        }
    });
</script>
