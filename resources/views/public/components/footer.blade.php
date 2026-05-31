@php
    $footer = \AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('footer', []);
    $locale = app()->getLocale();
    $copyright = $footer['copyright'] ?? '© ' . date('Y') . ' AGC Assessors. Tots els drets reservats.';
    $legalLinks = $footer['legal_links'] ?? [];
@endphp

{{-- Footer v2: fondo claro, logo + copyright + nav + socials --}}
<footer class="bg-[#ededf5] w-full py-16 border-t border-[#E2E8F0]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8
                grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

        {{-- Izquierda: logo + copyright --}}
        <div>
            <img src="{{ asset('images/logo.webp') }}"
                 alt="AGC Assessors"
                 class="h-10 w-auto object-contain mb-3">
            <p class="text-[15px] text-[#64748B] font-light">
                {{ $copyright }}
            </p>
        </div>

        {{-- Derecha: links legales + socials --}}
        <nav class="flex flex-wrap gap-6 md:justify-end items-center">
            @foreach($legalLinks as $link)
                @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? ''; @endphp
                @if($label && !empty($link['url']))
                <a href="{{ $link['url'] }}"
                   class="text-[15px] text-[#64748B] hover:text-[#0f172a]
                          transition-colors duration-300">
                    {{ $label }}
                </a>
                @endif
            @endforeach
            <a href="{{ route('newsletter.unsubscribe.form') }}"
               class="text-[15px] text-[#64748B] hover:text-[#0f172a] transition-colors duration-300">
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
        </nav>
    </div>
</footer>
