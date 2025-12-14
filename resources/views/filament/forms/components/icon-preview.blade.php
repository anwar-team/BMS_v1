@php
    $iconType = $getState()['icon_type'] ?? null;
    $iconUrl = $getState()['icon_url'] ?? null;
    $iconLibrary = $getState()['icon_library'] ?? null;
    $iconName = $getState()['icon_name'] ?? null;
    $iconColor = $getState()['icon_color'] ?? '#3B82F6';
    $iconSize = $getState()['icon_size'] ?? 'md';
@endphp

<div class="p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-center min-h-[80px]">
        @if($iconType === 'upload' && $iconUrl)
            <img src="{{ asset('storage/' . $iconUrl) }}" 
                 alt="معاينة الأيقونة" 
                 class="max-w-16 max-h-16 object-contain">
        @elseif($iconType === 'url' && $iconUrl)
            <img src="{{ $iconUrl }}" 
                 alt="معاينة الأيقونة" 
                 class="max-w-16 max-h-16 object-contain"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div style="display: none;" class="text-red-500 text-sm">
                <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                خطأ في تحميل الصورة
            </div>
        @elseif($iconType === 'library' && $iconLibrary && $iconName)
            @php
                $sizeClasses = [
                    'sm' => 'w-4 h-4',
                    'md' => 'w-6 h-6',
                    'lg' => 'w-8 h-8',
                    'xl' => 'w-12 h-12'
                ];
                
                if ($iconSize === 'custom' && isset($getState()['icon_custom_size'])) {
                    $customSize = $getState()['icon_custom_size'];
                    $sizeClass = "w-[{$customSize}px] h-[{$customSize}px]";
                } else {
                    $sizeClass = $sizeClasses[$iconSize] ?? 'w-6 h-6';
                }
            @endphp
            
            @if($iconLibrary === 'heroicons')
                <svg class="{{ $sizeClass }}" fill="none" stroke="{{ $iconColor }}" viewBox="0 0 24 24">
                    @switch($iconName)
                        @case('home')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            @break
                        @case('user')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            @break
                        @case('book')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            @break
                        @case('star')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            @break
                        @case('heart')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            @break
                        @default
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    @endswitch
                </svg>
            @elseif($iconLibrary === 'fontawesome')
                <i class="fas fa-{{ $iconName }} {{ $sizeClass }}" style="color: {{ $iconColor }}"></i>
            @else
                <div class="text-gray-500 text-sm">أيقونة مخصصة: {{ $iconName }}</div>
            @endif
        @elseif($iconType === 'color')
            <div class="w-16 h-16 rounded-full border-2 border-gray-300" 
                 style="background-color: {{ $iconColor }}"></div>
        @else
            <div class="text-gray-400 text-sm text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                اختر نوع الأيقونة لرؤية المعاينة
            </div>
        @endif
    </div>
    
    @if($iconType)
        <div class="mt-3 text-xs text-gray-600 dark:text-gray-400 text-center">
            النوع: {{ 
                $iconType === 'upload' ? 'ملف مرفوع' : 
                ($iconType === 'url' ? 'رابط خارجي' : 
                ($iconType === 'library' ? 'من المكتبة' : 'لون فقط'))
            }}
            @if($iconType === 'library' && $iconLibrary && $iconName)
                | المكتبة: {{ $iconLibrary }} | الاسم: {{ $iconName }}
            @endif
        </div>
    @endif
</div>