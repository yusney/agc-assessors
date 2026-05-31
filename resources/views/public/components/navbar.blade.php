<header class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-[#E2E8F0]"
        x-data="{ open: false, langOpen: false }">
    @php
        use AGC\Infrastructure\Persistence\Eloquent\Models\MenuItem;
        use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;

        $menuItems = MenuItem::active()->ordered()->whereNull('parent_id')->with('children')->get();

        $socialNetworks = collect(SiteSetting::get('social_networks', []) ?? [])
            ->filter(fn($n) => !empty($n['is_active']) && !empty($n['url']) && !empty($n['platform']))
            ->values();

        $visibleSocials = $socialNetworks->take(3);
        $hiddenSocials  = $socialNetworks->skip(3);
    @endphp
    <div class="flex justify-between items-center h-20 w-full px-6 md:px-8 max-w-[1280px] mx-auto">

        {{-- Brand --}}
        <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), '/') }}"
           class="flex items-center">
            <img src="{{ asset('images/logo.webp') }}"
                 alt="AGC Assessors"
                 class="h-10 w-auto object-contain">
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-8" aria-label="Main navigation">
            @foreach($menuItems as $item)
                @if($item->children->isNotEmpty())
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen"
                                class="flex items-center gap-1 nav-link">
                            {{ $item->getTranslation('label', app()->getLocale()) }}
                            <span class="material-symbols-outlined text-[16px]"
                                  :class="dropdownOpen ? 'rotate-180' : ''"
                                  style="transition: transform .2s">expand_more</span>
                        </button>
                        <div x-show="dropdownOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute left-0 top-full mt-2 w-48 bg-white rounded-xl
                                    border border-[#E2E8F0] shadow-lg overflow-hidden z-50">
                            @foreach($item->children as $child)
                                <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $child->url_path) }}"
                                   target="{{ $child->target }}"
                                   class="block px-4 py-2.5 text-[14px] text-[#1E293B] hover:text-[#00346f] hover:bg-[#f9f9ff] transition-colors">
                                    {{ $child->getTranslation('label', app()->getLocale()) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $item->url_path) }}"
                       target="{{ $item->target }}"
                       class="nav-link">
                        {{ $item->getTranslation('label', app()->getLocale()) }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-3">

            {{-- Social icons (desktop only, lg+) --}}
            @if($socialNetworks->isNotEmpty())
                <div class="hidden lg:flex items-center gap-3 mr-1"
                     x-data="{ moreOpen: false }"
                     @click.outside="moreOpen = false">

                    @foreach($visibleSocials as $network)
                        <a href="{{ $network['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-[#00346f] hover:text-[#00B4D8] transition-all duration-300 hover:-translate-y-0.5"
                           aria-label="{{ ucfirst($network['platform']) }}">
                            @include('public.components.social-icon', ['platform' => $network['platform']])
                        </a>
                    @endforeach

                    @if($hiddenSocials->isNotEmpty())
                        <div class="relative">
                            <button @click="moreOpen = !moreOpen"
                                    class="flex items-center justify-center w-5 h-5 text-[#00346f] hover:text-[#00B4D8] transition-colors duration-300"
                                    aria-label="{{ __('messages.nav.more_socials') }}">
                                <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                            </button>
                            <div x-show="moreOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl
                                        border border-[#E2E8F0] shadow-lg overflow-hidden z-50">
                                @foreach($hiddenSocials as $network)
                                    <a href="{{ $network['url'] }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="flex items-center gap-3 px-4 py-2.5 text-[14px]
                                              text-[#1E293B] hover:text-[#00346f] hover:bg-[#f9f9ff] transition-colors">
                                        <span class="text-[#00346f]">
                                            @include('public.components.social-icon', ['platform' => $network['platform']])
                                        </span>
                                        {{ ucfirst($network['platform']) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Search button (desktop only) --}}
            <button class="hidden md:flex items-center justify-center text-[#00346f] hover:text-[#00B4D8] transition-colors duration-300 p-0.5"
                    aria-label="{{ __('messages.nav.search') }}">
                <span class="material-symbols-outlined text-[22px]">search</span>
            </button>

            {{-- Language selector --}}
            <div class="relative hidden md:block" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                <button @click="langOpen = !langOpen"
                        class="flex items-center gap-1.5 text-[#1E293B] hover:text-[#00346f]
                               text-[14px] font-medium uppercase tracking-wider transition-colors py-1 px-2">
                    {{ app()->getLocale() }}
                    <span class="material-symbols-outlined text-[16px]"
                          :class="langOpen ? 'rotate-180' : ''"
                          style="transition: transform .2s">expand_more</span>
                </button>
                <div x-show="langOpen"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 top-full mt-2 w-28 bg-white rounded-xl
                            border border-[#E2E8F0] shadow-lg overflow-hidden z-50">
                    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $properties)
                    <a href="{{ route('locale.switch', $locale) }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-[14px]
                              {{ app()->getLocale() === $locale ? 'text-[#00346f] font-semibold bg-[#f3f3fa]' : 'text-[#1E293B] hover:bg-[#f9f9ff]' }}
                              transition-colors">
                        {{ strtoupper($locale) }}
                        <span class="text-[12px] text-[#64748B] normal-case font-normal">{{ $properties['native'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), '/contacte') }}"
               class="hidden md:inline-flex btn-primary text-sm">
                {{ __('messages.nav.cta') }}
            </a>

            {{-- Mobile burger --}}
            <button @click="open = !open"
                    class="md:hidden text-[#1E293B] hover:text-[#00346f] transition-colors"
                    :aria-expanded="open"
                    aria-label="Menú">
                <span x-show="!open" class="material-symbols-outlined text-[28px]">menu</span>
                <span x-show="open"  class="material-symbols-outlined text-[28px]">close</span>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden border-t border-[#E2E8F0] bg-white px-6 pb-6">
        <ul class="flex flex-col gap-1 pt-3">
            @foreach($menuItems as $item)
                <li>
                    @if($item->children->isNotEmpty())
                        <div x-data="{ mobDropdownOpen: false }">
                            <button @click="mobDropdownOpen = !mobDropdownOpen"
                                    class="flex items-center justify-between w-full py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium transition-colors">
                                {{ $item->getTranslation('label', app()->getLocale()) }}
                                <span class="material-symbols-outlined text-[16px]"
                                      :class="mobDropdownOpen ? 'rotate-180' : ''"
                                      style="transition: transform .2s">expand_more</span>
                            </button>
                            <div x-show="mobDropdownOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="pl-4 flex flex-col gap-1">
                                @foreach($item->children as $child)
                                    <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $child->url_path) }}"
                                       target="{{ $child->target }}"
                                       class="block py-2 text-[14px] text-[#1E293B] hover:text-[#00346f] transition-colors">
                                        {{ $child->getTranslation('label', app()->getLocale()) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $item->url_path) }}"
                           target="{{ $item->target }}"
                           class="block py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium transition-colors">
                            {{ $item->getTranslation('label', app()->getLocale()) }}
                        </a>
                    @endif
                </li>
            @endforeach

            {{-- Mobile language switcher --}}
            <li class="pt-3 border-t border-[#E2E8F0] mt-3">
                <div class="flex items-center gap-4">
                    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $properties)
                    <a href="{{ route('locale.switch', $locale) }}"
                       class="text-[14px] font-semibold uppercase
                              {{ app()->getLocale() === $locale ? 'text-[#00346f]' : 'text-[#64748B]' }}">
                        {{ $locale }}
                    </a>
                    @endforeach
                </div>
            </li>

            {{-- Mobile social icons --}}
            @if($socialNetworks->isNotEmpty())
                <li class="pt-3 border-t border-[#E2E8F0] mt-1">
                    <div class="flex items-center gap-4">
                        @foreach($socialNetworks as $network)
                            <a href="{{ $network['url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="text-[#00346f] hover:text-[#00B4D8] transition-colors"
                               aria-label="{{ ucfirst($network['platform']) }}">
                                @include('public.components.social-icon', ['platform' => $network['platform']])
                            </a>
                        @endforeach
                    </div>
                </li>
            @endif

            <li class="pt-3">
                <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), '/contacte') }}"
                   class="btn-primary w-full justify-center text-sm">
                    {{ __('messages.nav.cta') }}
                </a>
            </li>
        </ul>
    </div>
</header>
