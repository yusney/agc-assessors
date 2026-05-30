<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Forms\Components\UrlPickerField;
use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = NewsModel::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Noticia';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Noticias';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->columnSpanFull()->schema([

                // ── Columna principal (2/3) ───────────────────────────────
                Section::make('Contenido')
                    ->columnSpan(3)
                    ->schema([
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),

                        Tabs::make('Traducciones')
                            ->tabs([
                                Tabs\Tab::make('Català')->schema([
                                    TextInput::make('title.ca')->label('Título (ca)')->required(),
                                    Textarea::make('excerpt.ca')->label('Extracto (ca)')->rows(3),
                                    RichEditor::make('body.ca')->label('Contenido (ca)'),
                                ]),
                                Tabs\Tab::make('Español')->schema([
                                    TextInput::make('title.es')->label('Título (es)'),
                                    Textarea::make('excerpt.es')->label('Extracto (es)')->rows(3),
                                    RichEditor::make('body.es')->label('Contenido (es)'),
                                ]),
                                Tabs\Tab::make('English')->schema([
                                    TextInput::make('title.en')->label('Título (en)'),
                                    Textarea::make('excerpt.en')->label('Extracto (en)')->rows(3),
                                    RichEditor::make('body.en')->label('Contenido (en)'),
                                ]),
                            ])->columnSpanFull(),
                    ]),

                // ── Sidebar (1/3) ─────────────────────────────────────────
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

                    Section::make('Publicación')
                        ->schema([
                            Toggle::make('published')->label('Publicada')->inline(false),
                            DateTimePicker::make('published_at')->label('Fecha de publicación'),
                        ]),

                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        TextInput::make('seo_title.ca')->label('Título SEO (ca)')->maxLength(70),
                        Textarea::make('seo_description.ca')->label('Descripción SEO (ca)')->maxLength(160)->rows(2),
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
                    ->label('Portada')
                    ->size(72),                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title.ca')->label('Título (ca)')->limit(50),
                Tables\Columns\IconColumn::make('published')->label('Publicada')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->label('Publicado')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => \AGC\Filament\Resources\NewsResource\Pages\ListNews::route('/'),
            'create' => \AGC\Filament\Resources\NewsResource\Pages\CreateNews::route('/create'),
            'edit'   => \AGC\Filament\Resources\NewsResource\Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
