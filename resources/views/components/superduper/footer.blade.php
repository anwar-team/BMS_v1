<footer class="section-footer">
    <div class="bg-primary-600">
        <!-- Logo Section -->
        <div class="text-white">
            <div class="py-[60px] lg:py-20">
                <div class="container-default">
                    <div class="flex justify-center">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('images/footer-WhiteLogo.svg') }}" alt="{{ $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper') }}" width="220" height="auto" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white bg-opacity-5">
            <div class="py-[18px]">
                <div class="container-default">
                    <div class="text-center text-white text-opacity-80">
                        &copy; Copyright {{ date('Y') }}, {{ $siteSettings->copyright_text ?? 'All Rights Reserved' }}
                        {{ $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
