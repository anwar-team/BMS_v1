{{-- الزر العائم للملاحظات والشكاوى --}}
<div id="feedbackFloatingButton" class="fixed bottom-6 left-6 z-[10000]">
    <button onclick="openFeedbackPanel()" 
            class="group bg-green-800 hover:bg-green-900 text-white font-bold py-4 px-6 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 flex items-center gap-3"
            title="إرسال ملاحظة أو شكوى">
        <span class="text-2xl">💬</span>
        <span class="hidden group-hover:inline-block transition-all duration-300">ملاحظة؟</span>
    </button>
</div>

{{-- نموذج الإرسال (Slide Panel) --}}
<div id="feedbackPanel" class="fixed inset-y-0 left-0 z-[10001] w-full sm:w-96 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out" dir="rtl">
    <div class="h-full flex flex-col">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-800 to-green-900 text-white p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-white text-2xl font-bold flex items-center gap-2">
                    <span class="text-3xl">💬</span>
                    <span>ملاحظاتك تهمنا</span>
                </h3>
                <button onclick="closeFeedbackPanel()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-green-100 text-sm">ساعدنا في تحسين تجربتك</p>
        </div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto p-6">
            <form id="feedbackForm" class="space-y-5">
                @csrf

                {{-- نوع الرسالة --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">نوع الرسالة <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="feedback" class="peer sr-only" required>
                            <div class="p-4 border-2 border-gray-300 rounded-lg text-center transition-all peer-checked:border-green-800 peer-checked:bg-green-50 hover:border-green-600">
                                <div class="text-3xl mb-2">⭐</div>
                                <div class="font-bold text-sm text-gray-700 peer-checked:text-green-800">ملاحظة/اقتراح</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="complaint" class="peer sr-only" required>
                            <div class="p-4 border-2 border-gray-300 rounded-lg text-center transition-all peer-checked:border-red-600 peer-checked:bg-red-50 hover:border-red-400">
                                <div class="text-3xl mb-2">⚠️</div>
                                <div class="font-bold text-sm text-gray-700">شكوى/مشكلة</div>
                            </div>
                        </label>
                    </div>
                    <div class="text-red-500 text-sm mt-1 hidden" id="typeError"></div>
                </div>

                {{-- الموضوع --}}
                <div>
                    <label for="subject" class="block text-sm font-bold text-gray-700 mb-2">
                        الموضوع <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           required 
                           minlength="3"
                           maxlength="255"
                           placeholder="اكتب موضوع الرسالة..."
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none">
                    <div class="text-red-500 text-sm mt-1 hidden" id="subjectError"></div>
                </div>

                {{-- المحتوى --}}
                <div>
                    <label for="message" class="block text-sm font-bold text-gray-700 mb-2">
                        الرسالة <span class="text-red-500">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              required 
                              minlength="10"
                              rows="6"
                              placeholder="اكتب ملاحظتك أو شكواك بالتفصيل..."
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none"></textarea>
                    <div class="text-xs text-gray-500 mt-1" id="charCount">0 / 10 حرف كحد أدنى</div>
                    <div class="text-red-500 text-sm mt-1 hidden" id="messageError"></div>
                </div>

                
            </form>
        </div>

        {{-- Footer Actions --}}
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeFeedbackPanel()" 
                        class="flex-1 bg-white hover:bg-gray-100 text-gray-700 font-bold py-3 px-4 rounded-lg border-2 border-gray-300 transition-all">
                    إلغاء
                </button>
                <button type="submit" 
                        form="feedbackForm"
                        id="submitBtn"
                        class="flex-1 bg-green-800 hover:bg-green-900 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitBtnText">إرسال</span>
                    <span id="submitBtnLoading" class="hidden">
                        <svg class="inline w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري الإرسال...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="feedbackToast" class="fixed top-6 right-6 z-[10002] hidden transform transition-all duration-300">
    <div class="bg-white rounded-lg shadow-2xl border-r-4 p-4 max-w-md" id="toastContent">
        {{-- Content will be inserted here --}}
    </div>
</div>

<script>
    // فتح نموذج الملاحظات
    function openFeedbackPanel() {
        const panel = document.getElementById('feedbackPanel');
        if (panel) {
            panel.classList.remove('-translate-x-full');
        }
        
        // إضافة overlay
        const overlay = document.createElement('div');
        overlay.id = 'feedbackOverlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9998]';
        overlay.onclick = closeFeedbackPanel;
        document.body.appendChild(overlay);
        
        // منع scroll
        document.body.style.overflow = 'hidden';
    }

    // إغلاق نموذج الملاحظات
    function closeFeedbackPanel() {
        const panel = document.getElementById('feedbackPanel');
        if (panel) {
            panel.classList.add('-translate-x-full');
        }
        
        // إزالة overlay
        const overlay = document.getElementById('feedbackOverlay');
        if (overlay) {
            overlay.remove();
        }
        
        // السماح بـ scroll
        document.body.style.overflow = '';
        
        // إعادة تعيين النموذج
        setTimeout(() => {
            document.getElementById('feedbackForm')?.reset();
            hideAllErrors();
        }, 300);
    }

    // عداد الأحرف
    document.getElementById('message')?.addEventListener('input', function() {
        const count = this.value.length;
        const counter = document.getElementById('charCount');
        if (counter) {
            counter.textContent = `${count} / 10 حرف كحد أدنى`;
            counter.className = count >= 10 ? 'text-xs text-green-600 mt-1 font-bold' : 'text-xs text-gray-500 mt-1';
        }
    });

    // إرسال النموذج
    document.getElementById('feedbackForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // إخفاء الأخطاء السابقة
        hideAllErrors();
        
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitBtnLoading = document.getElementById('submitBtnLoading');
        
        // تعطيل الزر وإظهار Loading
        submitBtn.disabled = true;
        submitBtnText.classList.add('hidden');
        submitBtnLoading.classList.remove('hidden');
        
        const formData = new FormData(this);
        
        // الحصول على CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        try {
            const response = await fetch('{{ route("feedback.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // إغلاق النموذج
                closeFeedbackPanel();
                
                // إظهار رسالة نجاح
                showToast('success', '✓ تم الإرسال بنجاح', 'شكراً لك! تم إرسال رسالتك وسنراجعها قريباً.');
                
                // إعادة تعيين النموذج
                this.reset();
            } else {
                // إظهار الأخطاء
                if (data.errors) {
                    showErrors(data.errors);
                }
                showToast('error', '✗ خطأ', 'يرجى التحقق من البيانات المدخلة');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', '✗ خطأ', 'حدث خطأ أثناء الإرسال. يرجى المحاولة مرة أخرى.');
        } finally {
            // إعادة تفعيل الزر
            submitBtn.disabled = false;
            submitBtnText.classList.remove('hidden');
            submitBtnLoading.classList.add('hidden');
        }
    });

    // إظهار الأخطاء
    function showErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const errorEl = document.getElementById(`${field}Error`);
            if (errorEl) {
                errorEl.textContent = messages[0];
                errorEl.classList.remove('hidden');
            }
        }
    }

    // إخفاء جميع الأخطاء
    function hideAllErrors() {
        document.querySelectorAll('[id$="Error"]').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
    }

    // إظهار Toast
    function showToast(type, title, message) {
        const toast = document.getElementById('feedbackToast');
        const content = document.getElementById('toastContent');
        
        const colors = {
            success: 'border-green-600',
            error: 'border-red-600',
            warning: 'border-yellow-600'
        };
        
        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠'
        };
        
        content.className = `bg-white rounded-lg shadow-2xl border-r-4 p-4 max-w-md ${colors[type]}`;
        content.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="text-2xl">${icons[type]}</span>
                <div>
                    <div class="font-bold text-gray-800 mb-1">${title}</div>
                    <div class="text-sm text-gray-600">${message}</div>
                </div>
            </div>
        `;
        
        toast.classList.remove('hidden');
        
        // إخفاء بعد 5 ثواني
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 5000);
    }
</script>
