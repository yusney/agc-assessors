<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialMediaSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Navbar';
    protected static ?string $title = 'Configuración del Navbar';
    protected string $view = 'filament.pages.social-media-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'networks' => SiteSetting::get('social_networks', []) ?? [],
            'cta'      => SiteSetting::get('navbar_cta', [
                'label_ca' => 'Àrea de client',
                'label_es' => 'Área de cliente',
                'label_en' => 'Client Area',
                'url'      => '/area-client',
                'target'   => '_self',
            ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Botón CTA del navbar')
                    ->description('Botón de llamada a la acción que aparece a la derecha del navbar. Configurá el texto en cada idioma, la URL de destino y si abre en la misma pestaña o en una nueva.')
                    ->schema([
                        TextInput::make('cta.label_ca')
                            ->label('Texto (Catalán)')
                            ->required(),
                        TextInput::make('cta.label_es')
                            ->label('Texto (Español)')
                            ->required(),
                        TextInput::make('cta.label_en')
                            ->label('Texto (English)')
                            ->required(),
                        TextInput::make('cta.url')
                            ->label('URL')
                            ->required()
                            ->placeholder('/area-client'),
                        Select::make('cta.target')
                            ->label('Abrir enlace')
                            ->options([
                                '_self'  => 'Misma pestaña',
                                '_blank' => 'Nueva pestaña',
                            ])
                            ->default('_self')
                            ->native(false),
                    ]),

                Section::make('Redes sociales en el navbar')
                    ->description('Se muestran máximo 3 iconos directamente. Si hay más, aparece un botón "⋯" que despliega el resto. El orden se gestiona arrastrando.')
                    ->schema([
                        Repeater::make('networks')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('platform')
                                    ->label('Red social')
                                    ->required()
                                    ->options([
                                        'linkedin'  => 'LinkedIn',
                                        'twitter'   => 'X (Twitter)',
                                        'instagram' => 'Instagram',
                                        'facebook'  => 'Facebook',
                                        'youtube'   => 'YouTube',
                                        'tiktok'    => 'TikTok',
                                        'whatsapp'  => 'WhatsApp',
                                    ])
                                    ->columnSpan(1),

                                TextInput::make('url')
                                    ->label('URL del perfil')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://www.linkedin.com/company/...')
                                    ->columnSpan(2),

                                Toggle::make('is_active')
                                    ->label('Visible')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Añadir red social')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('social_networks', $this->form->getState()['networks'] ?? []);
        SiteSetting::set('navbar_cta', $this->form->getState()['cta'] ?? []);

        Notification::make()
            ->title('Configuración del navbar guardada')
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
