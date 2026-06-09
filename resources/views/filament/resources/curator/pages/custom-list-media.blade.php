@php
    $directories = $this->getDirectoryTree();
    $breadcrumbs = $this->getFolderTrail();
@endphp

<x-filament-panels::page>
    {{-- Toolbar: breadcrumbs --}}
    <div class="flex flex-wrap items-center gap-2 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        {{-- Root --}}
        <button
            wire:click="setDirectory('')"
            @class([
                'flex items-center gap-1 text-sm font-medium transition-colors',
                'text-gray-500 hover:text-primary-600' => $this->activeDirectory,
                'text-primary-600' => ! $this->activeDirectory,
            ])
        >
            <x-filament::icon
                icon="heroicon-o-folder-open"
                class="w-5 h-5"
            />
            Raíz
        </button>

        {{-- Breadcrumb path --}}
        @foreach ($breadcrumbs as $crumb)
            <x-filament::icon
                icon="heroicon-o-chevron-right"
                class="w-4 h-4 text-gray-400"
            />
            <button
                wire:click="setDirectory('{{ $crumb['path'] }}')"
                @class([
                    'text-sm font-medium transition-colors',
                    'text-gray-500 hover:text-primary-600' => ! $crumb['isLast'],
                    'text-primary-600' => $crumb['isLast'],
                ])
            >
                {{ $crumb['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Two-column layout: folders tree + files table --}}
    <div class="mt-4 flex flex-col gap-4 lg:flex-row">
        {{-- Folders tree --}}
        <aside class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-2 lg:w-64 lg:shrink-0 lg:sticky lg:top-4 lg:self-start">
            <div class="flex items-center justify-between px-2 py-1.5">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Carpetas
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ collect($directories)->sum(fn ($n) => 1 + collect($n['children'])->sum(fn ($c) => 1 + collect($c['children'])->sum(fn ($gc) => 1))) }}
                </span>
            </div>

            {{-- Root entry --}}
            <button
                wire:click="setDirectory('')"
                @class([
                    'w-full group flex items-center gap-1.5 px-2 py-1.5 rounded-md text-left text-sm transition-colors',
                    'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300 font-medium' => $this->activeDirectory === null,
                    'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60' => $this->activeDirectory !== null,
                ])
            >
                <x-filament::icon icon="heroicon-s-folder-open" class="w-4 h-4 shrink-0" />
                <span class="flex-1 truncate font-medium">Raíz</span>
            </button>

            @if (count($directories) > 0)
                <nav class="flex flex-col gap-0.5">
                    @include('filament.resources.curator.pages.partials.directory-tree', [
                        'nodes' => $directories,
                        'depth' => 0,
                    ])
                </nav>
            @endif
        </aside>

        {{-- Files table/grid (drop zone for files) --}}
        <div id="media-drop-zone" class="min-w-0 relative transition-colors rounded-lg">
            {{ $this->table }}
        </div>
    </div>

    {{-- Drop zone JS: open multi-upload modal AND pre-load the dropped files --}}
    @push('scripts')
        <script>
            (function () {
                if (window.__mediaDropZoneBound) return;
                window.__mediaDropZoneBound = true;

                function getLivewireId() {
                    const zone = document.getElementById('media-drop-zone');
                    if (!zone) return null;
                    const host = zone.closest('[wire\\:id]');
                    return host ? host.getAttribute('wire:id') : null;
                }

                /**
                 * Inject dropped files into the Curator upload modal's FilePond input
                 * so the user only has to drag once. Polls the DOM for up to 3s waiting
                 * for the modal to mount its hidden file input.
                 */
                function injectFilesIntoUploadModal(files) {
                    const start = Date.now();
                    const poll = setInterval(function () {
                        // Look for the file input inside an open Filament modal.
                        // Curator's Uploader uses Filepond, which renders the input with name="filepond".
                        const input = document.querySelector('dialog[open] input[type="file"], .fi-modal-window input[type="file"]');
                        if (input) {
                            clearInterval(poll);
                            try {
                                const dt = new DataTransfer();
                                for (const f of files) dt.items.add(f);
                                input.files = dt.files;
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            } catch (err) {
                                // Browser may block programmatic file assignment — fall back to leaving the modal open.
                                console.warn('[media-drop-zone] could not inject files:', err);
                            }
                            return;
                        }
                        if (Date.now() - start > 3000) {
                            clearInterval(poll);
                        }
                    }, 100);
                }

                document.addEventListener('dragover', function (e) {
                    const zone = e.target.closest('#media-drop-zone');
                    if (!zone) return;
                    e.preventDefault();
                    zone.classList.add('media-drop-active');
                });

                document.addEventListener('dragleave', function (e) {
                    const zone = e.target.closest('#media-drop-zone');
                    if (!zone) return;
                    if (e.relatedTarget && zone.contains(e.relatedTarget)) return;
                    zone.classList.remove('media-drop-active');
                });

                document.addEventListener('drop', function (e) {
                    const zone = e.target.closest('#media-drop-zone');
                    if (!zone) return;
                    e.preventDefault();
                    zone.classList.remove('media-drop-active');

                    const files = e.dataTransfer && e.dataTransfer.files;
                    if (!files || files.length === 0) return;

                    const wireId = getLivewireId();
                    if (!wireId) return;
                    const component = window.Livewire.find(wireId);
                    if (!component) return;

                    // Snapshot the FileList into a real Array (DataTransfer is invalidated
                    // by the next event tick, so we can't keep a reference to it).
                    const snapshot = Array.from(files);
                    // Stash for the post-mount poll to pick up.
                    window.__mediaDropPendingFiles = snapshot;

                    component.call('openUploadModal');
                    injectFilesIntoUploadModal(snapshot);
                });
            })();
        </script>
    @endpush
</x-filament-panels::page>
