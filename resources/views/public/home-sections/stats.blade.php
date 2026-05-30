@php $stats = collect($section->setting('stats', [])); @endphp

@if($stats->isNotEmpty())
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-10 md:py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white rounded-[2rem] border border-[#E2E8F0]/50 p-8 md:p-10 shadow-[0_2px_20px_-4px_rgba(0,0,0,0.05)]">
        @foreach($stats as $stat)
            <div class="text-center md:text-left">
                <p class="font-headline text-[44px] md:text-[56px] leading-none text-[#00346f] font-semibold">{{ data_get($stat, 'value') }}</p>
                <p class="mt-3 text-[16px] text-[#64748B] font-light">{{ data_get($stat, 'label.' . app()->getLocale(), data_get($stat, 'label.ca')) }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif
