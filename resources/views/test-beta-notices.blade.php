<x-layouts.app>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-8" dir="rtl">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">🎨 معاينة إشعارات الإصدار التجريبي</h1>
                <p class="text-lg text-gray-600 mb-6">اختر التصميم الأنسب للموقع</p>
            </div>
            
            {{-- الإصدار الأول: البسيط --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" dir="rtl">
                    <div class="bg-gray-800 text-white p-4">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <span class="text-2xl">1️⃣</span>
                            الإصدار البسيط
                        </h2>
                        <p class="text-sm opacity-75 mt-1">تصميم بسيط وأنيق، مناسب لجميع الشاشات</p>
                    </div>
                    <div class="p-1">
                        @include('partials.beta-notice')
                    </div>
                    <div class="p-4 bg-gray-50">
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ بسيط</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ سريع التحميل</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ متجاوب</span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">ℹ أساسي</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- الإصدار الثاني: المُحسن --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" dir="rtl">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <span class="text-2xl">2️⃣</span>
                            الإصدار المُحسن
                        </h2>
                        <p class="text-sm opacity-75 mt-1">تصميم شامل مع إحصائيات وتفاصيل كاملة</p>
                    </div>
                    <div class="p-1">
                        @include('partials.beta-notice-enhanced')
                    </div>
                    <div class="p-4 bg-gray-50">
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ تفاعلي</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ إحصائيات مباشرة</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ ميزات قادمة</span>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">⭐ متقدم</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- الإصدار الثالث: البسيط الأنيق --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" dir="rtl">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-4">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <span class="text-2xl">3️⃣</span>
                            الإصدار البسيط الأنيق
                        </h2>
                        <p class="text-sm opacity-75 mt-1">توازن مثالي بين البساطة والتفاعل</p>
                    </div>
                    <div class="p-1">
                        @include('partials.beta-notice-simple')
                    </div>
                    <div class="p-4 bg-gray-50">
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ متوازن</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ تفاعل سريع</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">✓ للجوال والكمبيوتر</span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">⚡ مُوصى به</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- دليل الاستخدام --}}
            <div class="bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg p-6 mb-8" dir="rtl">
                <h3 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <span class="text-3xl">🚀</span>
                    كيفية التطبيق
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white bg-opacity-10 rounded-lg p-4">
                        <h4 class="font-bold mb-2 text-lg">1️⃣ للإصدار البسيط</h4>
                        <div class="text-sm opacity-90 space-y-1">
                            <p>في ملف <code class="bg-black bg-opacity-30 px-1 rounded">app.blade.php</code>:</p>
                            <code class="block bg-black bg-opacity-30 p-2 rounded text-xs">
                                @include('partials.beta-notice')
                            </code>
                        </div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4">
                        <h4 class="font-bold mb-2 text-lg">2️⃣ للإصدار المُحسن</h4>
                        <div class="text-sm opacity-90 space-y-1">
                            <p>في ملف <code class="bg-black bg-opacity-30 px-1 rounded">app.blade.php</code>:</p>
                            <code class="block bg-black bg-opacity-30 p-2 rounded text-xs">
                                @include('partials.beta-notice-enhanced')
                            </code>
                        </div>
                    </div>
                    
                    <div class="bg-white bg-opacity-10 rounded-lg p-4">
                        <h4 class="font-bold mb-2 text-lg">3️⃣ للإصدار الأنيق</h4>
                        <div class="text-sm opacity-90 space-y-1">
                            <p>في ملف <code class="bg-black bg-opacity-30 px-1 rounded">app.blade.php</code>:</p>
                            <code class="block bg-black bg-opacity-30 p-2 rounded text-xs">
                                @include('partials.beta-notice-simple')
                            </code>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-white bg-opacity-10 rounded-lg p-4">
                    <h4 class="font-bold mb-2">📝 ملاحظات مهمة:</h4>
                    <ul class="text-sm opacity-90 space-y-1 mr-4">
                        <li>• كل إصدار يحفظ حالة الإغلاق منفصلة</li>
                        <li>• الإشعارات متجاوبة مع جميع أحجام الشاشات</li>
                        <li>• تتكامل تلقائياً مع نظام الملاحظات والشكاوى</li>
                        <li>• يمكن تخصيص الألوان والنصوص حسب الحاجة</li>
                    </ul>
                </div>
            </div>
            
            {{-- إحصائيات سريعة --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6 text-center" dir="rtl">
                    <div class="text-3xl mb-2">📊</div>
                    <div class="text-2xl font-bold text-blue-600">3</div>
                    <div class="text-sm text-gray-600">تصميمات متاحة</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 text-center" dir="rtl">
                    <div class="text-3xl mb-2">📱</div>
                    <div class="text-2xl font-bold text-green-600">100%</div>
                    <div class="text-sm text-gray-600">متجاوب</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 text-center" dir="rtl">
                    <div class="text-3xl mb-2">⚡</div>
                    <div class="text-2xl font-bold text-yellow-600">سريع</div>
                    <div class="text-sm text-gray-600">تحميل فوري</div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 text-center" dir="rtl">
                    <div class="text-3xl mb-2">🎨</div>
                    <div class="text-2xl font-bold text-purple-600">قابل</div>
                    <div class="text-sm text-gray-600">للتخصيص</div>
                </div>
            </div>
            
            {{-- روابط مفيدة --}}
            <div class="bg-white rounded-lg shadow-lg p-6 text-center" dir="rtl">
                <h3 class="text-xl font-bold text-gray-800 mb-4">🔗 روابط مفيدة</h3>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="/test-feedback" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all">
                        🧪 اختبار نظام الملاحظات
                    </a>
                    <a href="/test-feedback-js.html" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-all">
                        🔧 اختبار JavaScript
                    </a>
                    <a href="/admin/feedback-complaints" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-all">
                        👨‍💼 لوحة الإدارة
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>