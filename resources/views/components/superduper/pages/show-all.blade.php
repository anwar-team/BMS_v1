<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">
            <!-- background pattern-->
            <div class="relative">
                <div class="pattern-top top-24"></div>
                <!--<div class="pattern-top top-80"></div>-->
                <!-- end of background pattern-->
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    
                    {{-- Header Section --}}
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-8 h-8">
                            <h2 class="text-4xl text-green-800 font-bold">{{ $title }}</h2>
                        </div>


                    </div>

                    {{-- استخدام Livewire Components حسب النوع --}}
                    @if($type === 'books')
                        @livewire('books-table', [
                            'section' => request('section'),
                            'showSearch' => true,
                            'showFilters' => false,
                            'title' => $title,
                            'perPage' => request('per_page', 50),
                            'showPagination' => true
                        ])
                    @else
                        @livewire('authors-table', [
                            'showSearch' => true,
                            'showFilters' => false,
                            'title' => $title,
                            'perPage' => request('per_page', 50),
                            'showPagination' => true
                        ])
                    @endif

                </section>
            </div>
        </main>
    </div>
</x-superduper.main>