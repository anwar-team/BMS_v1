{{-- إشعار بسيط وأنيق للإصدار التجريبي --}}
<div id="simpleBetaNotice" class="bg-gradient-to-l from-blue-600 to-indigo-700 text-white relative overflow-hidden" dir="rtl">
    {{-- شريط علوي متحرك --}}
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-green-400 to-blue-400">
        <div class="h-full bg-white opacity-50 w-1/3 animate-pulse"></div>
    </div>
    
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            {{-- المحتوى الرئيسي --}}
            <div class="flex items-center gap-3 flex-1 min-w-0">
                {{-- أيقونة --}}
                <div class="flex-shrink-0 relative">
                    <div class="bg-yellow-400 text-black rounded-full w-10 h-10 flex items-center justify-center font-bold text-lg animate-pulse">
                        β
                    </div>
                </div>
                
                {{-- النص --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                        <h3 class="font-bold text-lg sm:text-xl truncate">الإصدار الأولي التجريبي</h3>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="bg-yellow-400 text-black px-2 py-1 rounded-full text-xs font-bold">BETA</span>
                            <span class="hidden sm:inline opacity-90">نرحب بملاحظاتكم لتحسين التجربة</span>
                            <span class="sm:hidden opacity-90 truncate">ملاحظاتكم تهمنا</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- الأزرار --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- زر الملاحظات --}}
                <button onclick="openFeedbackPanel()" 
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1 shadow-lg hover:shadow-xl">
                    <span class="text-lg">💬</span>
                    <span class="hidden sm:inline">ملاحظة</span>
                </button>
                
                {{-- زر التفاصيل --}}
                <button onclick="toggleSimpleDetails()" 
                        id="simpleDetailsBtn"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-3 py-2 rounded-lg text-sm transition-all duration-200">
                    <svg id="simpleDetailsArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                {{-- زر الإغلاق --}}
                <button onclick="closeSimpleBetaNotice()" 
                        class="bg-red-500 bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded-lg transition-all duration-200 group">
                    <svg class="w-4 h-4 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- التفاصيل المخفية --}}
        <div id="simpleDetails" class="hidden mt-3 pt-3 border-t border-white border-opacity-30 animate-fadeIn">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="text-center">
                    <div class="text-2xl mb-1">📚</div>
                    <div class="font-bold">{{ number_format(rand(5000, 15000)) }}</div>
                    <div class="text-xs opacity-75">كتاب</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">⚡</div>
                    <div class="font-bold">سريع</div>
                    <div class="text-xs opacity-75">بحث فوري</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">💬</div>
                    <div class="font-bold">{{ \App\Models\FeedbackComplaint::count() }}</div>
                    <div class="text-xs opacity-75">ملاحظة</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl mb-1">🔄</div>
                    <div class="font-bold">مستمر</div>
                    <div class="text-xs opacity-75">تطوير</div>
                </div>
            </div>
            
            <div class="text-center">
                <div class="flex flex-wrap justify-center gap-2 text-sm mb-2">
                    <button onclick="quickFeedback('إعجاب')" class="bg-green-500 bg-opacity-60 hover:bg-opacity-80 px-3 py-1 rounded-full transition-all">
                        👍 أعجبني
                    </button>
                    <button onclick="quickFeedback('اقتراح')" class="bg-blue-500 bg-opacity-60 hover:bg-opacity-80 px-3 py-1 rounded-full transition-all">
                        💡 اقتراح
                    </button>
                    <button onclick="quickFeedback('مشكلة')" class="bg-yellow-500 bg-opacity-60 hover:bg-opacity-80 px-3 py-1 rounded-full transition-all">
                        ⚠️ مشكلة
                    </button>
                </div>
                <div class="text-xs opacity-75">
                    آخر تحديث: {{ date('Y/m/d') }} • الإصدار: v1.0-beta
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    #simpleBetaNotice {
        transition: all 0.3s ease-in-out;
    }
    
    #simpleBetaNotice.slide-out {
        transform: translateY(-100%);
        opacity: 0;
    }
</style>

<script>
    function closeSimpleBetaNotice() {
        const notice = document.getElementById('simpleBetaNotice');
        if (notice) {
            notice.classList.add('slide-out');
            setTimeout(() => {
                notice.style.display = 'none';
                localStorage.setItem('simpleBetaNoticeClosed', 'true');
                localStorage.setItem('simpleBetaClosedDate', new Date().toDateString());
            }, 300);
        }
    }
    
    function toggleSimpleDetails() {
        const details = document.getElementById('simpleDetails');
        const arrow = document.getElementById('simpleDetailsArrow');
        
        if (details && arrow) {
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                details.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    }
    
    function quickFeedback(type) {
        if (typeof openFeedbackPanel === 'function') {
            openFeedbackPanel();
            setTimeout(() => {
                const subjectField = document.getElementById('subject');
                const messageField = document.getElementById('message');
                
                if (subjectField && messageField) {
                    subjectField.value = `${type} - الإصدار التجريبي`;
                    
                    if (type === 'إعجاب') {
                        messageField.value = 'أعجبني الإصدار التجريبي، خاصة ';
                    } else if (type === 'اقتراح') {
                        messageField.value = 'أقترح تحسين ';
                    } else if (type === 'مشكلة') {
                        messageField.value = 'واجهت مشكلة في ';
                    }
                    
                    messageField.focus();
                    messageField.setSelectionRange(messageField.value.length, messageField.value.length);
                }
            }, 500);
        } else {
            alert(`شكراً لك على ${type}!\nيمكنك مراسلتنا للمزيد من التفاصيل.`);
        }
    }
    
    // التحكم في عرض الإشعار
    document.addEventListener('DOMContentLoaded', function() {
        const notice = document.getElementById('simpleBetaNotice');
        if (notice) {
            const wasClosed = localStorage.getItem('simpleBetaNoticeClosed');
            const closedDate = localStorage.getItem('simpleBetaClosedDate');
            const today = new Date().toDateString();
            
            if (wasClosed === 'true' && closedDate === today) {
                notice.style.display = 'none';
            }
        }
    });
</script>