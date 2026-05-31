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

<footer class="bg-[#ededf5] w-full pt-16 pb-8 border-t border-[#d9d9e1]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8">

        {{-- Main grid — 3 columns --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-12 mb-12">

            {{-- Col 1: Brand --}}
            <div class="space-y-5">
                <img src="{{ asset('images/logo.webp') }}"
                     alt="AGC Assessors"
                     class="h-9 w-auto object-contain">

                @if($description)
                <p class="text-[14px] text-[#64748B] leading-relaxed max-w-xs">
                    {{ $description }}
                </p>
                @endif
            </div>

            {{-- Col 2: Navigation --}}
            <div>
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.15em] text-[#94a3b8] mb-5">
                    {{ __('messages.footer.links') }}
                </h4>
                <nav class="flex flex-col gap-3">
                    @foreach($navLinks as $link)
                        @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? ''; @endphp
                        @if($label && !empty($link['url']))
                        <a href="{{ $link['url'] }}"
                           class="text-[14px] text-[#0f172a] font-medium hover:text-[#00346f] transition-colors duration-300 w-fit">
                            {{ $label }}
                        </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Col 3: Contact + Legal + Social --}}
            <div class="space-y-6">
                {{-- Contact --}}
                <div>
                    <h4 class="text-[12px] font-semibold uppercase tracking-[0.15em] text-[#94a3b8] mb-5">
                        {{ __('messages.footer.contact') }}
                    </h4>
                    <div class="space-y-3.5">
                        @if($phone)
                        <a href="tel:{{ $phone }}"
                           class="flex items-center gap-3 text-[14px] text-[#0f172a] hover:text-[#00346f] transition-colors duration-300 group w-fit">
                            <span class="material-symbols-outlined text-[#00346f] text-[18px] group-hover:-translate-y-0.5 transition-transform">call</span>
                            <span>{{ $phone }}</span>
                        </a>
                        @endif

                        @if($emailAddr)
                        <a href="mailto:{{ $emailAddr }}"
                           class="flex items-center gap-3 text-[14px] text-[#0f172a] hover:text-[#00346f] transition-colors duration-300 group w-fit">
                            <span class="material-symbols-outlined text-[#00346f] text-[18px] group-hover:-translate-y-0.5 transition-transform">mail</span>
                            <span>{{ $emailAddr }}</span>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Social icons --}}
                @php
                    $socials = collect(\AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('social_networks', []))
                        ->filter(fn($n) => !empty($n['is_active']) && !empty($n['url']));
                @endphp
                @if($socials->isNotEmpty())
                <div class="flex items-center gap-4">
                    @foreach($socials->take(3) as $network)
                    <a href="{{ $network['url'] }}" target="_blank" rel="noopener noreferrer"
                       class="text-[#64748B] hover:text-[#00346f] transition-all duration-300 hover:-translate-y-0.5"
                       aria-label="{{ ucfirst($network['platform']) }}">
                        @include('public.components.social-icon', ['platform' => $network['platform'], 'size' => 'w-[18px] h-[18px]'])
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Bottom bar: copyright + legal links --}}
        <div class="pt-8 border-t border-[#d9d9e1] flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-[13px] text-[#94a3b8]">
                {{ $copyright }}
            </p>

            <nav class="flex flex-wrap items-center gap-x-6 gap-y-2 justify-center md:justify-end">
                @foreach($legalLinks as $link)
                    @php $label = $link['label_' . $locale] ?? $link['label_ca'] ?? ''; @endphp
                    @if($label && !empty($link['url']))
                    <a href="{{ $link['url'] }}"
                       class="text-[13px] text-[#94a3b8] hover:text-[#00346f] transition-colors duration-300">
                        {{ $label }}
                    </a>
                    @endif
                @endforeach
                <a href="{{ route('newsletter.unsubscribe.form') }}"
                   class="text-[13px] text-[#94a3b8] hover:text-[#00346f] transition-colors duration-300">
                    {{ __('messages.footer.newsletter_unsubscribe') }}
                </a>
            </nav>
        </div>
    </div>
</footer>
