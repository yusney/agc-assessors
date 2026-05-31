@php
    $items      = collect($section->setting('service_items', []));
    $serviceMap = collect($services ?? [])->keyBy(fn ($s) => $s->slug());
@endphp

@if($items->isNotEmpty() && $serviceMap->isNotEmpty())
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-24 md:py-32 bg-white rounded-[2rem]">
    <div class="text-center mb-20">
        <h2 class="font-headline text-[36px] md:text-[48px] text-[#0f172a] mb-6 tracking-tight font-semibold">
            {{ $section->localized('title') }}
        </h2>
        @if($section->localized('subtitle'))
            <p class="text-[20px] text-[#64748B] max-w-2xl mx-auto font-light">{{ $section->localized('subtitle') }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
        @foreach($items as $item)
            @php
                $service = $serviceMap->get(data_get($item, 'service_slug'));
                $icon    = data_get($item, 'icon', 'star');
            @endphp
            @if($service)
                <a href="{{ url('/serveis/' . $service->slug()) }}" class="group flex flex-col items-center text-center p-8 rounded-3xl hover:bg-[#f8fafc] transition-colors duration-500 cursor-pointer">
                    <div class="mb-6">
                        <span class="material-symbols-outlined text-[#0f172a] group-hover:text-[#00346f] transition-colors text-[40px]" style="font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 40">
                            {{ $icon }}
                        </span>
                    </div>
                    <h3 class="font-headline font-medium text-[20px] text-[#0f172a] mb-3">
                        {{ $service->name()->get(app()->getLocale()) }}
                    </h3>
                    <p class="text-[16px] text-[#64748B] font-light leading-relaxed line-clamp-3">
                        {!! strip_tags($service->description()->get(app()->getLocale())) !!}
                    </p>
                </a>
            @endif
        @endforeach
    </div>
</section>
@endif
