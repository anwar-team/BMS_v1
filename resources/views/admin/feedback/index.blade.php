<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        {{-- الإحصائيات --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- إجمالي الرسائل --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border-r-4 border-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">إجمالي الرسائل</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="text-4xl">📊</div>
                </div>
            </div>

            {{-- المعلقة --}}
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-6 border-r-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">المعلقة</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="text-4xl">⏳</div>
                </div>
            </div>

            {{-- قيد المعالجة --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border-r-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">قيد المعالجة</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['in_progress'] }}</p>
                    </div>
                    <div class="text-4xl">🔄</div>
                </div>
            </div>

            {{-- المحلولة --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border-r-4 border-green-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">المحلولة</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['resolved'] }}</p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>
        </div>

        {{-- الفلاتر والبحث --}}
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.feedback.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- البحث --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">بحث في الموضوع أو المحتوى</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="ابحث..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    {{-- نوع الرسالة --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">النوع</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">الكل</option>
                            <option value="feedback" {{ request('type') == 'feedback' ? 'selected' : '' }}>ملاحظة</option>
                            <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>شكوى</option>
                        </select>
                    </div>

                    {{-- الحالة --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>محلولة</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" class="bg-green-800 hover:bg-green-900 text-white font-bold py-2 px-6 rounded-lg transition-all">
                        🔍 بحث
                    </button>
                    <a href="{{ route('admin.feedback.index') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                        مسح الفلاتر
                    </a>
                </div>
            </form>
        </div>

        {{-- جدول الرسائل --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموضوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">مقتطف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الأولوية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($feedbacks as $feedback)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $feedback->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $feedback->type_badge }}">
                                    {{ $feedback->type_icon }} {{ $feedback->type_arabic }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ \Str::limit($feedback->subject, 50) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ \Str::limit($feedback->message, 60) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $feedback->status_badge }}">
                                    {{ $feedback->status_arabic }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $feedback->priority_badge }}">
                                    {{ $feedback->priority_arabic }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $feedback->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.feedback.show', $feedback->id) }}" 
                                       class="text-green-600 hover:text-green-900 font-bold"
                                       title="عرض التفاصيل">
                                        👁️ عرض
                                    </a>
                                    <a href="{{ route('admin.feedback.edit', $feedback->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 font-bold"
                                       title="تعديل">
                                        ✏️ تعديل
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <div class="text-6xl mb-4">📭</div>
                                    <p class="text-lg font-medium">لا توجد رسائل</p>
                                    <p class="text-sm mt-2">لم يتم استلام أي ملاحظات أو شكاوى بعد</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($feedbacks->hasPages())
            <div class="bg-white rounded-lg shadow p-4">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
