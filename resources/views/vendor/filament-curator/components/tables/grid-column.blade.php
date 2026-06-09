@php
    $record = $getRecord();
    $isSvg = curator()->isSvg($record->ext);
@endphp

<div {{ $attributes->merge($getExtraAttributes())->class(['curator-grid-column flex flex-col h-full rounded-xl overflow-hidden']) }}>
    {{-- Thumbnail --}}
    <div @class([
        'relative flex-1 min-h-0 overflow-hidden bg-gray-100 dark:bg-gray-950/50',
        'checkered' => $isSvg,
    ])>
        <x-curator::display
            :item="$record"
            :src="$record->mediumUrl"
            :lazy="true"
            icon-classes="size-24"
            x-on:click="toggleSelectedRecord('{{ $record->id }}')"
            @class([
                'h-full w-full',
                'object-contain p-2' => $isSvg,
                'object-cover' => ! $isSvg,
            ])
        />
    </div>

    {{-- Footer: name + size, below the image --}}
    <div class="shrink-0 flex items-center justify-between gap-2 px-3 py-2 text-xs bg-white dark:bg-white/5 border-t border-gray-950/5 dark:border-white/10">
        <p class="truncate font-medium text-gray-700 dark:text-gray-200" title="{{ $record->pretty_name }}">
            {{ $record->pretty_name }}
        </p>
        <p class="shrink-0 tabular-nums text-gray-500 dark:text-gray-400">
            {{ curator()->sizeForHumans($record->size) }}
        </p>
    </div>
</div>
