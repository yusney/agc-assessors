<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContactSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Formularios de contacto';

    protected static ?string $title = 'Configuración — Formularios de contacto';

    protected string $view = 'filament.pages.contact-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::get('contact_settings', [
            'contact_destination_email'   => '',
            'newsletter_destination_email' => '',
        ]) ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Destino de los correos')
                    ->description('Define a qué dirección de email llega cada formulario cuando un usuario lo envía.')
                    ->schema([
                        TextInput::make('contact_destination_email')
                            ->label('Email destino — Formulario de contacto')
                            ->email()
                            ->required()
                            ->placeholder('info@agcassessors.com')
                            ->helperText('Aquí llegarán los mensajes del formulario "Solicitar consulta".'),

                        TextInput::make('newsletter_notification_email')
                            ->label('Email destino — Notificación de nueva suscripción (opcional)')
                            ->email()
                            ->placeholder('marketing@agcassessors.com')
                            ->helperText('Si se rellena, se enviará un aviso cada vez que alguien se suscriba al newsletter. Déjalo vacío para no recibir avisos.'),
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
