<div class="relative inline-block text-left" x-data="{ open: false }">
    <div>
        <button @click="open = !open" type="button" 
                class="inline-flex items-center justify-center gap-x-2 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-800" 
                id="language-menu-button" aria-expanded="false" aria-haspopup="true">
            <span class="flex items-center gap-2">
                <span class="text-lg">{{ $availableLanguages[$currentLanguage]['flag'] }}</span>
                <span class="hidden sm:block">{{ $availableLanguages[$currentLanguage]['name'] }}</span>
            </span>
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-900 dark:ring-gray-600" 
         role="menu" aria-orientation="vertical" aria-labelledby="language-menu-button" tabindex="-1">
        <div class="py-1" role="none">
            @foreach($availableLanguages as $code => $language)
                <button wire:click="switchLanguage('{{ $code }}')" 
                        class="group flex items-center justify-between w-full px-4 py-3 text-left text-sm transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $currentLanguage === $code ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}" 
                        role="menuitem" tabindex="-1">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">{{ $language['flag'] }}</span>
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $language['name'] }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($code) }}</span>
                        </div>
                    </div>
                    @if($currentLanguage === $code)
                        <svg class="h-4 w-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
