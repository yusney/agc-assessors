@php
    $footer = \AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('footer', []);
    $locale = app()->getLocale();
    $description = is_array($footer['description'] ?? null)
        ? ($footer['description'][$locale] ?? $footer['description']['ca'] ?? '')
        : ($footer['description'] ?? '');
    $copyright = $footer['copyright'] ?? '© ' . date('Y') . ' AGC Assessors. Tots els drets reservats.';
    $phone     = $footer['phone'] ?? null;
    $emailAddr = $footer['email'] ?? null;
    $navLinks   = $footer['nav_links'] ?? [];
    $legalLinks = $footer['legal_links'] ?? [];
@endphp

{{-- Footer v2: fondo claro, logo + descripción + nav + legal + contacto + socials --}}
<footer class="bg-[#ededf5] w-full py-16 border-t border-[#E2E8F0]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8
                grid grid-cols-1 md:grid-cols-2 gap-8">

        {{-- Izquierda: logo + descripción + copyright + contacto --}}
        <div class="space-y-4">
            <img src="{{ asset('images/logo.webp') }}"
                 alt="AGC Assessors"
                 class="h-10 w-auto object-contain">

            @if($description)
            <p class="text-[15px] text-[#64748B] leading-relaxed max-w-md font-light">
                {{ $description }}
            </p>
            @endif

            <p class="text-[14px] text-[#64748B] font-light">{{ $copyright }}</p>

            @if($phone || $emailAddr)
            <div class="flex flex-wrap items-center gap-6 pt-1">
                @if($phone)
                <a href="tel:{{ $phone }}"
                   class="flex items-center gap-2.5 text-[15px] text-[#64748B] hover:text-[#00346f] transition-colors duration-300 group">
                    <span class="material-symbols-outlined text-[#00346f] text-[20px] group-hover:-translate-y-0.5 transition-transform">call</span>
                    <span>{{ $phone }}</span>
                </a>
                @endif

                @if($emailAddr)
                <a href="mailto:{{ $emailAddr }}"
                   class="flex items-center gap-2.5 text-[15px] text-[#64748B] hover:text-[#00346f] transition-colors duration-300 group">
                    <span class="material-symbols-outlined text-[#00346f] text-[20px] group-hover:-translate-y-0.5 transition-transform">mail</span>
                    <span>{{ $emailAddr }}</span>
                </a>
                @endif
            </div>
            @endif
        </div>

        {{-- Derecha: nav links + legal + socials --}}
        <div class="flex flex-col items-start md:items-end gap-8">
            {{-- Nav links --}}
            @if(!empty($navLinks))
            <nav class="flex flex-wrap gap-x-8 gap-y-3" aria-label="Footer navigation">
                @foreach($navLinks as $link)
                    @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? ''; @endphp
                    @if($label && !empty($link['url']))
                    <a href="{{ $link['url'] }}"
                       class="text-[15px] text-[#0f172a] font-medium hover:text-[#00346f]
                              transition-colors duration-300">
                        {{ $label }}
                    </a>
                    @endif
                @endforeach
            </nav>
            @endif

            {{-- Legal + unsubscribe + socials --}}
            <div class="flex flex-wrap gap-6 items-center justify-start md:justify-end">
                @foreach($legalLinks as $link)
                    @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? ''; @endphp
                    @if($label && !empty($link['url']))
                    <a href="{{ $link['url'] }}"
                       class="text-[14px] text-[#64748B] hover:text-[#0f172a]
                              transition-colors duration-300">
                        {{ $label }}
                    </a>
                    @endif
                @endforeach
                <a href="{{ route('newsletter.unsubscribe.form') }}"
                   class="text-[14px] text-[#64748B] hover:text-[#0f172a] transition-colors duration-300">
                    {{ __('messages.footer.newsletter_unsubscribe') }}
                </a>

                {{-- Socials --}}
                @php
                    $socials = collect(\AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('social_networks', []))
                        ->filter(fn($n) => !empty($n['is_active']) && !empty($n['url']));
                @endphp
                @if($socials->isNotEmpty())
                <div class="flex items-center gap-4 pl-6 border-l border-[#c2c6d3]/50">
                    @foreach($socials->take(3) as $network)
                    <a href="{{ $network['url'] }}" target="_blank" rel="noopener noreferrer"
                       class="text-[#00346f] hover:text-[#004a99] transition-all duration-300 hover:-translate-y-1"
                       aria-label="{{ ucfirst($network['platform']) }}">
                        @include('public.components.social-icon', ['platform' => $network['platform']])
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</footer>
