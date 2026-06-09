<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Resources\ServiceResource\Pages\CreateService;
use AGC\Filament\Resources\ServiceResource\Pages\EditService;
use AGC\Filament\Resources\ServiceResource\Pages\ListServices;
use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
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

class ServiceResource extends Resource
{
    protected static ?string $model = ServiceModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Servicio';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Servicios';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->columnSpanFull()->schema([

                Section::make('Detalles del servicio')
                    ->columnSpan(3)
                    ->schema([
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),

                        Tabs::make('Traducciones')
                            ->tabs([
                                Tabs\Tab::make('Català')->schema([
                                    TextInput::make('name.ca')->label('Nombre (ca)')->required(),
                                    RichEditor::make('description.ca')->label('Descripción (ca)')->plugins([AttachCuratorMediaPlugin::make()])->enableToolbarButtons(['attachCuratorMedia']),
                                ]),
                                Tabs\Tab::make('Español')->schema([
                                    TextInput::make('name.es')->label('Nombre (es)'),
                                    RichEditor::make('description.es')->label('Descripción (es)')->plugins([AttachCuratorMediaPlugin::make()])->enableToolbarButtons(['attachCuratorMedia']),
                                ]),
                                Tabs\Tab::make('English')->schema([
                                    TextInput::make('name.en')->label('Nombre (en)'),
                                    RichEditor::make('description.en')->label('Descripción (en)')->plugins([AttachCuratorMediaPlugin::make()])->enableToolbarButtons(['attachCuratorMedia']),
                                ]),
                            ])->columnSpanFull(),
                    ]),

                Grid::make(1)->columnSpan(1)->schema([

                    Section::make('Imagen de portada')
                        ->schema([
                            CuratorPicker::make('cover_media_id')
                                ->hiddenLabel()
                                ->buttonLabel('Seleccionar imagen')
                                ->constrained()
                                ->nullable()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Configuración')
                        ->schema([
                            TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                            Toggle::make('active')->label('Activo')->default(true)->inline(false),
                        ]),

                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        Tabs::make('SEO por idioma')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('seo_title.ca')->label('Título SEO (ca)')->maxLength(70),
                                        Textarea::make('seo_description.ca')->label('Descripción SEO (ca)')->maxLength(160)->rows(2),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('seo_title.es')->label('Título SEO (es)')->maxLength(70),
                                        Textarea::make('seo_description.es')->label('Descripción SEO (es)')->maxLength(160)->rows(2),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('seo_title.en')->label('Título SEO (en)')->maxLength(70),
                                        Textarea::make('seo_description.en')->label('Descripción SEO (en)')->maxLength(160)->rows(2),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        TextInput::make('seo_canonical')->label('URL canónica')->maxLength(500),
                    ]),

                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('cover_media_id')
                    ->label('Imagen')
                    ->size(72),
                Tables\Columns\TextColumn::make('slug')->sortable(),
                Tables\Columns\TextColumn::make('name.ca')->label('Nombre (ca)')->limit(40),
                Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
                Tables\Columns\IconColumn::make('active')->label('Activo')->boolean(),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
