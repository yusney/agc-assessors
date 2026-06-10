<header class="bg-white sticky top-0 z-[1100] border-b border-[#E2E8F0]"
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

        $navbarCta = SiteSetting::get('navbar_cta');
        $locale = app()->getLocale();

        // If configured, use that; otherwise fallback to translation + /area-client
        if ($navbarCta && !empty($navbarCta['url'])) {
            $ctaLabel  = $navbarCta['label_' . $locale] ?? $navbarCta['label_ca'] ?? '';
            $ctaUrl    = $navbarCta['url'];
            $ctaTarget = $navbarCta['target'] ?? '_self';
        } else {
            $ctaLabel  = __('messages.nav.cta');
            $ctaUrl    = LaravelLocalization::getLocalizedURL(app()->getLocale(), '/area-client');
            $ctaTarget = '_self';
        }
    @endphp
    <div class="flex justify-between items-center h-20 w-full px-6 md:px-8 max-w-[1280px] mx-auto">

        {{-- Brand --}}
        <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), '/') }}"
           class="flex items-center flex-shrink-0 mr-6">
            <img src="{{ asset('images/logo.webp') }}"
                 alt="AGC Assessors"
                 class="h-10 w-auto object-contain">
        </a>

        {{-- Desktop nav with overflow --}}
        <nav class="hidden md:flex items-center flex-1 min-w-0 mx-4" aria-label="Main navigation"
             x-data="{
                visibleCount: {{ $menuItems->count() }},
                moreOpen: false,
                calculateVisible() {
                    const container = this.$refs.navContainer;
                    const items = container.querySelectorAll('.nav-item');
                    const moreBtn = this.$el.querySelector('[x-ref=moreButton]');
                    
                    if (!items.length) return;
                    
                    // Phase 1: show all items, hide more button, measure
                    items.forEach(item => item.style.display = '');
                    if (moreBtn) moreBtn.style.display = 'none';
                    container.offsetHeight; // force reflow
                    
                    const containerWidth = container.offsetWidth;
                    
                    let totalWidth = 0;
                    let count = 0;
                    
                    for (let i = 0; i < items.length; i++) {
                        if (totalWidth + items[i].offsetWidth <= containerWidth) {
                            totalWidth += items[i].offsetWidth;
                            count++;
                        } else {
                            break;
                        }
                    }
                    
                    // If all items fit, done
                    if (count >= items.length) {
                        this.visibleCount = items.length;
                        return;
                    }
                    
                    // Phase 2: need more button — reserve its space and recalculate
                    if (moreBtn) {
                        moreBtn.style.display = '';
                        container.offsetHeight; // force reflow
                        const moreWidth = moreBtn.offsetWidth;
                        
                        totalWidth = 0;
                        count = 0;
                        for (let i = 0; i < items.length; i++) {
                            if (totalWidth + items[i].offsetWidth + moreWidth <= containerWidth) {
                                totalWidth += items[i].offsetWidth;
                                count++;
                            } else {
                                break;
                            }
                        }
                    }
                    
                    this.visibleCount = count;
                    
                    // Apply visibility
                    items.forEach((item, i) => {
                        item.style.display = i < this.visibleCount ? '' : 'none';
                    });
                }
             }"
             x-init="$nextTick(() => {
                 calculateVisible();
                 if (document.fonts) document.fonts.ready.then(() => calculateVisible());
                 setTimeout(() => calculateVisible(), 500);
             })"
             @resize.window.debounce="calculateVisible()">
            
            <div class="flex items-center min-w-0 w-full" x-ref="navContainer">
                @foreach($menuItems as $index => $item)
                    <div class="nav-item flex-shrink-0"
                         data-index="{{ $index }}">
                        @if($item->children->isNotEmpty())
                            <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                                <button @click="dropdownOpen = !dropdownOpen"
                                        class="flex items-center gap-1 nav-link whitespace-nowrap px-4">
                                    {{ $item->getTranslation('label', app()->getLocale()) }}
                                    <span class="material-symbols-outlined text-[16px]"
                                          :class="dropdownOpen ? 'rotate-180' : ''"
                                          style="transition: transform .2s">&#xe5cf;</span>
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
                               class="nav-link px-4 whitespace-nowrap">
                                {{ $item->getTranslation('label', app()->getLocale()) }}
                            </a>
                        @endif
                    </div>
                @endforeach
                
                {{-- More button --}}
                <div class="relative flex-shrink-0 px-2" 
                     x-ref="moreButton"
                     x-data="{ moreOpen: false }"
                     @click.outside="moreOpen = false">
                    <button @click="moreOpen = !moreOpen"
                            class="flex items-center gap-1 nav-link"
                            aria-label="{{ __('messages.nav.more_options') }}">
                        <span class="material-symbols-outlined text-[20px]">&#xe5d3;</span>
                    </button>
                    <div x-show="moreOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl
                                border border-[#E2E8F0] shadow-lg overflow-hidden z-50 py-2"
                         style="display: none;">
                        @foreach($menuItems as $index => $item)
                            <template x-if="{{ $index }} >= visibleCount">
                                @if($item->children->isNotEmpty())
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                                class="flex items-center justify-between w-full px-4 py-2.5 text-[14px] text-[#1E293B] hover:text-[#00346f] hover:bg-[#f9f9ff] transition-colors">
                                            <span>{{ $item->getTranslation('label', app()->getLocale()) }}</span>
                                            <span class="material-symbols-outlined text-[16px]" x-show="!subOpen">&#xe5cc;</span>
                                            <span class="material-symbols-outlined text-[16px]" x-show="subOpen">&#xe5cf;</span>
                                        </button>
                                        <div x-show="subOpen"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 -translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="bg-[#f9f9ff] py-1">
                                            @foreach($item->children as $child)
                                                <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $child->url_path) }}"
                                                   target="{{ $child->target }}"
                                                   class="block px-8 py-2 text-[13px] text-[#1E293B] hover:text-[#00346f] transition-colors">
                                                    {{ $child->getTranslation('label', app()->getLocale()) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $item->url_path) }}"
                                       target="{{ $item->target }}"
                                       class="block px-4 py-2.5 text-[14px] text-[#1E293B] hover:text-[#00346f] hover:bg-[#f9f9ff] transition-colors">
                                        {{ $item->getTranslation('label', app()->getLocale()) }}
                                    </a>
                                @endif
                            </template>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-3" id="navbar-actions">

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
                                <span class="material-symbols-outlined text-[20px]">&#xe5d3;</span>
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

            {{-- Search (desktop) --}}
            <div class="relative hidden md:flex items-center"
                 x-data="{ searchOpen: false, searchQuery: '' }"
                 @click.outside="searchOpen = false"
                 @keydown.escape.window="searchOpen = false">
                <button @click="searchOpen = !searchOpen; $nextTick(() => { if(searchOpen) $refs.searchInput.focus() })"
                        class="flex items-center justify-center text-[#00346f] hover:text-[#00B4D8] transition-colors duration-300 p-0.5"
                        :aria-expanded="searchOpen"
                        aria-label="{{ __('messages.nav.search') }}">
                    <span class="material-symbols-outlined text-[22px]" x-show="!searchOpen">&#xe8b6;</span>
                    <span class="material-symbols-outlined text-[22px]" x-show="searchOpen" x-cloak>&#xe5cd;</span>
                </button>

                <div x-show="searchOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute top-full right-0 mt-3 z-50">
                    <div class="bg-white rounded-2xl shadow-lg border border-[#E2E8F0] p-3">
                        <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2">
                            <input
                                x-ref="searchInput"
                                type="text"
                                name="q"
                                x-model="searchQuery"
                                placeholder="{{ __('messages.search.placeholder') }}"
                                class="w-64 px-4 py-2 rounded-xl border border-[#00346f]/30 bg-[#f9f9ff] text-[#1E293B]
                                       text-[14px] placeholder-[#94A3B8] focus:outline-none focus:border-[#00346f]
                                       focus:ring-2 focus:ring-[#00346f]/10 transition-all"
                            >
                            <button type="submit"
                                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#00346f]
                                           text-white hover:bg-[#00B4D8] transition-colors flex-shrink-0">
                                <span class="material-symbols-outlined text-[18px]">&#xe8b6;</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Language selector --}}
            <div class="relative hidden md:block" x-data="{ langOpen: false }" @click.outside="langOpen = false">
                <button @click="langOpen = !langOpen"
                        class="flex items-center gap-1.5 text-[#1E293B] hover:text-[#00346f]
                               text-[14px] font-medium uppercase tracking-wider transition-colors py-1 px-2">
                    {{ app()->getLocale() }}
                    <span class="material-symbols-outlined text-[16px]"
                          :class="langOpen ? 'rotate-180' : ''"
                          style="transition: transform .2s">&#xe5cf;</span>
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

            @if($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}"
               target="{{ $ctaTarget }}"
               @if($ctaTarget === '_blank') rel="noopener noreferrer" @endif
               class="hidden md:inline-flex btn-primary text-sm">
                {{ $ctaLabel }}
            </a>
            @endif

            {{-- Mobile burger --}}
            <button @click="open = !open"
                    class="md:hidden text-[#1E293B] hover:text-[#00346f] transition-colors"
                    :aria-expanded="open"
                    aria-label="{{ __('messages.nav.menu') }}">
                <span x-show="!open" class="material-symbols-outlined text-[28px]">&#xe5d2;</span>
                <span x-show="open"  class="material-symbols-outlined text-[28px]">&#xe5cd;</span>
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
                                    class="flex items-center justify-between w-full py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium font-headline transition-colors">
                                {{ $item->getTranslation('label', app()->getLocale()) }}
                                <span class="material-symbols-outlined text-[16px]"
                                      :class="mobDropdownOpen ? 'rotate-180' : ''"
                                      style="transition: transform .2s">&#xe5cf;</span>
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
                           class="block py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium font-headline transition-colors">
                            {{ $item->getTranslation('label', app()->getLocale()) }}
                        </a>
                    @endif
                </li>
            @endforeach

            {{-- Mobile search --}}
            <li class="pt-2">
                <a href="{{ route('search') }}"
                   class="flex items-center gap-2 py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium transition-colors">
                    <span class="material-symbols-outlined text-[20px]">&#xe8b6;</span>
                    {{ __('messages.nav.search') }}
                </a>
            </li>

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

            @if($ctaLabel && $ctaUrl)
            <li class="pt-3">
                <a href="{{ $ctaUrl }}"
                   target="{{ $ctaTarget }}"
                   @if($ctaTarget === '_blank') rel="noopener noreferrer" @endif
                   class="btn-primary w-full justify-center text-sm">
                    {{ $ctaLabel }}
                </a>
            </li>
            @endif
        </ul>
    </div>
</header>
