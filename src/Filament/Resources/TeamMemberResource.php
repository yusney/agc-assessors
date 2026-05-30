<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Infrastructure\Persistence\Eloquent\Models\TeamMemberModel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMemberModel::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return 'Miembro del equipo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Equipo';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->columnSpanFull()->schema([

                Section::make('Información personal')
                    ->columnSpan(3)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),

                        Tabs::make('Traducciones')
                            ->tabs([
                                Tabs\Tab::make('Català')->schema([
                                    TextInput::make('role.ca')->label('Cargo (ca)')->required(),
                                    Textarea::make('bio.ca')->label('Biografía (ca)')->rows(4),
                                ]),
                                Tabs\Tab::make('Español')->schema([
                                    TextInput::make('role.es')->label('Cargo (es)'),
                                    Textarea::make('bio.es')->label('Biografía (es)')->rows(4),
                                ]),
                                Tabs\Tab::make('English')->schema([
                                    TextInput::make('role.en')->label('Cargo (en)'),
                                    Textarea::make('bio.en')->label('Biografía (en)')->rows(4),
                                ]),
                            ])->columnSpanFull(),
                    ]),

                Grid::make(1)->columnSpan(1)->schema([

                    Section::make('Fotografía')
                        ->schema([
                            CuratorPicker::make('photo_media_id')
                                ->hiddenLabel()
                                ->buttonLabel('Seleccionar foto')
                                ->constrained()
                                ->nullable()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Configuración')
                        ->schema([
                            TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                            Toggle::make('active')->label('Activo')->default(true)->inline(false),
                        ]),

                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('photo_media_id')
                    ->label('Foto')
                    ->size(72),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role.ca')->label('Cargo (ca)'),
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
            'index'  => \AGC\Filament\Resources\TeamMemberResource\Pages\ListTeamMembers::route('/'),
            'create' => \AGC\Filament\Resources\TeamMemberResource\Pages\CreateTeamMember::route('/create'),
            'edit'   => \AGC\Filament\Resources\TeamMemberResource\Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
