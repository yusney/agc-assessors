<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Resources\OfficeResource\Pages\CreateOffice;
use AGC\Filament\Resources\OfficeResource\Pages\EditOffice;
use AGC\Filament\Resources\OfficeResource\Pages\ListOffices;
use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class OfficeResource extends Resource
{
    protected static ?string $model = EloquentOffice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return 'Oficina';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Oficinas';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Traducciones')->schema([
                Tabs::make('Idiomas')
                    ->tabs([
                        Tabs\Tab::make('Català')->schema([
                            TextInput::make('name.ca')->label('Nom (ca)')->required(),
                            Textarea::make('address.ca')->label('Adreça (ca)')->rows(2),
                            TextInput::make('city.ca')->label('Ciutat (ca)'),
                            Textarea::make('description.ca')->label('Descripció (ca)')->rows(4),
                        ]),
                        Tabs\Tab::make('Castellano')->schema([
                            TextInput::make('name.es')->label('Nombre (es)'),
                            Textarea::make('address.es')->label('Dirección (es)')->rows(2),
                            TextInput::make('city.es')->label('Ciudad (es)'),
                            Textarea::make('description.es')->label('Descripción (es)')->rows(4),
                        ]),
                        Tabs\Tab::make('English')->schema([
                            TextInput::make('name.en')->label('Name (en)'),
                            Textarea::make('address.en')->label('Address (en)')->rows(2),
                            TextInput::make('city.en')->label('City (en)'),
                            Textarea::make('description.en')->label('Description (en)')->rows(4),
                        ]),
                    ])->columnSpanFull(),
            ]),

            Section::make('Imatge i contacte')->schema([
                CuratorPicker::make('cover_media_id')
                    ->label('Imatge de portada')
                    ->nullable()
                    ->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('phone')->label('Telèfon')->nullable(),
                    TextInput::make('email')->label('Email')->email()->nullable(),
                    TextInput::make('lat')->label('Latitud')->numeric()->nullable(),
                    TextInput::make('lng')->label('Longitud')->numeric()->nullable(),
                ]),
                Toggle::make('is_active')->label('Activa')->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->getStateUsing(fn (EloquentOffice $record): string => $record->getTranslation('name', 'ca')),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciutat')
                    ->getStateUsing(fn (EloquentOffice $record): string => $record->getTranslation('city', 'ca')),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffices::route('/'),
            'create' => CreateOffice::route('/create'),
            'edit' => EditOffice::route('/{record}/edit'),
        ];
    }
}
