@php
    $footer = \AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('footer', []);
    $desc = $footer['description'] ?? [];
    $locale = app()->getLocale();
    $description = is_array($desc)
        ? ($desc[$locale] ?? $desc['ca'] ?? 'Assessoria fiscal, laboral i comptable a Caldes de Montbui.')
        : ($desc ?: 'Assessoria fiscal, laboral i comptable a Caldes de Montbui.');
    $phone = $footer['phone'] ?? '+34 93 862 61 00';
    $email = $footer['email'] ?? 'agcassessors@agc.cat';
    $address = $footer['address'] ?? 'Av. Pi i Margall 114 · 08140 · Caldes de Montbui';
    $copyright = $footer['copyright'] ?? '© ' . date('Y') . ' AGC Assessors. Tots els drets reservats.';
    $legalLinks = $footer['legal_links'] ?? [];
@endphp

{{-- Zone 1 — Main footer --}}
<footer class="bg-[#00346f] text-white w-full">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 py-14 grid grid-cols-1 md:grid-cols-2 gap-10">

        {{-- Left: Logo + description --}}
        <div class="flex flex-col items-center md:items-start gap-4">
            <img src="{{ asset('images/logo-footer-white.webp') }}" alt="AGC Assessors" class="h-11 w-auto object-contain">
            <p class="text-[15px] text-blue-100 leading-relaxed text-center md:text-left">
                {{ $description }}
            </p>
        </div>

        {{-- Center: Contact info --}}
        <div class="flex flex-col gap-3">
            <h3 class="font-semibold text-sm uppercase tracking-widest text-blue-200 mb-1">Contacte</h3>
            @if($address)
            <div class="flex items-start gap-2 text-[15px] text-blue-100">
                <svg class="w-4 h-4 mt-0.5 shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ $address }}</span>
            </div>
            @endif
            @if($phone)
            <a href="tel:{{ $phone }}" class="flex items-center gap-2 text-[15px] text-blue-100 hover:text-white transition-colors">
                <svg class="w-4 h-4 shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>{{ $phone }}</span>
            </a>
            @endif
            @if($email)
            <a href="mailto:{{ $email }}" class="flex items-center gap-2 text-[15px] text-blue-100 hover:text-white transition-colors">
                <svg class="w-4 h-4 shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>{{ $email }}</span>
            </a>
            @endif
        </div>

    </div>
</footer>

{{-- Zone 2 — Legal bar --}}
<div class="bg-[#1e293b] w-full py-4">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-[#94a3b8]">
        <span>{{ $copyright }}</span>
        <nav class="flex flex-wrap gap-x-6 gap-y-1" aria-label="Legal navigation">
                @foreach($legalLinks as $link)
                    @if(!empty($link['url']))
                    @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? $link['label'] ?? ''; @endphp
                    @if($label)
                    <a href="{{ $link['url'] }}" class="hover:text-white transition-colors">{{ $label }}</a>
                    @endif
                    @endif
                @endforeach
                <a href="{{ route('newsletter.unsubscribe.form') }}" class="hover:text-white transition-colors">
                    {{ __('messages.footer.newsletter_unsubscribe') }}
                </a>
            </nav>
    </div>
</div>
