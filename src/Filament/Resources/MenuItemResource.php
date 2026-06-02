<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Forms\Components\UrlPickerField;
use AGC\Filament\Resources\MenuItemResource\Pages;
use AGC\Infrastructure\Persistence\Eloquent\Models\MenuItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return 'Elemento de menú';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Elementos de menú';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)
                ->columnSpanFull()
                ->schema([
                    // ── Main content (3/4) ──────────────────────────────
                    Section::make('Detalles')
                        ->columnSpan(3)
                        ->schema([
                            Grid::make(2)->schema([
                                UrlPickerField::make('url_path')
                                    ->label('Ruta URL')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('/serveis'),

                                TextInput::make('route_name')
                                    ->label('Nombre de ruta')
                                    ->maxLength(255)
                                    ->nullable(),

                                Select::make('target')
                                    ->label('Objetivo')
                                    ->options([
                                        '_self' => 'Misma ventana',
                                        '_blank' => 'Nueva ventana',
                                    ])
                                    ->default('_self')
                                    ->required(),

                                TextInput::make('icon')
                                    ->label('Icono')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->placeholder('heroicon-o-...'),

                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Select::make('parent_id')
                                    ->label('Elemento padre')
                                    ->relationship('parent', 'label')
                                    ->getOptionLabelFromRecordUsing(fn (MenuItem $record): string => $record->getTranslation('label', 'ca') ?? $record->label)
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Sin padre (nivel superior)'),
                            ]),
                        ]),

                    // ── Sidebar (1/4) ────────────────────────────────────
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Section::make('Estado')
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Activo')
                                        ->default(true)
                                        ->inline(false),
                                ]),

                            Section::make('Traducciones')
                                ->schema([
                                    Tabs::make('Idiomas')
                                        ->tabs([
                                            Tabs\Tab::make('Català')->schema([
                                                TextInput::make('label.ca')
                                                    ->label('Etiqueta (ca)')
                                                    ->required(),
                                            ]),
                                            Tabs\Tab::make('Español')->schema([
                                                TextInput::make('label.es')
                                                    ->label('Etiqueta (es)'),
                                            ]),
                                            Tabs\Tab::make('English')->schema([
                                                TextInput::make('label.en')
                                                    ->label('Etiqueta (en)'),
                                            ]),
                                        ])->columnSpanFull(),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('label.ca')
                    ->label('Etiqueta (ca)')
                    ->limit(50),
                Tables\Columns\TextColumn::make('url_path')
                    ->label('Ruta URL')
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->reorderable('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
