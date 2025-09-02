<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper">
            <div class="relative">
                <div class="pattern-top top-24"></div>
                
                <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                    <!-- Book Title Section -->
                    <div class="mb-12 z-10">
                        <div class="flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                            <a href="{{ route('books.details', $book->id) }}" 
                               class="text-4xl text-green-800 font-bold hover:text-green-600 transition-colors duration-200 cursor-pointer">
                                {{ $book->title }}
                            </a>
                        </div>
                    </div>

                    <!-- Book Image and Description Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 p-8">
                        <div class="flex flex-col lg:flex-row items-start gap-6">
                            <!-- Book Image -->
                            <div class="flex-shrink-0 w-full lg:w-1/4">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                         alt="{{ $book->title }}" 
                                         class="w-full h-auto rounded-lg shadow-md">
                                @else
                                    <div class="w-full h-80 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-500">لا توجد صورة للكتاب</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Book Description -->
                            <div class="w-full lg:w-3/4">
                                <h3 class="text-2xl font-semibold text-[#5D6019] mb-4">وصف الكتاب</h3>
                                @if($book->description)
                                    <p class="text-lg mt-4 text-gray-600 leading-relaxed">
                                        {{ $book->description }}
                                    </p>
                                @else
                                    <p class="text-lg mt-4 text-gray-500">
                                        لا يتوفر وصف لهذا الكتاب حالياً.
                                    </p>
                                @endif

                                <!-- Additional Book Info -->
                                <div class="mt-6 flex flex-wrap gap-4">
                                    @if($book->authors->count() > 0)
                                        <div class="bg-gray-50 px-4 py-2 rounded-lg">
                                            <span class="font-semibold text-green-800">المؤلف:</span>
                                            <span class="text-gray-700">
                                                @foreach($book->mainAuthors as $author)
                                                    {{ $author->name }}@if(!$loop->last), @endif
                                                @endforeach
                                                @if($book->mainAuthors->count() == 0)
                                                    @foreach($book->authors->take(1) as $author)
                                                        {{ $author->name }}
                                                    @endforeach
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($book->pages()->count() > 0)
                                        <div class="bg-gray-50 px-4 py-2 rounded-lg">
                                            <span class="font-semibold text-green-800">عدد الصفحات:</span>
                                            <span class="text-gray-700">{{ $book->pages()->count() }} صفحة</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <div class="mt-6">
                                    @if($book->pages()->count() > 0)
                                        <a href="#" 
                                           class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                                            قراءة الكتاب
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</x-superduper.main>
