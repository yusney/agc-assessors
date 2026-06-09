{{-- Recursive directory tree node. Renders each folder with optional expand/collapse. --}}
@foreach ($nodes as $node)
    @php
        $isActive = $this->activeDirectory === $node['path'];
        $isExpanded = $this->isExpanded($node['path']);
        $hasChildren = count($node['children']) > 0;
    @endphp

    <div>
        <div
            @class([
                'w-full group flex items-center gap-1.5 py-1 pr-2 rounded-md text-left text-sm transition-colors',
                'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 font-medium' => $isActive,
                'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60' => ! $isActive,
            ])
            style="padding-left: {{ ($depth * 0.75) + 0.5 }}rem;"
        >
            {{-- Expand/collapse chevron --}}
            @if ($hasChildren)
                <button
                    type="button"
                    wire:click.stop="toggleExpanded('{{ $node['path'] }}')"
                    class="shrink-0 inline-flex items-center justify-center w-4 h-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    <x-filament::icon
                        :icon="$isExpanded ? 'heroicon-s-chevron-down' : 'heroicon-s-chevron-right'"
                        class="w-3.5 h-3.5"
                    />
                </button>
            @else
                <span class="shrink-0 w-4"></span>
            @endif

            {{-- Folder icon --}}
            <button
                type="button"
                wire:click="setDirectory('{{ $node['path'] }}')"
                class="shrink-0 inline-flex items-center justify-center w-4 h-4"
            >
                <x-filament::icon
                    :icon="$isExpanded ? 'heroicon-s-folder-open' : 'heroicon-s-folder'"
                    @class([
                        'w-4 h-4',
                        'text-primary-500' => $isActive,
                        'text-primary-400 group-hover:text-primary-500' => ! $isActive,
                    ])
                />
            </button>

            {{-- Folder name + count --}}
            <button
                type="button"
                wire:click="setDirectory('{{ $node['path'] }}')"
                @class([
                    'flex-1 truncate text-left',
                    'font-medium' => $isActive,
                ])
            >
                {{ $node['name'] }}
            </button>

            {{-- Item count --}}
            <span class="shrink-0 text-xs tabular-nums text-gray-400 dark:text-gray-500">
                {{ $node['count'] }}
            </span>
        </div>

        {{-- Children --}}
        @if ($hasChildren && $isExpanded)
            <div class="flex flex-col gap-0.5">
                @include('filament.resources.curator.pages.partials.directory-tree', [
                    'nodes' => $node['children'],
                    'depth' => $depth + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
