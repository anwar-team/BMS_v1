@php
    $record = $getRecord();
@endphp

<div class="flex items-center justify-center">
    @if($record->hasIcon())
        @switch($record->icon_type)
            @case('upload')
            @case('url')
                @if($record->getIconUrlAttribute())
                    <img src="{{ $record->getIconUrlAttribute() }}" 
                         alt="{{ $record->name }}" 
                         class="w-8 h-8 rounded object-cover">
                @endif
                @break
                
            @case('library')
                @if($record->icon_library === 'heroicons' && $record->icon_name)
                    <svg class="w-8 h-8" 
                         fill="none" 
                         stroke="{{ $record->icon_color ?: '#000000' }}" 
                         viewBox="0 0 24 24">
                        <use href="#{{ $record->icon_name }}"></use>
                    </svg>
                @elseif($record->icon_library === 'fontawesome' && $record->icon_name)
                    <i class="fas fa-{{ $record->icon_name }} text-2xl" 
                       style="color: {{ $record->icon_color ?: '#000000' }}"></i>
                @endif
                @break
                
            @case('color')
                <div class="w-8 h-8 rounded-full border-2 border-gray-200" 
                     style="background-color: {{ $record->icon_color ?: '#3B82F6' }}"></div>
                @break
                
            @default
                @if($record->logo_path)
                    <img src="{{ asset('images/' . $record->logo_path) }}" 
                         alt="{{ $record->name }}" 
                         class="w-8 h-8 rounded object-cover">
                @else
                    <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
        @endswitch
    @else
        <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
            </svg>
        </div>
    @endif
</div>