<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-6" dir="rtl">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">تعديل الرسالة #{{ $feedback->id }}</h2>
            <a href="{{ route('admin.feedback.show', $feedback->id) }}" class="text-green-600 hover:text-green-800 font-bold">
                ← العودة للتفاصيل
            </a>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('admin.feedback.update', $feedback->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- معلومات الرسالة --}}
                <div class="bg-gray-50 rounded-lg p-6 border-r-4 border-gray-300">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-3xl">{{ $feedback->type_icon }}</span>
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $feedback->type_badge }}">
                                {{ $feedback->type_arabic }}
                            </span>
                            <h3 class="text-xl font-bold text-gray-900 mt-1">{{ $feedback->subject }}</h3>
                        </div>
                    </div>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $feedback->message }}</p>
                    <p class="text-sm text-gray-500 mt-4">تاريخ الإرسال: {{ $feedback->created_at->format('Y/m/d - H:i') }}</p>
                </div>

                {{-- الحالة --}}
                <div>
                    <label for="status" class="block text-sm font-bold text-gray-700 mb-2">
                        الحالة <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none">
                        <option value="pending" {{ old('status', $feedback->status) == 'pending' ? 'selected' : '' }}>⏳ معلقة</option>
                        <option value="in_progress" {{ old('status', $feedback->status) == 'in_progress' ? 'selected' : '' }}>🔄 قيد المعالجة</option>
                        <option value="resolved" {{ old('status', $feedback->status) == 'resolved' ? 'selected' : '' }}>✅ محلولة</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- الأولوية --}}
                <div>
                    <label for="priority" class="block text-sm font-bold text-gray-700 mb-2">
                        الأولوية <span class="text-red-500">*</span>
                    </label>
                    <select id="priority" 
                            name="priority" 
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none">
                        <option value="low" {{ old('priority', $feedback->priority) == 'low' ? 'selected' : '' }}>منخفضة</option>
                        <option value="medium" {{ old('priority', $feedback->priority) == 'medium' ? 'selected' : '' }}>متوسطة</option>
                        <option value="high" {{ old('priority', $feedback->priority) == 'high' ? 'selected' : '' }}>عالية</option>
                    </select>
                    @error('priority')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ملاحظات الإدارة --}}
                <div>
                    <label for="admin_notes" class="block text-sm font-bold text-gray-700 mb-2">
                        ملاحظات الإدارة
                    </label>
                    <textarea id="admin_notes" 
                              name="admin_notes" 
                              rows="6"
                              placeholder="أضف ملاحظات الإدارة هنا..."
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-800 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none">{{ old('admin_notes', $feedback->admin_notes) }}</textarea>
                    @error('admin_notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- أزرار الإجراءات --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" 
                            class="flex-1 bg-green-800 hover:bg-green-900 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl">
                        💾 حفظ التعديلات
                    </button>
                    <a href="{{ route('admin.feedback.show', $feedback->id) }}" 
                       class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition-all">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>

        {{-- معلومات إضافية --}}
        <div class="bg-blue-50 border-r-4 border-blue-400 rounded-lg p-4">
            <div class="flex items-start gap-2">
                <span class="text-xl">ℹ️</span>
                <div class="text-sm text-gray-700">
                    <p class="font-bold mb-1">نصائح:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>غيّر الحالة إلى "قيد المعالجة" عند البدء بمعالجة الرسالة</li>
                        <li>غيّر الحالة إلى "محلولة" بعد حل المشكلة أو تنفيذ الاقتراح</li>
                        <li>استخدم ملاحظات الإدارة لتوثيق الإجراءات المتخذة</li>
                    </ul>
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
                document.querySelector('.fixed.top-6')?.remove();
            }, 3000);
        </script>
    @endif
</x-filament-panels::page>
