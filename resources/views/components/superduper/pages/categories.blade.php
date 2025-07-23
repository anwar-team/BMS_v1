<x-superduper.main>
    <div class="page-wrapper relative z-[1]" dir="rtl">
        <main class="relative overflow-hidden main-wrapper ">

            <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pt-32">
                <div class="mb-12 z-10">
                    <div class="flex items-center gap-3 mb-8">
                        <img src="{{ asset('images/group0.svg') }}" alt="Icon" class="w-16 h-16">
                        <h2 class="text-4xl text-green-800 font-bold">أقسام الكتب</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @for ($i = 0; $i < 50; $i++)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                        <div class="relative">
                            <img
                                src="{{ asset('images/mask-group0.svg') }}"
                                alt="Category image"
                                class="absolute left-0 top-0 w-32 h-32">
                            <div class="p-8">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-xl text-green-800 font-bold mb-1">العقيدة</h3>
                                        <p class="text-sm text-gray-600">1035 كتاب</p>
                                    </div>
                                    <img src="{{ asset('images/group1.svg') }}" alt="Icon" class="w-16 h-16">
                                </div>
                            </div>
                        </div>
                </div>
                @endfor
    </div>
    </section>
    </main>
    </div>
</x-superduper.main>