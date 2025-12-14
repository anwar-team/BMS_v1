<x-layouts.app>
    <div class="min-h-screen bg-gray-100 py-12" dir="rtl">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">🧪 اختبار الزر العائم في الصفحات العادية</h1>
                
                <div class="prose max-w-none">
                    <div class="bg-green-50 border-r-4 border-green-600 p-6 mb-6">
                        <h2 class="text-xl font-bold text-green-800 mb-3">✅ التحقق من الزر العائم</h2>
                        <div class="space-y-3 text-gray-700">
                            <p><strong>1. انظر للأسفل اليسار</strong> - يجب أن ترى زر أخضر مع أيقونة 💬</p>
                            <p><strong>2. عند التمرير فوقه</strong> - يجب أن يظهر نص "ملاحظة؟"</p>
                            <p><strong>3. عند النقر</strong> - يجب أن يفتح نموذج من اليسار</p>
                            <p><strong>4. في النموذج</strong> - يمكنك إرسال ملاحظة بدون اسم أو بريد</p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border-r-4 border-blue-600 p-6 mb-6">
                        <h2 class="text-xl font-bold text-blue-800 mb-3">📋 معلومات تقنية</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <h4 class="font-bold mb-2">موقع الملف:</h4>
                                <code class="bg-gray-100 p-2 rounded block">resources/views/partials/feedback-panel.blade.php</code>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">يُضمن في:</h4>
                                <code class="bg-gray-100 p-2 rounded block">resources/views/components/layouts/app.blade.php</code>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">CSS Classes:</h4>
                                <code class="bg-gray-100 p-2 rounded block">fixed bottom-6 left-6 z-50</code>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">الموضع:</h4>
                                <code class="bg-gray-100 p-2 rounded block">أسفل يسار الشاشة</code>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border-r-4 border-yellow-600 p-6 mb-6">
                        <h2 class="text-xl font-bold text-yellow-800 mb-3">⚠️ إذا لم يظهر الزر</h2>
                        <div class="space-y-2 text-gray-700">
                            <p><strong>السبب المحتمل 1:</strong> تداخل CSS مع إشعار البيتا</p>
                            <p><strong>السبب المحتمل 2:</strong> JavaScript لم يتم تحميله</p>
                            <p><strong>السبب المحتمل 3:</strong> z-index منخفض</p>
                            <p><strong>الحل:</strong> افتح Developer Tools (F12) وابحث عن عنصر <code>feedbackFloatingButton</code></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">🔍 اختبار تفاعلي</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold mb-3">JavaScript Test:</h4>
                                <button onclick="testFeedbackButton()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    اختبار الزر برمجياً
                                </button>
                                <div id="testResult" class="mt-3 p-3 bg-white rounded border text-sm"></div>
                            </div>
                            
                            <div>
                                <h4 class="font-bold mb-3">Manual Test:</h4>
                                <button onclick="highlightFeedbackButton()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                    تمييز الزر
                                </button>
                                <p class="mt-2 text-sm text-gray-600">سيضع إطار أحمر حول الزر إذا كان موجود</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- محاكاة محتوى طويل لاختبار موضع الزر --}}
                    <div class="mt-8 space-y-4">
                        <h3 class="text-xl font-bold">📄 محتوى تجريبي (لاختبار الموضع)</h3>
                        @for($i = 1; $i <= 20; $i++)
                            <p class="text-gray-600">
                                هذا نص تجريبي رقم {{ $i }} لمحاكاة محتوى طويل للصفحة. 
                                يجب أن يبقى الزر العائم مرئياً في أسفل يسار الشاشة أثناء التمرير. 
                                هذا يساعد في اختبار أن الزر يعمل بشكل صحيح في جميع الصفحات.
                            </p>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function testFeedbackButton() {
            const result = document.getElementById('testResult');
            const button = document.getElementById('feedbackFloatingButton');
            const panel = document.getElementById('feedbackPanel');
            
            if (!button) {
                result.innerHTML = '<div class="text-red-600">❌ الزر غير موجود في DOM</div>';
                return;
            }
            
            if (!panel) {
                result.innerHTML = '<div class="text-red-600">❌ النموذج غير موجود في DOM</div>';
                return;
            }
            
            const buttonStyles = window.getComputedStyle(button);
            const isVisible = buttonStyles.display !== 'none' && buttonStyles.visibility !== 'hidden';
            
            if (!isVisible) {
                result.innerHTML = '<div class="text-yellow-600">⚠️ الزر موجود لكنه مخفي</div>';
                return;
            }
            
            result.innerHTML = '<div class="text-green-600">✅ الزر موجود ومرئي بشكل صحيح!</div>';
        }
        
        function highlightFeedbackButton() {
            const button = document.getElementById('feedbackFloatingButton');
            if (button) {
                button.style.border = '3px solid red';
                button.style.boxShadow = '0 0 20px red';
                setTimeout(() => {
                    button.style.border = '';
                    button.style.boxShadow = '';
                }, 3000);
                alert('تم العثور على الزر وتمييزه بإطار أحمر لمدة 3 ثوان');
            } else {
                alert('❌ لم يتم العثور على الزر في الصفحة');
            }
        }
        
        // اختبار تلقائي عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(testFeedbackButton, 1000);
        });
    </script>
</x-layouts.app>