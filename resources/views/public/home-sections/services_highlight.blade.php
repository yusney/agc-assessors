@php
    $items      = collect($section->setting('service_items', []));
    $serviceMap = collect($services ?? [])->keyBy(fn ($s) => $s->slug()->value());
    $columns    = (int) $section->setting('columns', 3);
    $gridClass  = match ($columns) {
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-6',
    };

    // Build a server-side array of service modal payloads, encoded as JSON for
    // safe consumption by Alpine. Each entry is keyed by the service slug so
    // the click handler can do `modalData[slug]` without interpolating any
    // string into a JS expression (which is the XSS risk we are removing).
    $modalData = $items->mapWithKeys(function ($item) use ($serviceMap) {
        $service = $serviceMap->get(data_get($item, 'service_slug'));
        if (! $service) {
            return [];
        }
        return [$service->slug()->value() => [
            'name'        => $service->name()->get(app()->getLocale()),
            'description' => strip_tags(Str::limit($service->description()->get(app()->getLocale()), 200)),
            'icon'        => data_get($item, 'icon', 'star'),
            'coverUrl'    => $service->coverUrl() ?? '',
            'url'         => url('/serveis/' . $service->slug()->value()),
        ]];
    })->all();
@endphp

@if($items->isNotEmpty() && $serviceMap->isNotEmpty())
<section
    class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-20 bg-white rounded-[2rem]"
    x-data='{
        modalOpen: false,
        modalName: "",
        modalDescription: "",
        modalIcon: "star",
        modalCoverUrl: "",
        modalUrl: "",
        modalData: @json($modalData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        open(slug) {
            const o = this.modalData[slug];
            if (!o) return;
            this.modalName = o.name;
            this.modalDescription = o.description;
            this.modalIcon = o.icon;
            this.modalCoverUrl = o.coverUrl;
            this.modalUrl = o.url;
            this.modalOpen = true;
        }
    }'
>
    <div class="text-center mb-12">
        <h2 class="font-headline text-[36px] md:text-[48px] text-[#0f172a] mb-6 tracking-tight font-semibold">
            {{ $section->localized('title') }}
        </h2>
        @if($section->localized('subtitle'))
            <p class="text-[20px] text-[#64748B] max-w-2xl mx-auto font-light">{{ $section->localized('subtitle') }}</p>
        @endif
    </div>

    <div class="grid {{ $gridClass }}">
        @foreach($items as $item)
            @php $service = $serviceMap->get(data_get($item, 'service_slug')); @endphp
            @if($service)
                <div
                    class="group flex flex-col items-center text-center p-8 rounded-3xl hover:bg-[#f8fafc] transition-colors duration-500 cursor-pointer"
                    @click="open('{{ $service->slug()->value() }}')"
                >
                    <div class="mb-6">
                        <span class="material-symbols-outlined text-[#0f172a] group-hover:text-[#00346f] transition-colors text-[40px]" style="font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 40">
                            {{ data_get($item, 'icon', 'star') }}
                        </span>
                    </div>
                    <h3 class="font-headline font-medium text-[20px] text-[#0f172a] mb-3">
                        {{ $service->name()->get(app()->getLocale()) }}
                    </h3>
                    <p class="text-[16px] text-[#64748B] font-light leading-relaxed line-clamp-3">
                        {!! strip_tags($service->description()->get(app()->getLocale())) !!}
                    </p>
                </div>
            @endif
        @endforeach
    </div>

    @include('public.components.service-modal')
</section>
@endif
