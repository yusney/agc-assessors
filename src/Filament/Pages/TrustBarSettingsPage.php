<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrustBarSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Trust Bar';
    protected static ?string $title = 'Configuración de la Trust Bar';
    protected string $view = 'filament.pages.trust-bar-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'badges' => SiteSetting::get('trust_bar', [
                [
                    'icon'            => 'verified',
                    'title_ca'        => 'UNE 420001',
                    'title_es'        => 'UNE 420001',
                    'title_en'        => 'UNE 420001',
                    'subtitle_ca'     => 'Qualitat certificada',
                    'subtitle_es'     => 'Calidad certificada',
                    'subtitle_en'     => 'Certified quality',
                    'url'             => '',
                    'is_active'       => true,
                ],
                [
                    'icon'            => 'history',
                    'title_ca'        => '+25 anys',
                    'title_es'        => '+25 años',
                    'title_en'        => '+25 years',
                    'subtitle_ca'     => 'd\'experiència professional',
                    'subtitle_es'     => 'de experiencia profesional',
                    'subtitle_en'     => 'of professional experience',
                    'url'             => '',
                    'is_active'       => true,
                ],
            ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Badges de confianza')
                    ->description('Se muestran en una barra horizontal justo encima del footer. Cada badge puede tener un icono de Material Symbols o una imagen subida desde la biblioteca de medios.')
                    ->schema([
                        Repeater::make('badges')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icono (Material Symbols)')
                                    ->placeholder('verified')
                                    ->helperText('Si no se sube imagen, se usa este icono. Ej: verified, shield, award_star, stars')
                                    ->columnSpan(1),

                                CuratorPicker::make('image_media_id')
                                    ->label('O subir imagen')
                                    ->helperText('La imagen tiene prioridad sobre el icono.')
                                    ->columnSpan(1),

                                TextInput::make('title_ca')
                                    ->label('Título (Catalán)')
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('title_es')
                                    ->label('Título (Español)')
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('title_en')
                                    ->label('Título (English)')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('subtitle_ca')
                                    ->label('Subtítulo (Catalán)')
                                    ->columnSpan(1),
                                TextInput::make('subtitle_es')
                                    ->label('Subtítulo (Español)')
                                    ->columnSpan(1),
                                TextInput::make('subtitle_en')
                                    ->label('Subtítulo (English)')
                                    ->columnSpan(1),

                                TextInput::make('url')
                                    ->label('URL (opcional)')
                                    ->url()
                                    ->placeholder('https://www.une420001.com/')
                                    ->helperText('Si se completa, el badge será un enlace clickeable.')
                                    ->columnSpan(2),

                                Toggle::make('is_active')
                                    ->label('Visible')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Añadir badge')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('trust_bar', $this->form->getState()['badges'] ?? []);

        Notification::make()
            ->title('Configuración de la trust bar guardada')
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
