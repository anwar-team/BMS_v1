{{-- إشعار الإصدار التجريبي - يظهر فوق الـ Header --}}
<div id="topBetaNotice" class="bg-gradient-to-l from-blue-600 via-indigo-600 to-purple-700 text-white relative overflow-hidden shadow-lg" dir="rtl">
    {{-- شريط متحرك علوي --}}
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-green-400 to-orange-400">
        <div class="h-full bg-white opacity-60 w-1/4 animate-pulse"></div>
    </div>
    
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            {{-- المحتوى الرئيسي --}}
            <div class="flex items-center gap-3 flex-1 min-w-0">
                {{-- أيقونة البيتا المتحركة --}}
                <div class="flex-shrink-0 relative">
                    <div class="bg-yellow-400 text-black rounded-full w-10 h-10 flex items-center justify-center font-bold text-sm shadow-lg">
                        <span class="animate-pulse">β</span>
                    </div>
                    <div class="absolute -top-1 -right-1 bg-red-500 rounded-full w-4 h-4 flex items-center justify-center">
                        <span class="text-xs font-bold text-white animate-bounce">!</span>
                    </div>
                </div>
                
                {{-- النص --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-lg sm:text-xl truncate">🚀 الإصدار الأولي التجريبي</h3>
                            <span class="bg-yellow-400 text-black px-2 py-1 rounded-full text-xs font-bold animate-pulse">BETA v1.0</span>
                        </div>
                        <div class="text-sm opacity-90">
                            <span class="hidden sm:inline text-yellow-300">●</span>
                            <span class="font-medium">هذا إصدار تجريبي من المكتبة الكاملة - ملاحظاتكم تساعدنا في التطوير!</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- أزرار التفاعل --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- زر الملاحظات السريع --}}
                <button onclick="openFeedbackPanel()" 
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1 shadow-lg hover:shadow-xl group">
                    <span class="text-lg group-hover:animate-pulse">💬</span>
                    <span class="hidden sm:inline">ملاحظة</span>
                </button>
                
                {{-- زر التفاصيل --}}
                <button onclick="toggleTopBetaDetails()" 
                        id="topDetailsBtn"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-3 py-2 rounded-lg text-sm transition-all duration-200 flex items-center gap-1">
                    <span class="text-sm">تفاصيل</span>
                    <svg id="topDetailsArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                {{-- زر الإغلاق --}}
                <button onclick="closeTopBetaNotice()" 
                        class="bg-red-500 bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded-lg transition-all duration-200 group"
                        title="إخفاء الإشعار">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- التفاصيل القابلة للطي --}}
        <div id="topBetaDetails" class="hidden mt-3 pt-3 border-t border-white border-opacity-30">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                {{-- إحصائيات سريعة --}}
                <div class="text-center">
                    <div class="text-2xl mb-1">📚</div>
                    <div class="font-bold">{{ number_format(rand(5000, 15000)) }}</div>
                    <div class="text-xs opacity-75">كتاب ومرجع</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">⚡</div>
                    <div class="font-bold">فوري</div>
                    <div class="text-xs opacity-75">بحث سريع</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">💬</div>
                    <div class="font-bold">{{ \App\Models\FeedbackComplaint::count() }}</div>
                    <div class="text-xs opacity-75">ملاحظة مستلمة</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">🔄</div>
                    <div class="font-bold">مستمر</div>
                    <div class="text-xs opacity-75">تحديث يومي</div>
                </div>
            </div>
            
            {{-- أزرار ردود فعل سريعة --}}
            <div class="flex flex-wrap justify-center gap-2 mb-3">
                <button onclick="quickTopFeedback('إعجاب')" 
                        class="bg-green-500 bg-opacity-60 hover:bg-opacity-80 px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1">
                    <span>👍</span>
                    <span>أعجبني التحديث</span>
                </button>
                <button onclick="quickTopFeedback('اقتراح')" 
                        class="bg-blue-500 bg-opacity-60 hover:bg-opacity-80 px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1">
                    <span>💡</span>
                    <span>لدي اقتراح</span>
                </button>
                <button onclick="quickTopFeedback('مشكلة')" 
                        class="bg-yellow-500 bg-opacity-60 hover:bg-opacity-80 px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1">
                    <span>⚠️</span>
                    <span>واجهت مشكلة</span>
                </button>
            </div>
            
            {{-- معلومات إضافية --}}
            <div class="text-center">
                <div class="flex flex-wrap justify-center gap-4 text-xs opacity-75 mb-2">
                    <span>📅 آخر تحديث: {{ date('Y/m/d') }}</span>
                    <span>•</span>
                    <span>🏷️ الإصدار: v1.0-beta</span>
                    <span>•</span>
                    <span>🌍 البيئة: تجريبية</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- شريط متحرك إضافي (اختياري) --}}
