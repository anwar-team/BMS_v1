@php
    $impersonator = session(config('laravel-impersonate.session_key'))
        ? \App\Models\User::find(session(config('laravel-impersonate.session_key')))
        : null;

    $impersonated = Filament\Facades\Filament::auth()->user();

    $display = ($impersonated && $impersonated->firstname && $impersonated->lastname)
        ? $impersonated->firstname . ' ' . $impersonated->lastname
        : 'Unknown User';

    $impersonatorDisplay = ($impersonator && $impersonator->firstname && $impersonator->lastname)
        ? $impersonator->firstname . ' ' . $impersonator->lastname
        : 'Unknown User';
@endphp

{{-- تم تعليق الكود التالي مؤقتًا لأن خدمة impersonate غير مفعّلة في النظام حالياً.
    يمكن تفعيله لاحقًا عند تثبيت الحزمة lab404/laravel-impersonate
--}}

{{--
@if(auth()->check() && app('impersonate')->isImpersonating())
    <div class="fixed bottom-0 z-40 flex flex-col items-center justify-center w-full min-h-0" role="status" aria-live="polite">
        <div class="flex items-center justify-between w-full pl-2 mx-auto space-x-2 text-sm text-white bg-warning-600">
            <div class="flex items-center space-x-2">
                <!-- Icon -->
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                <span>Impersonating <span class="font-semibold">{{ $display }}</span>
                    @if($impersonator)
                        <span class="ml-1 px-2 py-0.5 rounded bg-secondary-50/20">as {{ $impersonatorDisplay }}</span>
                    @endif
            </div>

            <a href="{{ route('impersonate.leave') }}"
                class="inline-flex items-center px-2 py-1 text-xs font-semibold text-black transition shadow-sm bg-warning-300 hover:bg-warning-100"
                aria-label="Leave impersonation and return to your account">
                رجوع لحسابي
            </a>
        </div>
    </div>
@endif
--}}
