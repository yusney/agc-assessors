<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

final class TrustBarSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Trust Bar';
    protected static ?string $title = 'Configuración de la Trust Bar';
    protected string $view = 'filament.pages.trust-bar-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    private const DEFAULT_BADGES = [
        [
            'sort_order'     => 1,
            'icon'           => 'verified',
            'image_media_id' => null,
            'url'            => '',
            'is_active'      => true,
            'title_ca'       => 'UNE 420001',
            'subtitle_ca'    => 'Qualitat certificada',
            'title_es'       => 'UNE 420001',
            'subtitle_es'    => 'Calidad certificada',
            'title_en'       => 'UNE 420001',
            'subtitle_en'    => 'Certified quality',
        ],
        [
            'sort_order'     => 2,
            'icon'           => 'history',
            'image_media_id' => null,
            'url'            => '',
            'is_active'      => true,
            'title_ca'       => '+25 anys',
            'subtitle_ca'    => "d'experiència professional",
            'title_es'       => '+25 años',
            'subtitle_es'    => 'de experiencia profesional',
            'title_en'       => '+25 years',
            'subtitle_en'    => 'of professional experience',
        ],
    ];

    public function mount(): void
    {
        $this->form->fill([
            'badges' => SiteSetting::get('trust_bar', self::DEFAULT_BADGES),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Badges de confianza')
                    ->description('Se muestran en una barra horizontal justo encima del footer. Arrastrá para reordenar, o usá el número de orden.')
                    ->schema([
                        Repeater::make('badges')
                            ->hiddenLabel()
                            ->schema([

                                // ── Columna izquierda: config + idiomas ───────────────────────
                                Section::make()
                                    ->compact()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('sort_order')
                                                    ->label('Orden')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->required()
                                                    ->columnSpan(1),

                                                TextInput::make('url')
                                                    ->label('Enlace (opcional)')
                                                    ->url()
                                                    ->placeholder('https://...')
                                                    ->helperText('Si se completa, el badge es clickeable.')
                                                    ->columnSpan(1),

                                                Toggle::make('is_active')
                                                    ->label('Visible')
                                                    ->default(true)
                                                    ->columnSpan(1),
                                            ]),

                                        Tabs::make('Textos por idioma')
                                            ->tabs([
                                                Tabs\Tab::make('🏳️ Català')
                                                    ->schema([
                                                        TextInput::make('title_ca')
                                                            ->label('Títol principal')
                                                            ->required()
                                                            ->columnSpan(1),
                                                        TextInput::make('subtitle_ca')
                                                            ->label('Subtítol')
                                                            ->columnSpan(1),
                                                    ])
                                                    ->columns(2),
                                                Tabs\Tab::make('🇪🇸 Español')
                                                    ->schema([
                                                        TextInput::make('title_es')
                                                            ->label('Título principal')
                                                            ->required()
                                                            ->columnSpan(1),
                                                        TextInput::make('subtitle_es')
                                                            ->label('Subtítulo')
                                                            ->columnSpan(1),
                                                    ])
                                                    ->columns(2),
                                                Tabs\Tab::make('🇬🇧 English')
                                                    ->schema([
                                                        TextInput::make('title_en')
                                                            ->label('Main title')
                                                            ->required()
                                                            ->columnSpan(1),
                                                        TextInput::make('subtitle_en')
                                                            ->label('Subtitle')
                                                            ->columnSpan(1),
                                                    ])
                                                    ->columns(2),
                                            ]),
                                    ])
                                    ->columnSpan(2),

                                // ── Columna derecha: icono + imagen ───────────────────────────
                                Section::make('Visual')
                                    ->compact()
                                    ->schema([
                                        TextInput::make('icon')
                                            ->label('Icono (Material Symbols)')
                                            ->placeholder('verified')
                                            ->helperText('Ej: verified, shield, award_star')
                                            ->columnSpanFull(),

                                        CuratorPicker::make('image_media_id')
                                            ->label('Imagen (anula el icono)')
                                            ->helperText('Si subís imagen, el icono se ignora.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpan(1),

                            ])
                            ->columns(3)
                            ->addActionLabel('Añadir badge')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['title_ca'] ?? null)
                                    ? ($state['title_ca'] . ($state['subtitle_ca'] ? ' — ' . $state['subtitle_ca'] : ''))
                                    : 'Badge sin título'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $badges = $this->form->getState()['badges'] ?? [];

        usort($badges, fn (array $a, array $b): int =>
            ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0)
        );

        SiteSetting::set('trust_bar', $badges);

        Notification::make()
            ->title('Configuración de la trust bar guardada')
            ->success()
            ->send();
    }
}
