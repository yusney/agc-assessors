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

            Section::make('Horaris i cobertura (SEO local)')->schema([
                Tabs::make('Idiomes horaris')
                    ->tabs([
                        Tabs\Tab::make('Català')->schema([
                            Textarea::make('opening_hours.ca')
                                ->label('Horari (ca)')
                                ->helperText('Una línia per dia. Ex: "dilluns a dijous: 9:00–18:00"')
                                ->rows(3),
                            Textarea::make('service_area.ca')
                                ->label('Pobles / zones que atenem (ca)')
                                ->helperText('Un per línia. Ex: "Sentmenat", "Palau-solità i Plegamans"')
                                ->rows(4),
                            TextInput::make('image_alt.ca')
                                ->label('Text alternatiu imatge (ca)')
                                ->maxLength(125)
                                ->helperText('Descriu la imatge per a lectors de pantalla i SEO.'),
                            TextInput::make('slug.ca')
                                ->label('Slug URL (ca)')
                                ->helperText('Buit = auto-generat des del nom de la ciutat. Ex: "caldes-de-montbui"')
                                ->dehydrated(),
                            TextInput::make('manager_name.ca')
                                ->label('Responsable - Nom (ca)')
                                ->maxLength(120),
                            TextInput::make('manager_role.ca')
                                ->label('Responsable - Càrrec (ca)')
                                ->maxLength(120)
                                ->placeholder('Ex: "Assessor fiscal sènior"'),
                            Textarea::make('manager_bio.ca')
                                ->label('Responsable - Presentació (ca)')
                                ->helperText('1-2 frases. Es mostra a la pàgina individual de l\'oficina.')
                                ->rows(3),
                        ]),
                        Tabs\Tab::make('Castellano')->schema([
                            Textarea::make('opening_hours.es')
                                ->label('Horario (es)')
                                ->helperText('Una línea por día. Ej: "lunes a jueves: 9:00–18:00"')
                                ->rows(3),
                            Textarea::make('service_area.es')
                                ->label('Pueblos / zonas que atendemos (es)')
                                ->helperText('Uno por línea. Ej: "Sentmenat", "Palau-solità i Plegamans"')
                                ->rows(4),
                            TextInput::make('image_alt.es')
                                ->label('Texto alternativo imagen (es)')
                                ->maxLength(125)
                                ->helperText('Describe la imagen para lectores de pantalla y SEO.'),
                            TextInput::make('slug.es')
                                ->label('Slug URL (es)')
                                ->helperText('Vacío = auto-generado desde el nombre de la ciudad. Ej: "caldes-de-montbui"')
                                ->dehydrated(),
                            TextInput::make('manager_name.es')
                                ->label('Responsable - Nombre (es)')
                                ->maxLength(120),
                            TextInput::make('manager_role.es')
                                ->label('Responsable - Cargo (es)')
                                ->maxLength(120)
                                ->placeholder('Ej: "Asesor fiscal senior"'),
                            Textarea::make('manager_bio.es')
                                ->label('Responsable - Presentación (es)')
                                ->helperText('1-2 frases. Se muestra en la página individual de la oficina.')
                                ->rows(3),
                        ]),
                        Tabs\Tab::make('English')->schema([
                            Textarea::make('opening_hours.en')
                                ->label('Opening hours (en)')
                                ->helperText('One line per day. Ex: "Monday to Thursday: 9:00–18:00"')
                                ->rows(3),
                            Textarea::make('service_area.en')
                                ->label('Towns / areas served (en)')
                                ->helperText('One per line. Ex: "Sentmenat", "Palau-solità i Plegamans"')
                                ->rows(4),
                            TextInput::make('image_alt.en')
                                ->label('Image alt text (en)')
                                ->maxLength(125)
                                ->helperText('Describe the image for screen readers and SEO.'),
                            TextInput::make('slug.en')
                                ->label('URL slug (en)')
                                ->helperText('Empty = auto-generated from the city name. Ex: "caldes-de-montbui"')
                                ->dehydrated(),
                            TextInput::make('manager_name.en')
                                ->label('Manager - Name (en)')
                                ->maxLength(120),
                            TextInput::make('manager_role.en')
                                ->label('Manager - Role (en)')
                                ->maxLength(120)
                                ->placeholder('Ex: "Senior tax advisor"'),
                            Textarea::make('manager_bio.en')
                                ->label('Manager - Bio (en)')
                                ->helperText('1-2 sentences. Shown on the office individual page.')
                                ->rows(3),
                        ]),
                    ])->columnSpanFull(),
            ])->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telèfon')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
                Tables\Columns\TextColumn::make('lat')
                    ->label('Lat')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lng')
                    ->label('Lng')
                    ->toggleable(isToggledHiddenByDefault: true),
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
