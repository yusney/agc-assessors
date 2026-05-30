<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
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
                Section::make('Principal')
                    ->schema([
                        TextInput::make('description')
                            ->label('Descripción corta')
                            ->helperText('Aparece bajo el logo en el footer'),
                        TextInput::make('phone')
                            ->label('Teléfono'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('address')
                            ->label('Dirección'),
                        TextInput::make('copyright')
                            ->label('Texto copyright')
                            ->default('© 2025 AGC Assessors. Tots els drets reservats.'),
                    ]),

                Section::make('Logos institucionales')
                    ->collapsible()
                    ->schema([
                        Repeater::make('institutional_logos')
                            ->hiddenLabel()
                            ->schema([
                                CuratorPicker::make('media_id')
                                    ->label('Imagen'),
                                TextInput::make('alt')
                                    ->label('Texto alternativo (accesibilidad)'),
                                TextInput::make('url')
                                    ->label('URL de destino (opcional)')
                                    ->url(),
                            ])
                            ->addActionLabel('Añadir logo')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                Section::make('Links adicionales en footer')
                    ->collapsible()
                    ->schema([
                        Repeater::make('extra_links')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('label')
                                    ->label('Texto del link'),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->url(),
                            ])
                            ->addActionLabel('Añadir link')
                            ->collapsible()
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
