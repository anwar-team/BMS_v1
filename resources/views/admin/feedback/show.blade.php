<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">تفاصيل الرسالة #{{ $feedback->id }}</h2>
            <a href="{{ route('admin.feedback.index') }}" class="text-green-600 hover:text-green-800 font-bold">
                ← العودة للقائمة
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- المحتوى الرئيسي --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- معلومات الرسالة --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">{{ $feedback->type_icon }}</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $feedback->type_badge }}">
                                        {{ $feedback->type_arabic }}
                                    </span>
                                    <span class="text-gray-500 text-sm">{{ $feedback->created_at->format('Y/m/d - H:i') }}</span>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $feedback->subject }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="prose prose-lg max-w-none">
                        <div class="bg-gray-50 rounded-lg p-6 border-r-4 border-gray-300">
                            <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $feedback->message }}</p>
                        </div>
                    </div>
                </div>

                {{-- ملاحظات الإدارة --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span>📝</span>
                        <span>ملاحظات الإدارة</span>
                    </h4>
                    
                    @if($feedback->admin_notes)
                        <div class="bg-blue-50 border-r-4 border-blue-500 rounded-lg p-4 mb-4">
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $feedback->admin_notes }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.feedback.update', $feedback->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="status" value="{{ $feedback->status }}">
                        <input type="hidden" name="priority" value="{{ $feedback->priority }}">

                        <textarea name="admin_notes" 
                                  rows="4" 
                                  placeholder="أضف ملاحظات الإدارة هنا..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none">{{ old('admin_notes', $feedback->admin_notes) }}</textarea>

                        <div class="mt-4">
                            <button type="submit" class="bg-green-800 hover:bg-green-900 text-white font-bold py-2 px-6 rounded-lg transition-all">
                                💾 حفظ الملاحظات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- الشريط الجانبي --}}
            <div class="space-y-6">
                {{-- الحالة والأولوية --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">الحالة والأولوية</h4>
                    
                    <form action="{{ route('admin.feedback.update', $feedback->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="admin_notes" value="{{ $feedback->admin_notes }}">

                        {{-- الحالة --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="pending" {{ $feedback->status == 'pending' ? 'selected' : '' }}>معلقة</option>
                                <option value="in_progress" {{ $feedback->status == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="resolved" {{ $feedback->status == 'resolved' ? 'selected' : '' }}>محلولة</option>
                            </select>
                        </div>

                        {{-- الأولوية --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الأولوية</label>
                            <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="low" {{ $feedback->priority == 'low' ? 'selected' : '' }}>منخفضة</option>
                                <option value="medium" {{ $feedback->priority == 'medium' ? 'selected' : '' }}>متوسطة</option>
                                <option value="high" {{ $feedback->priority == 'high' ? 'selected' : '' }}>عالية</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-green-800 hover:bg-green-900 text-white font-bold py-2 px-4 rounded-lg transition-all">
                            تحديث
                        </button>
                    </form>
                </div>

                {{-- معلومات تقنية --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">معلومات تقنية</h4>
                    
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-bold text-gray-700">معرف الرسالة:</span>
                            <span class="text-gray-600">{{ $feedback->id }}</span>
                        </div>

                        @if($feedback->ip_address)
                            <div>
                                <span class="font-bold text-gray-700">IP Address:</span>
                                <span class="text-gray-600 font-mono text-xs">{{ $feedback->ip_address }}</span>
                            </div>
                        @endif

                        <div>
                            <span class="font-bold text-gray-700">تاريخ الإرسال:</span>
                            <span class="text-gray-600">{{ $feedback->created_at->format('Y/m/d - H:i:s') }}</span>
                        </div>

                        <div>
                            <span class="font-bold text-gray-700">آخر تحديث:</span>
                            <span class="text-gray-600">{{ $feedback->updated_at->format('Y/m/d - H:i:s') }}</span>
                        </div>

                        @if($feedback->user_agent)
                            <div>
                                <span class="font-bold text-gray-700">User Agent:</span>
                                <p class="text-gray-600 text-xs break-all mt-1">{{ $feedback->user_agent }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- إجراءات --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">إجراءات</h4>
                    
                    <div class="space-y-2">
                        <a href="{{ route('admin.feedback.edit', $feedback->id) }}" 
                           class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                            ✏️ تعديل
                        </a>

                        <form action="{{ route('admin.feedback.destroy', $feedback->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                                🗑️ حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed top-6 right-6 bg-green-100 border-r-4 border-green-600 text-green-800 p-4 rounded-lg shadow-lg z-50">
            ✓ {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                document.querySelector('.fixed.top-6').remove();
            }, 3000);
        </script>
    @endif
</x-filament-panels::page>
