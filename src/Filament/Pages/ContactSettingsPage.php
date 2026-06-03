<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

final class ContactSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Página de contacto';

    protected static ?string $title = 'Configuración — Página de contacto';

    protected string $view = 'filament.pages.contact-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::get('contact_settings', []) ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── Textos de la página ───────────────────────────────────────
                Section::make('Textos de la página')
                    ->schema([
                        Tabs::make('Idiomas')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('title.ca')
                                            ->label('Título')
                                            ->required(),
                                        TextInput::make('subtitle.ca')
                                            ->label('Subtítulo'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('title.es')
                                            ->label('Título')
                                            ->required(),
                                        TextInput::make('subtitle.es')
                                            ->label('Subtítulo'),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label('Title')
                                            ->required(),
                                        TextInput::make('subtitle.en')
                                            ->label('Subtitle'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── Información de contacto visible ───────────────────────────
                Section::make('Información de contacto')
                    ->description('Datos que se muestran en la columna izquierda de la página de contacto.')
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->placeholder('Av. Pi i Margall 114, Caldes de Montbui'),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->placeholder('+34 93 862 61 00'),
                        TextInput::make('email_public')
                            ->label('Email público')
                            ->email()
                            ->placeholder('info@agcassessors.com'),
                        Tabs::make('Horario por idioma')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('hours.ca')
                                            ->label('Horario')
                                            ->placeholder('Dilluns–Divendres: 9h–18h'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('hours.es')
                                            ->label('Horario')
                                            ->placeholder('Lunes–Viernes: 9h–18h'),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('hours.en')
                                            ->label('Hours')
                                            ->placeholder('Monday–Friday: 9am–6pm'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── Destino de correos ────────────────────────────────────────
                Section::make('Destino de los correos')
                    ->description('A qué dirección llega cada formulario cuando un usuario lo envía.')
                    ->schema([
                        TextInput::make('contact_destination_email')
                            ->label('Email destino — Formulario de contacto')
                            ->email()
                            ->required()
                            ->placeholder('info@agcassessors.com')
                            ->helperText('Aquí llegarán los mensajes del formulario "Solicitar consulta".'),

                        TextInput::make('newsletter_notification_email')
                            ->label('Email destino — Aviso de nueva suscripción (opcional)')
                            ->email()
                            ->placeholder('marketing@agcassessors.com')
                            ->helperText('Si se rellena, recibirás un aviso cada vez que alguien se suscriba. Déjalo vacío para no recibir avisos.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('contact_settings', $this->form->getState());

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }
}
