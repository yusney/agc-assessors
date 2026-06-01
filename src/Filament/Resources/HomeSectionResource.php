<?php

declare(strict_types=1);

namespace AGC\Filament\Resources;

use AGC\Filament\Forms\Components\UrlPickerField;
use AGC\Infrastructure\Persistence\Eloquent\Models\HomeSection;
use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Sección de inicio';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Secciones de inicio';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)
                ->columnSpanFull()
                ->schema([

                    // ── Main content (3/4) ──────────────────────────────────
                    Grid::make(1)
                        ->columnSpan(3)
                        ->schema([

                            // ── Textos (todos los tipos) ─────────────────
                            Section::make('Textos')
                                ->schema([
                                    Tabs::make('Traducciones')
                                        ->tabs([
                                            self::translationTab('Català', 'ca', required: true),
                                            self::translationTab('Español', 'es'),
                                            self::translationTab('English', 'en'),
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            // ── Imagen principal / Hero Slider ─────────────
                            Section::make('Imagen principal')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'hero')
                                ->schema([
                                    // Repeater for hero slides
                                    Repeater::make('settings.hero_slides')
                                        ->label('Diapositivas del hero')
                                        ->helperText('Agregá una o más imágenes. Se mostrarán como slider con transiciones.')
                                        ->schema([
                                            CuratorPicker::make('media_id')
                                                ->label('Imagen (biblioteca)')
                                                ->buttonLabel('Seleccionar imagen')
                                                ->constrained()
                                                ->nullable()
                                                ->columnSpanFull(),
                                            TextInput::make('image_url')
                                                ->label('URL alternativa')
                                                ->helperText('Si no hay imagen en biblioteca, se usa esta URL.')
                                                ->url()
                                                ->maxLength(1000)
                                                ->columnSpanFull(),
                                            TextInput::make('image_alt')
                                                ->label('Texto alternativo (alt)')
                                                ->helperText('Describe brevemente lo que se ve en la imagen (personas, acción, lugar). Ej: "Equipo de AGC revisando documentos fiscales en oficina". No uses "imagen de...", sé directo.')
                                                ->maxLength(255)
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible()
                                        ->reorderable()
                                        ->addActionLabel('Añadir diapositiva')
                                        ->columnSpanFull(),

                                    // Transition settings
                                    Grid::make(3)->schema([
                                        Select::make('settings.hero_transition')
                                            ->label('Transición')
                                            ->options([
                                                'fade'  => 'Fade (desvanecimiento)',
                                                'slide' => 'Slide (deslizamiento)',
                                                'zoom'  => 'Zoom (acercamiento)',
                                            ])
                                            ->default('fade')
                                            ->native(false)
                                            ->columnSpan(1),

                                        TextInput::make('settings.hero_interval')
                                            ->label('Intervalo (segundos)')
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(1)
                                            ->maxValue(60)
                                            ->columnSpan(1),

                                        Toggle::make('settings.hero_autoplay')
                                            ->label('Auto-play')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1),
                                    ]),

                                    // Fallback: single-image fields (backward compatibility)
                                    // Hidden when hero_slides has items
                                    CuratorPicker::make('main_image_media_id')
                                        ->label('Imagen principal (modo simple)')
                                        ->hidden(fn (Get $get): bool => !empty($get('settings.hero_slides')))
                                        ->hiddenLabel()
                                        ->buttonLabel('Seleccionar imagen')
                                        ->constrained()
                                        ->nullable()
                                        ->columnSpanFull(),
                                    Grid::make(2)->schema([
                                        TextInput::make('image_url')
                                            ->label('URL alternativa')
                                            ->helperText('Si no hay imagen en biblioteca, se usa esta URL.')
                                            ->url()
                                            ->maxLength(1000),
                                        TextInput::make('settings.image_alt')
                                            ->label('Texto alternativo (alt)')
                                            ->maxLength(255),
                                    ])
                                    ->hidden(fn (Get $get): bool => !empty($get('settings.hero_slides'))),
                                ])
                                ->columnSpanFull(),

                            // ── Botones (hero, news_highlight, contact_cta) ─
                            Section::make('Botones')
                                ->hidden(fn (Get $get): bool => ! in_array($get('type'), ['hero', 'news_highlight', 'contact_cta']))
                                ->schema([
                                    Grid::make(2)->schema([
                                        UrlPickerField::make('cta_url')
                                            ->label('URL botón principal')
                                            ->maxLength(500),
                                    ]),
                                    Grid::make(2)
                                        ->hidden(fn (Get $get): bool => $get('type') !== 'hero')
                                        ->schema([
                                            UrlPickerField::make('secondary_cta_url')
                                                ->label('URL botón secundario')
                                                ->maxLength(500),
                                        ]),
                                ])
                                ->description('El texto de cada botón se edita en la pestaña "Textos".'),

                            // ── Elementos del carrusel (solo carousel) ────
                            Section::make('Diapositivas del carrusel')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'carousel')
                                ->schema([
                                     Repeater::make('carousel_items')
                                        ->hiddenLabel()
                                        ->schema([
                                            CuratorPicker::make('media_id')
                                                ->label('Imagen')
                                                ->buttonLabel('Seleccionar imagen')
                                                ->columnSpanFull(),
                                            TextInput::make('image_url')
                                                ->label('URL externa (alternativa si no hay imagen subida)')
                                                ->url()
                                                ->maxLength(1000)
                                                ->columnSpanFull(),
                                            UrlPickerField::make('cta_url')
                                                ->label('URL del botón')
                                                ->maxLength(500)
                                                ->columnSpanFull(),
                                            Tabs::make('Textos superpuestos')
                                                ->tabs([
                                                    self::carouselItemTab('Català', 'ca'),
                                                    self::carouselItemTab('Español', 'es'),
                                                    self::carouselItemTab('English', 'en'),
                                                ])
                                                ->columnSpanFull(),
                                        ])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => data_get($state, 'title.ca') ?: data_get($state, 'image_url'))
                                        ->columnSpanFull(),
                                ]),

                            // ── Estadísticas (solo stats) ─────────────────
                            Section::make('Estadísticas')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'stats')
                                ->description('Cada ítem muestra un número grande y una etiqueta debajo.')
                                ->schema([
                                    Repeater::make('settings.stats')
                                        ->hiddenLabel()
                                        ->schema([
                                            TextInput::make('value')
                                                ->label('Valor (número o texto)')
                                                ->required()
                                                ->maxLength(50),
                                            Tabs::make('Etiqueta por idioma')
                                                ->tabs([
                                                    Tabs\Tab::make('Català')
                                                        ->schema([
                                                            TextInput::make('label.ca')->label('Etiqueta (ca)')->required(),
                                                        ]),
                                                    Tabs\Tab::make('Español')
                                                        ->schema([
                                                            TextInput::make('label.es')->label('Etiqueta (es)'),
                                                        ]),
                                                    Tabs\Tab::make('English')
                                                        ->schema([
                                                            TextInput::make('label.en')->label('Etiqueta (en)'),
                                                        ]),
                                                ]),
                                        ])
                                        ->addActionLabel('Añadir estadística')
                                        ->columnSpanFull(),
                                ]),

                            // ── Noticias destacadas (solo news_highlight) ──
                            Section::make('Configuración de noticias')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'news_highlight')
                                ->schema([
                                    TextInput::make('settings.limit')
                                        ->label('Número de noticias a mostrar')
                                        ->numeric()
                                        ->default(3)
                                        ->minValue(1)
                                        ->maxValue(12),
                                ]),

                            // ── Testimonios (solo testimonials) ──────────
                            Section::make('Testimonios')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'testimonials')
                                ->description('Configura los testimonios que aparecen en la sección de clientes.')
                                ->schema([
                                    TextInput::make('settings.limit')
                                        ->label('Número de testimonios a mostrar')
                                        ->numeric()
                                        ->default(3)
                                        ->minValue(1)
                                        ->maxValue(12),
                                    Repeater::make('settings.testimonials')
                                        ->label('Testimonios')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Nombre completo')
                                                ->required()
                                                ->maxLength(100),
                                            Grid::make(2)->schema([
                                                TextInput::make('role')
                                                    ->label('Cargo')
                                                    ->maxLength(100),
                                                TextInput::make('company')
                                                    ->label('Empresa')
                                                    ->maxLength(100),
                                            ]),
                                            Textarea::make('text')
                                                ->label('Cita (ca)')
                                                ->rows(3)
                                                ->maxLength(500)
                                                ->columnSpanFull(),
                                            TextInput::make('initials')
                                                ->label('Iniciales (avatar, 2 letras — opcional)')
                                                ->maxLength(3)
                                                ->helperText('Si no se especifica, se usará la primera letra del nombre.'),
                                        ])
                                        ->addActionLabel('Añadir testimonio')
                                        ->collapsible()
                                        ->collapsed()
                                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),

                            // ── Llamada a contacto (solo contact_cta) ─────
                            Section::make('Configuración del formulario')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'contact_cta')
                                ->schema([
                                    Toggle::make('settings.is_newsletter')
                                        ->label('Mostrar formulario de newsletter')
                                        ->helperText('Si está activo, muestra un campo de email en lugar del botón.')
                                        ->inline(false)
                                        ->live(),
                                    Tabs::make('Textos del newsletter')
                                        ->hidden(fn (Get $get): bool => ! $get('settings.is_newsletter'))
                                        ->tabs([
                                            self::newsletterTab('Català', 'ca'),
                                            self::newsletterTab('Español', 'es'),
                                            self::newsletterTab('English', 'en'),
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            // ── Servicios con iconos (solo services_highlight) ─
                            Section::make('Servicios a mostrar')
                                ->hidden(fn (Get $get): bool => $get('type') !== 'services_highlight')
                                ->description('Seleccioná qué servicios mostrar y con qué icono representarlos.')
                                ->schema([
                                    Select::make('settings.columns')
                                        ->label('Columnas en desktop')
                                        ->options([
                                            3 => '3 columnas',
                                            4 => '4 columnas',
                                        ])
                                        ->default(3)
                                        ->native(false),

                                    Repeater::make('settings.service_items')
                                        ->hiddenLabel()
                                        ->schema([
                                            Select::make('service_slug')
                                                ->label('Servicio')
                                                ->options(fn (): array => ServiceModel::where('active', true)
                                                    ->orderBy('sort_order')
                                                    ->get(['slug', 'name'])
                                                    ->mapWithKeys(fn (ServiceModel $s): array => [
                                                        $s->slug => is_array($s->name)
                                                            ? ($s->name['ca'] ?? $s->name['es'] ?? reset($s->name))
                                                            : $s->name,
                                                    ])
                                                    ->all()
                                                )
                                                ->required()
                                                ->searchable()
                                                ->native(false)
                                                ->columnSpan(1),

                                            Select::make('icon')
                                                ->label('Icono')
                                                ->options(self::materialIconOptions())
                                                ->allowHtml()
                                                ->required()
                                                ->searchable()
                                                ->native(false)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel('Añadir servicio')
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── Sidebar (1/4) ────────────────────────────────────────
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Section::make('Estructura')
                                ->schema([
                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),
                                    Select::make('type')
                                        ->required()
                                        ->live()
                                        ->options([
                                            'hero'               => '🦸 Hero',
                                            'intro'              => '📝 Introducción',
                                            'services_highlight' => '⭐ Servicios destacados',
                                            'stats'              => '📊 Estadísticas',
                                            'testimonials'       => '💬 Testimonios',
                                            'carousel'           => '🎠 Carrusel',
                                            'news_highlight'     => '📰 Noticias destacadas',
                                            'contact_cta'        => '📬 Llamada a contacto',
                                            'offices_map'        => '🗺️ Mapa de oficinas',
                                        ]),
                                    TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                                    Toggle::make('is_active')->label('Activa')->default(true)->inline(false),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Identificador')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('title.ca')->label('Título (ca)')->limit(45),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activa'),
                Tables\Filters\SelectFilter::make('type')->options([
                    'hero'               => 'Hero',
                                    'intro'              => 'Introducción',
                                    'services_highlight' => 'Servicios destacados',
                                    'stats'              => 'Estadísticas',
                                    'testimonials'       => 'Testimonios',
                                    'carousel'           => 'Carrusel',
                                    'news_highlight'     => 'Noticias destacadas',
                                    'contact_cta'        => 'Llamada a contacto',
                                    'offices_map'        => 'Mapa de oficinas',
                ]),
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
            'index'  => \AGC\Filament\Resources\HomeSectionResource\Pages\ListHomeSections::route('/'),
            'create' => \AGC\Filament\Resources\HomeSectionResource\Pages\CreateHomeSection::route('/create'),
            'edit'   => \AGC\Filament\Resources\HomeSectionResource\Pages\EditHomeSection::route('/{record}/edit'),
        ];
    }

    // ── Translation tab: fields vary per type, shown/hidden via ->live() on type ──

    private static function translationTab(string $label, string $locale, bool $required = false): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([

            // eyebrow: hero, intro, carousel
            TextInput::make("eyebrow.{$locale}")
                ->label("Antetítulo ({$locale})")
                ->hidden(fn (Get $get): bool => ! in_array($get('type'), ['hero', 'intro', 'carousel'])),

            // title: all types
            TextInput::make("title.{$locale}")
                ->label("Título ({$locale})")
                ->required($required && $locale === 'ca'),

            // subtitle: hero, intro, services_highlight, carousel, news_highlight, contact_cta
            Textarea::make("subtitle.{$locale}")
                ->label("Subtítulo ({$locale})")
                ->rows(3)
                ->hidden(fn (Get $get): bool => in_array($get('type'), ['stats'])),

            // body: intro only
            Textarea::make("body.{$locale}")
                ->label("Texto ({$locale})")
                ->rows(5)
                ->hidden(fn (Get $get): bool => $get('type') !== 'intro'),

            // cta_label: hero, news_highlight, contact_cta
            TextInput::make("cta_label.{$locale}")
                ->label("Texto botón principal ({$locale})")
                ->hidden(fn (Get $get): bool => ! in_array($get('type'), ['hero', 'news_highlight', 'contact_cta'])),

            // secondary_cta_label: hero only
            TextInput::make("secondary_cta_label.{$locale}")
                ->label("Texto botón secundario ({$locale})")
                ->hidden(fn (Get $get): bool => $get('type') !== 'hero'),
        ]);
    }

    private static function carouselItemTab(string $label, string $locale): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([
            TextInput::make("eyebrow.{$locale}")->label("Antetítulo ({$locale})"),
            TextInput::make("title.{$locale}")->label("Título ({$locale})"),
            Textarea::make("body.{$locale}")->label("Texto ({$locale})")->rows(3),
            TextInput::make("cta_label.{$locale}")->label("Texto del botón ({$locale})"),
        ]);
    }

    private static function newsletterTab(string $label, string $locale): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([
            TextInput::make("settings.newsletter_placeholder.{$locale}")->label("Placeholder del campo email ({$locale})"),
            TextInput::make("settings.newsletter_legal.{$locale}")->label("Texto legal ({$locale})"),
        ]);
    }

    /**
     * Curated Material Symbols icons for a professional advisory firm.
     * Rendered with the icon glyph so the user can see what they're picking.
     *
     * @return array<string, string>
     */
    private static function materialIconOptions(): array
    {
        $icons = [
            // Derecho / asesoría
            'balance'            => 'Balanza',
            'gavel'              => 'Mazo / judicial',
            'policy'             => 'Política / normativa',
            'description'        => 'Documento',
            'contract'           => 'Contrato',
            'cases'              => 'Maletín de casos',
            'assignment'         => 'Asignación',
            'article'            => 'Artículo',
            // Finanzas / contabilidad
            'account_balance'    => 'Banco / contabilidad',
            'monitoring'         => 'Monitoreo / analítica',
            'trending_up'        => 'Tendencia al alza',
            'savings'            => 'Ahorros',
            'payments'           => 'Pagos',
            'currency_exchange'  => 'Cambio de divisa',
            'calculate'          => 'Calculadora',
            'receipt_long'       => 'Recibo / factura',
            'attach_money'       => 'Dinero',
            // Laboral / RRHH
            'work'               => 'Trabajo',
            'work_outline'       => 'Trabajo (outline)',
            'badge'              => 'Identificación',
            'groups'             => 'Equipo / grupos',
            'supervisor_account' => 'Supervisor',
            'manage_accounts'    => 'Gestión de cuentas',
            'handshake'          => 'Acuerdo / colaboración',
            'engineering'        => 'Ingeniería / técnico',
            // Empresa / general
            'business'           => 'Empresa',
            'business_center'    => 'Centro de negocios',
            'real_estate_agent'  => 'Inmobiliaria',
            'support'            => 'Soporte',
            'security'           => 'Seguridad',
            'verified'           => 'Verificado',
            'shield'             => 'Protección',
            'star'               => 'Estrella / destacado',
        ];

        return collect($icons)
            ->mapWithKeys(fn (string $label, string $name): array => [
                $name => '<span class="flex items-center gap-2">'
                    . '<span class="material-symbols-outlined" style="font-size:20px;line-height:1;vertical-align:middle">' . $name . '</span>'
                    . '<span>' . $label . '</span>'
                    . '</span>',
            ])
            ->all();
    }
}
