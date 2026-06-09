<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator\Pages;

use AGC\Filament\Resources\Curator\Actions\CustomMultiUploadAction;
use AGC\Filament\Resources\Curator\CustomMediaResource;
use Awcodes\Curator\CuratorPlugin;
use Awcodes\Curator\Resources\Media\Pages\ListMedia;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomListMedia extends ListMedia
{
    public string $layoutView = 'grid';

    protected static string $resource = CustomMediaResource::class;

    protected string $view = 'filament.resources.curator.pages.custom-list-media';

    public ?string $activeDirectory = null;

    /** @var array<int, string> Folder paths that are expanded in the sidebar tree. */
    public array $expandedFolders = [];

    protected $listeners = [
        'changeLayoutView' => 'changeLayoutView',
        'layoutViewChanged' => '$refresh',
        'directoryChanged' => '$refresh',
    ];

    public function mount(): void
    {
        parent::mount();

        $this->layoutView = config('curator.resource.default_layout');
    }

    public function changeLayoutView(): void
    {
        $this->layoutView = $this->layoutView === 'list' ? 'grid' : 'list';
        $this->dispatch('layoutViewChanged', $this->layoutView);
    }

    public function setDirectory(string $directory): void
    {
        $this->activeDirectory = $directory === '' ? null : $directory;
        $this->dispatch('directoryChanged');

        // Auto-expand the path leading to the active directory so the user sees context.
        $this->expandedFolders[$this->activeDirectory ?? ''] = true;
        foreach (explode('/', (string) $this->activeDirectory) as $i => $segment) {
            $partial = implode('/', array_slice(explode('/', (string) $this->activeDirectory), 0, $i + 1));
            $this->expandedFolders[$partial] = true;
        }
    }

    /**
     * Delete the active directory from storage.
     * Refuses if the directory contains any media records or subdirectories —
     * the user must empty it first. This is intentionally non-recursive to
     * prevent accidental mass deletion.
     */
    public function deleteActiveDirectory(): void
    {
        if (! $this->activeDirectory) {
            return;
        }

        $disk = config('curator.default_disk', 'public');
        $storage = Storage::disk($disk);
        $path = $this->activeDirectory;

        // Guard: must exist
        if (! $storage->exists($path)) {
            \Filament\Notifications\Notification::make()
                ->title('La carpeta ya no existe')
                ->warning()
                ->send();

            $this->activeDirectory = null;
            $this->dispatch('directoryChanged');

            return;
        }

        // Guard: no subdirectories (excluding spatie/media-library's conversions dir)
        $userSubdirs = array_values(array_filter(
            $storage->directories($path),
            fn (string $d): bool => basename($d) !== 'conversions',
        ));

        if (count($userSubdirs) > 0) {
            \Filament\Notifications\Notification::make()
                ->title('La carpeta tiene subcarpetas')
                ->body('Borrá las subcarpetas primero antes de eliminar esta.')
                ->danger()
                ->send();

            return;
        }

        // Guard: no media records pointing here
        $mediaCount = \Awcodes\Curator\Models\Media::query()
            ->where('directory', $path)
            ->count();

        if ($mediaCount > 0) {
            \Filament\Notifications\Notification::make()
                ->title('La carpeta tiene archivos')
                ->body("Hay {$mediaCount} archivo(s) adentro. Elimínalos primero.")
                ->danger()
                ->send();

            return;
        }

        $storage->deleteDirectory($path);

        \Filament\Notifications\Notification::make()
            ->title('Carpeta eliminada')
            ->body($path)
            ->success()
            ->send();

        // Clean up expanded state and navigate back to root
        unset($this->expandedFolders[$path]);
        $this->activeDirectory = null;
        $this->dispatch('directoryChanged');
    }

    public function toggleExpanded(string $path): void
    {
        if (isset($this->expandedFolders[$path])) {
            unset($this->expandedFolders[$path]);
        } else {
            $this->expandedFolders[$path] = true;
        }
    }

    public function isExpanded(string $path): bool
    {
        return isset($this->expandedFolders[$path]);
    }

    public function getTitle(): string
    {
        $base = Str::headline(CuratorPlugin::get()->getPluralLabel());
        
        if ($this->activeDirectory) {
            return $base . ' — ' . Str::of($this->activeDirectory)->replace('/', ' › ')->title();
        }

        return $base;
    }

    /** @throws Exception */
    public function getHeaderActions(): array
    {
        return [
            Action::make('createDirectory')
                ->label('Nueva carpeta')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->modalHeading(fn (): string => $this->activeDirectory
                    ? 'Nueva carpeta dentro de '.Str::of($this->activeDirectory)->replace('/', ' › ')->title()
                    : 'Nueva carpeta en la raíz')
                ->modalSubmitActionLabel('Crear carpeta')
                ->form([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->placeholder('Ej: logos, team, noticias')
                        ->required()
                        ->maxLength(255)
                        ->rules([
                            'regex:/^[A-Za-z0-9_\- ]+$/',
                        ])
                        ->helperText('Letras, números, guiones y espacios. No usar / ni ..'),
                ])
                ->action(function (array $data): void {
                    $name = trim($data['name']);

                    $disk = config('curator.default_disk', 'public');
                    $storage = Storage::disk($disk);

                    $relativePath = $this->activeDirectory
                        ? $this->activeDirectory.'/'.$name
                        : $name;

                    if ($storage->exists($relativePath)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ya existe una carpeta con ese nombre')
                            ->danger()
                            ->send();

                        return;
                    }

                    $storage->makeDirectory($relativePath);

                    \Filament\Notifications\Notification::make()
                        ->title('Carpeta creada')
                        ->body($relativePath)
                        ->success()
                        ->send();

                    $this->dispatch('directoryChanged');
                }),
            Action::make('toggle-table-view')
                ->color('gray')
                ->label(fn (): string => $this->layoutView === 'grid'
                    ? trans('curator::tables.actions.toggle_table_list')
                    : trans('curator::tables.actions.toggle_table_grid'))
                ->icon(fn (): string => $this->layoutView === 'grid'
                    ? 'heroicon-s-queue-list'
                    : 'heroicon-s-squares-2x2')
                ->action(function ($livewire): void {
                    $livewire->dispatch('changeLayoutView');
                }),
            CustomMultiUploadAction::make('multiUpload')
                ->directory($this->activeDirectory),
            CreateAction::make()
                ->label(fn (): string => trans('filament-actions::create.single.label', ['label' => CuratorPlugin::get()->getLabel()]))
                ->mutateFormDataUsing(function (array $data): array {
                    $data['directory'] = $this->activeDirectory;

                    return $data;
                }),
            Action::make('deleteDirectory')
                ->label('Eliminar carpeta')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => $this->activeDirectory !== null)
                ->modalHeading(fn (): string => 'Eliminar carpeta')
                ->modalDescription(fn (): string => $this->activeDirectory
                    ? "Se eliminará la carpeta «{$this->activeDirectory}». Esta acción no se puede deshacer."
                    : '')
                ->modalSubmitActionLabel('Eliminar')
                ->requiresConfirmation()
                ->action(fn () => $this->deleteActiveDirectory()),
        ];
    }

    public function getTable(): Table
    {
        $table = parent::getTable();

        // Modify query to filter by active directory
        $table->modifyQueryUsing(function ($query) {
            if ($this->activeDirectory) {
                $query->where('directory', $this->activeDirectory);
            } else {
                $query->whereNull('directory');
            }
        });

        return $table;
    }

    /**
     * Build a recursive directory tree starting from the storage root.
     * Excludes the "conversions" subdirectory that spatie/media-library creates
     * for thumbnails — those are implementation details, not user folders.
     *
     * @return array<int, array{name: string, path: string, count: int, children: array}>
     */
    public function getDirectoryTree(?string $parent = null): array
    {
        $disk = config('curator.default_disk', 'public');
        $storage = Storage::disk($disk);

        $dirs = $storage->directories($parent ?? '');

        // Filter out noise directories created by media-library
        $dirs = array_values(array_filter($dirs, function (string $dir): bool {
            $basename = basename($dir);

            return $basename !== 'conversions';
        }));

        $tree = [];
        foreach ($dirs as $dir) {
            $children = $this->getDirectoryTree($dir);
            $count = \Awcodes\Curator\Models\Media::query()
                ->where('directory', $dir)
                ->count();

            $tree[] = [
                'name' => basename($dir),
                'path' => $dir,
                'count' => $count,
                'children' => $children,
            ];
        }

        usort($tree, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $tree;
    }

    /**
     * Suppress Filament's native header breadcrumbs — we render our own
     * (Raíz › Carpeta) in the page body via getFolderTrail().
     *
     * @return array<int, string>
     */
    public function getBreadcrumbs(): array
    {
        return [];
    }

    /**
     * Called from the drop zone JS when the user drops files onto the table area.
     * Opens the multi-upload modal so Curator's FileUpload handles the actual
     * storage, EXIF extraction, thumbnail generation, and DB record creation.
     */
    public function openUploadModal(): void
    {
        $this->mountAction('multiUpload');
    }

    public function getFolderTrail(): array
    {
        if (! $this->activeDirectory) {
            return [];
        }

        $parts = explode('/', $this->activeDirectory);
        $trail = [];
        $currentPath = '';

        foreach ($parts as $index => $part) {
            $currentPath = $currentPath === '' ? $part : $currentPath.'/'.$part;
            $trail[] = [
                'label' => Str::title($part),
                'path' => $currentPath,
                'isLast' => $index === count($parts) - 1,
            ];
        }

        return $trail;
    }
}
