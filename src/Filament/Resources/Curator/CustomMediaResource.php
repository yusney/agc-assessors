<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator;

use AGC\Filament\Resources\Curator\Pages\CustomCreateMedia;
use AGC\Filament\Resources\Curator\Pages\CustomEditMedia;
use AGC\Filament\Resources\Curator\Tables\CustomMediaTable;
use Awcodes\Curator\Resources\Media\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\ListMedia;
use Exception;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomMediaResource extends MediaResource
{
    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'media';
    }
    /** @throws Exception */
    public static function form(Schema $schema): Schema
    {
        return CustomMediaForm::configure($schema);
    }

    /** @throws Exception */
    public static function table(Table $table): Table
    {
        return CustomMediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CustomCreateMedia::route('/create'),
            'edit' => CustomEditMedia::route('/{record}/edit'),
        ];
    }
}
