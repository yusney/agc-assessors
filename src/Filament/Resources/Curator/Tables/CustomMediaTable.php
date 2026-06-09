<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator\Tables;

use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Resources\Media\Tables\MediaTable;
use Exception;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CustomMediaTable extends MediaTable
{
    /** @throws Exception */
    public static function configure(Table $table): Table
    {
        $table = parent::configure($table);

        // Get directories for filter
        $directories = self::getDirectoryOptions();

        return $table
            ->contentGrid(fn () => [
                'md' => 3,
                'lg' => 4,
                'xl' => 4,
            ])
            ->emptyStateIcon('heroicon-o-cloud-arrow-up')
            ->emptyStateHeading('Esta carpeta está vacía')
            ->emptyStateDescription('Subí imágenes con el botón "Subida múltiple" o arrastralas directamente a esta zona.')
            ->filters([
                SelectFilter::make('directory')
                    ->label('Carpeta')
                    ->options($directories)
                    ->searchable()
                    ->placeholder('Todas las carpetas'),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function getDirectoryOptions(): array
    {
        $directories = Media::query()
            ->select('directory')
            ->whereNotNull('directory')
            ->distinct()
            ->pluck('directory')
            ->toArray();

        $options = [];
        foreach ($directories as $dir) {
            if ($dir) {
                $options[$dir] = Str::of($dir)->replace('/', ' › ')->title()->toString();
            }
        }

        ksort($options);

        return $options;
    }
}
