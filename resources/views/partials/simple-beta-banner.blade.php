{{-- إشعار الإصدار التجريبي البسيط --}}
<div id="betaBanner" class="bg-green-800 text-white py-3 shadow-lg relative z-40" dir="rtl">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between gap-4">
            {{-- المحتوى الرئيسي --}}
            <div class="flex items-center gap-3 flex-1">

                
                {{-- النص الرئيسي --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                    <h3 class="text-white font-bold text-lg"> الإصدار التجريبي الأولي للمكتبة الكاملة</h3>
                    <span class="text-sm opacity-90">
                        • نعمل على تطوير تجربتك باستمرار - شاركنا آرائك لنحسن الخدمة
                    </span>
                </div>
            </div>
            
            {{-- زر الملاحظات --}}
            <div class="flex-shrink-0">
                <button onclick="openFeedbackPanel()" 
                        class="bg-red-800 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg group">
                    <span class="text-lg group-hover:animate-pulse">💬</span>
                    <span class="hidden sm:inline">شاركنا رأيك</span>
                    <span class="sm:hidden">ملاحظة</span>
                </button>
            </div>
        </div>
        
        {{-- شريط متحرك صغير --}}
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full overflow-hidden">
            <div class="h-full bg-white opacity-60 w-1/3 rounded-full animate-pulse"></div>
        </div>
    </div>
</div>

<style>
    /* تحسين الرسوم المتحركة للإشعار البسيط */
    .bg-green-800 {
        background: linear-gradient(135deg, #166534 0%, #15803d 50%, #166534 100%);
    }
    
    /* حل مشكلة الـ Header المتحرك */
    /* تعديل موضع الـ Header ليبدأ بعد الإشعار */
    body:has(#betaBanner) header.fixed {
        position: relative !important;
        top: 0 !important;
    }
    
    /* إزالة الـ padding العلوي من الـ body */
    body:has(#betaBanner) {
        padding-top: 0 !important;
    }
    
    /* تأثير بسيط للحركة */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .bg-green-800 {
        animation: fadeInDown 0.6s ease-out;
    }
    
    /* تحسين للشاشات الصغيرة */
    @media (max-width: 640px) {
        .bg-green-800 .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .bg-green-800 h3 {
            font-size: 1rem;
        }
    }
</style>

<script>
    // حل مشكلة الـ Header المتحرك بـ JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header.fixed');
        const betaBanner = document.getElementById('betaBanner');
        
        if (header && betaBanner) {
            // تحويل الـ Header من fixed إلى relative عند وجود الإشعار
            header.style.position = 'relative';
            header.style.top = '0';
            
            // إزالة أي padding علوي قد يكون موجود في الـ body
            document.body.style.paddingTop = '0';
        }
    });
    
    // التأكد من توفر وظيفة فتح نموذج الملاحظات
    function openFeedbackPanel() {
        // البحث عن الزر العائم أولاً
        const feedbackButton = document.getElementById('feedbackButton');
        if (feedbackButton) {
            feedbackButton.click();
            return;
        }
        
        // البحث عن النموذج مباشرة
        const feedbackPanel = document.getElementById('feedbackPanel');
        if (feedbackPanel) {
            feedbackPanel.classList.remove('translate-x-full');
            feedbackPanel.classList.add('translate-x-0');
            return;
        }
        
        // إذا لم يكن النموذج متوفراً، عرض رسالة بديلة
        alert('شكراً لاهتمامك! يمكنك مراسلتنا عبر:\n\n📧 البريد الإلكتروني\n📱 وسائل التواصل الاجتماعي\n💬 نموذج الاتصال في الموقع');
    }
</script>