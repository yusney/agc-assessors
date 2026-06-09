<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Resources\PageResource\Pages\CreatePage;
use AGC\Filament\Resources\PageResource\Pages\EditPage;
use AGC\Filament\Resources\PageResource\Pages\ListPages;
use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
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

class PageResource extends Resource
{
    protected static ?string $model = PageModel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Página';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Páginas';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)
                ->columnSpanFull()
                ->schema([
                    // ── Main content (3/4) ──────────────────────────────
                    Section::make('Contenido')
                        ->columnSpan(3)
                        ->schema([
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            Tabs::make('Traducciones')
                                ->tabs([
                                    Tabs\Tab::make('Català')->schema([
                                        TextInput::make('title.ca')->label('Título (ca)')->required(),
                                         RichEditor::make('content.ca')->label('Contenido (ca)')->plugins([AttachCuratorMediaPlugin::make()]),
                                    ]),
                                    Tabs\Tab::make('Español')->schema([
                                        TextInput::make('title.es')->label('Título (es)'),
                                         RichEditor::make('content.es')->label('Contenido (es)')->plugins([AttachCuratorMediaPlugin::make()]),
                                    ]),
                                    Tabs\Tab::make('English')->schema([
                                        TextInput::make('title.en')->label('Título (en)'),
                                         RichEditor::make('content.en')->label('Contenido (en)')->plugins([AttachCuratorMediaPlugin::make()]),
                                    ]),
                                ])->columnSpanFull(),
                        ]),

                    // ── Sidebar (1/4) ────────────────────────────────────
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Section::make('Publicación')
                                ->schema([
                                    Toggle::make('published')->label('Publicada')->inline(false),
                                ]),

                            Section::make('SEO')
                                ->collapsible()
                                ->schema([
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
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title.ca')->label('Título (ca)')->limit(50),
                Tables\Columns\IconColumn::make('published')->label('Publicada')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