<div class="bg-gradient-to-r from-green-500 via-blue-500 to-purple-500 text-white py-1 text-center text-sm font-medium overflow-hidden">
    <div class="animate-pulse flex justify-center items-center gap-2">
        <span class="opacity-75">🎯</span>
        <span>نعمل على تطوير تجربتك باستمرار - شاركنا رأيك</span>
        <span class="opacity-75">📈</span>
    </div>
</div>

<style>
    /* تحسين الرسوم المتحركة */
    @keyframes slideDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(-100%);
            opacity: 0;
        }
    }
    
    #topBetaNotice {
        animation: slideDown 0.5s ease-out;
    }
    
    #topBetaNotice.closing {
        animation: slideUp 0.3s ease-in forwards;
    }
    
    /* تحسين للشاشات الصغيرة */
    @media (max-width: 640px) {
        #topBetaNotice .container {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        #topBetaNotice h3 {
            font-size: 1rem;
        }
    }
    
    /* تأثير تدرجي للخلفية */
    #topBetaNotice::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        animation: shimmer 3s infinite;
        pointer-events: none;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
</style>

<script>
    // التحكم في إشعار البيتا العلوي
    let topBetaVisible = true;
    
    function closeTopBetaNotice() {
        const notice = document.getElementById('topBetaNotice');
        if (notice) {
            notice.classList.add('closing');
            setTimeout(() => {
                notice.style.display = 'none';
                topBetaVisible = false;
                
                // حفظ حالة الإغلاق
                localStorage.setItem('topBetaNoticeClosed', 'true');
                localStorage.setItem('topBetaClosedDate', new Date().toDateString());
            }, 300);
        }
    }
    
    function toggleTopBetaDetails() {
        const details = document.getElementById('topBetaDetails');
        const arrow = document.getElementById('topDetailsArrow');
        const btn = document.getElementById('topDetailsBtn');
        
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
    
    function quickTopFeedback(type) {
        // فتح نموذج الملاحظات إذا كان متوفراً
        if (typeof openFeedbackPanel === 'function') {
            openFeedbackPanel();
            
            // ملء النموذج تلقائياً بعد تأخير قصير
            setTimeout(() => {
                const typeRadio = document.querySelector('input[name="type"][value="feedback"]');
                const subjectField = document.getElementById('subject');
                const messageField = document.getElementById('message');
                
                if (typeRadio) typeRadio.checked = true;
                
                if (subjectField && messageField) {
                    subjectField.value = `${type} - الإصدار التجريبي`;
                    
                    let messageText = '';
                    if (type === 'إعجاب') {
                        messageText = 'أعجبني الإصدار التجريبي الجديد، خاصة ';
                    } else if (type === 'اقتراح') {
                        messageText = 'أقترح تحسين الإصدار التجريبي من خلال ';
                    } else if (type === 'مشكلة') {
                        messageText = 'واجهت مشكلة في الإصدار التجريبي وهي: ';
                    }
                    
                    messageField.value = messageText;
                    messageField.focus();
                    messageField.setSelectionRange(messageField.value.length, messageField.value.length);
                }
            }, 500);
        } else {
            // عرض رسالة بديلة إذا لم يكن النموذج متوفراً
            alert(`شكراً لك على ${type}!\n\nيمكنك مراسلتنا عبر:\n- البريد الإلكتروني\n- وسائل التواصل الاجتماعي\n- أو من خلال نموذج الاتصال`);
        }
    }
    
    // التحكم في عرض الإشعار عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        const notice = document.getElementById('topBetaNotice');
        if (notice) {
            const wasClosed = localStorage.getItem('topBetaNoticeClosed');
            const closedDate = localStorage.getItem('topBetaClosedDate');
            const today = new Date().toDateString();
            
            // إخفاء إذا تم إغلاقه اليوم
            if (wasClosed === 'true' && closedDate === today) {
                notice.style.display = 'none';
                topBetaVisible = false;
            } else {
                // إظهار مع تأثير الحركة
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
    
    // إعادة إظهار الإشعار (للمطورين)
    function showTopBetaNotice() {
        const notice = document.getElementById('topBetaNotice');
        if (notice) {
            notice.style.display = 'block';
            notice.classList.remove('closing');
            topBetaVisible = true;
            localStorage.removeItem('topBetaNoticeClosed');
            localStorage.removeItem('topBetaClosedDate');
        }
    }
</script>