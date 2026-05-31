<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Filament\Forms\Components\UrlPickerField;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FooterSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Footer';
    protected static ?string $title = 'Configuración del Footer';
    protected string $view = 'filament.pages.footer-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::get('footer', []) ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Información del footer')
                    ->schema([
                        TextInput::make('description.ca')
                            ->label('Descripción (Catalán)')
                            ->helperText('Texto bajo el logo en la versión catalana'),
                        TextInput::make('description.es')
                            ->label('Descripción (Español)')
                            ->helperText('Texto bajo el logo en la versión española'),
                        TextInput::make('description.en')
                            ->label('Descripción (English)')
                            ->helperText('Texto bajo el logo en la versión inglesa'),
                        TextInput::make('phone')
                            ->label('Teléfono'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('address')
                            ->label('Dirección'),
                        TextInput::make('copyright')
                            ->label('Texto copyright')
                            ->default('© ' . date('Y') . ' AGC Assessors. Tots els drets reservats.'),
                    ]),

                Section::make('Links de navegación')
                    ->schema([
                        Repeater::make('nav_links')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('label_ca')
                                    ->label('Texto (Catalán)')
                                    ->required(),
                                TextInput::make('label_es')
                                    ->label('Texto (Español)')
                                    ->required(),
                                TextInput::make('label_en')
                                    ->label('Texto (English)')
                                    ->required(),
                                UrlPickerField::make('url')
                                    ->label('URL')
                                    ->required(),
                            ])
                            ->addActionLabel('Añadir link')
                            ->collapsible()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Links legales')
                    ->schema([
                        Repeater::make('legal_links')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('label_ca')
                                    ->label('Texto (Catalán)')
                                    ->required(),
                                TextInput::make('label_es')
                                    ->label('Texto (Español)')
                                    ->required(),
                                TextInput::make('label_en')
                                    ->label('Texto (English)')
                                    ->required(),
                                UrlPickerField::make('url')
                                    ->label('URL')
                                    ->required(),
                            ])
                            ->addActionLabel('Añadir link legal')
                            ->collapsible()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('footer', $this->form->getState());

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }
}
