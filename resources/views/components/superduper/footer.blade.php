
 <!-- ===================  FOOTER  =================== -->
<footer class="bg-[#1a3a2a] text-white">
    <div class="container mx-auto max-w-[1170px] px-4 py-10 flex flex-col items-center gap-10">
        {{-- شعار الموقع --}}
        <a href="{{ route('home') }}">
            @php
                $brandLogo  = $generalSettings->brand_logo   ?? null;
                $brandName  = $generalSettings->brand_name   ?? $siteSettings->name ?? config('app.name', 'SuperDuper');
                $footerLogo = $siteSettings->footer_logo     ?? $brandLogo;
            @endphp

            @if ($footerLogo)
                <img src="{{ Storage::url($footerLogo) }}"
                     alt="{{ $brandName }}"
                     class="h-auto w-[164px]"   {{-- نفس أبعاد النموذج الثاني تقريبًا --}}
                />
            @endif
        </a>

        {{-- أيقونات التواصل الاجتماعي --}}
        @php
            $socialLinks = [
                'facebook'  => $siteSocialSettings->facebook_url  ?? null,
                'twitter'   => $siteSocialSettings->twitter_url   ?? null,
                'instagram' => $siteSocialSettings->instagram_url ?? null,
                'linkedin'  => $siteSocialSettings->linkedin_url  ?? null,
                'youtube'   => $siteSocialSettings->youtube_url   ?? null,
                'tiktok'    => $siteSocialSettings->tiktok_url    ?? null,
            ];
            $faIcons = [
                'twitter'   => 'fa-brands fa-x-twitter',
                'facebook'  => 'fa-brands fa-facebook-f',
                'instagram' => 'fa-brands fa-instagram',
                'linkedin'  => 'fa-brands fa-linkedin-in',
                'youtube'   => 'fa-brands fa-youtube',
                'tiktok'    => 'fa-brands fa-tiktok',
            ];
        @endphp

        <div class="flex flex-wrap justify-center gap-5">
            @forelse($socialLinks as $platform => $url)
                @if($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                       class="flex h-[30px] w-[30px] items-center justify-center rounded-full bg-white/5 text-white text-sm
                              transition hover:bg-color-pale-gold hover:text-color-denim-darkblue"
                       aria-label="{{ $platform }}">
                        <i class="{{ $faIcons[$platform] }}"></i>
                    </a>
                @endif
            @empty
                {{-- روابط افتراضيّة إذا لم تُضِف شيئًا بعد --}}
                <a href="https://twitter.com"   target="_blank" class="flex h-[30px] w-[30px] items-center justify-center rounded-full bg-white/5 text-white">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
                <a href="https://facebook.com/" target="_blank" class="flex h-[30px] w-[30px] items-center justify-center rounded-full bg-white/5 text-white">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            @endforelse
        </div>

        {{-- خط فاصل نصف شفّاف  --}}
        <hr class="w-full border-t border-white/20" />

        {{-- حقوق النشر --}}
        <p class="text-sm text-center text-white/80">
            &copy; {{ date('Y') }}
            {{ $siteSettings->copyright_text ?? 'جميع الحقوق محفوظة.' }}
            {{ $generalSettings->brand_name ?? $siteSettings->name ?? config('app.name', 'SuperDuper') }}
        </p>
    </div>
</footer>
<!-- ================================================ -->
