<x-layouts.app>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg p-8" dir="rtl">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">اختبار نظام الملاحظات والشكاوى</h1>
                
                <div class="prose max-w-none">
                    <p class="text-lg text-gray-600 mb-6">
                        هذه صفحة اختبار لنظام الملاحظات والشكاوى.
                    </p>
                    
                    <div class="bg-green-50 border-r-4 border-green-800 p-4 mb-6">
                        <h3 class="text-lg font-bold text-green-800 mb-2">كيفية الاستخدام:</h3>
                        <ol class="list-decimal mr-6 space-y-2 text-gray-700">
                            <li>انظر للزر العائم في الأسفل اليسار 💬</li>
                            <li>اضغط على الزر لفتح نموذج الملاحظات</li>
                            <li>اختر نوع الرسالة (ملاحظة/شكوى)</li>
                            <li>املأ الموضوع والرسالة</li>
                            <li>اضغط على "إرسال" - الاسم والبريد اختياري!</li>
                        </ol>
                    </div>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">✓</span>
                                <p class="font-bold">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <div class="flex items-start gap-2">
                                <span class="text-2xl">✗</span>
                                <div>
                                    <p class="font-bold mb-2">يوجد أخطاء:</p>
                                    <ul class="list-disc mr-6">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <div class="text-4xl mb-3">📝</div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">عدد الملاحظات</h4>
                            <p class="text-3xl font-bold text-blue-600">{{ \App\Models\FeedbackComplaint::feedback()->count() }}</p>
                        </div>
                        
                        <div class="bg-red-50 p-6 rounded-lg">
                            <div class="text-4xl mb-3">⚠️</div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">عدد الشكاوى</h4>
                            <p class="text-3xl font-bold text-red-600">{{ \App\Models\FeedbackComplaint::complaint()->count() }}</p>
                        </div>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <div class="text-4xl mb-3">⏳</div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">قيد الانتظار</h4>
                            <p class="text-3xl font-bold text-yellow-600">{{ \App\Models\FeedbackComplaint::pending()->count() }}</p>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg">
                            <div class="text-4xl mb-3">📊</div>
                            <h4 class="text-lg font-bold text-gray-800 mb-2">الإجمالي</h4>
                            <p class="text-3xl font-bold text-green-600">{{ \App\Models\FeedbackComplaint::count() }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">آخر الرسائل المرسلة:</h3>
                        @php
                            $latestFeedbacks = \App\Models\FeedbackComplaint::latest()->take(5)->get();
                        @endphp
                        
                        @if($latestFeedbacks->count() > 0)
                            <div class="space-y-3">
                                @foreach($latestFeedbacks as $feedback)
                                    <div class="bg-white p-4 rounded border-r-4 {{ $feedback->type === 'feedback' ? 'border-green-500' : 'border-red-500' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xl">{{ $feedback->type === 'feedback' ? '⭐' : '⚠️' }}</span>
                                                    <span class="font-bold text-gray-800">{{ $feedback->subject }}</span>
                                                    <span class="text-xs px-2 py-1 rounded-full {{ 
                                                        $feedback->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                        ($feedback->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')
                                                    }}">
                                                        {{ $feedback->status_ar }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-1">{{ Str::limit($feedback->message, 100) }}</p>
                                                <p class="text-xs text-gray-400">{{ $feedback->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">لا توجد رسائل بعد. جرب إرسال أول رسالة! 📨</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
