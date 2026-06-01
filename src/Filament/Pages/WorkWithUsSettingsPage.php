<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

final class WorkWithUsSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Trabaja con nosotros';
    protected static ?string $title = 'Configuración — Trabaja con nosotros';
    protected string $view = 'filament.pages.work-with-us-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::get('careers_page', []) ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── Hero ─────────────────────────────────────────────────────
                Section::make('Hero')
                    ->schema([
                        Tabs::make('Título por idioma')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('hero_title.ca')
                                            ->label('Título (Catalán)')
                                            ->required(),
                                        TextInput::make('hero_subtitle.ca')
                                            ->label('Subtítulo (Catalán)'),
                                        TextInput::make('hero_cta_text.ca')
                                            ->label('Texto del botón CTA (Catalán)'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('hero_title.es')
                                            ->label('Título (Español)')
                                            ->required(),
                                        TextInput::make('hero_subtitle.es')
                                            ->label('Subtítulo (Español)'),
                                        TextInput::make('hero_cta_text.es')
                                            ->label('Texto del botón CTA (Español)'),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('hero_title.en')
                                            ->label('Title (English)')
                                            ->required(),
                                        TextInput::make('hero_subtitle.en')
                                            ->label('Subtitle (English)'),
                                        TextInput::make('hero_cta_text.en')
                                            ->label('CTA button text (English)'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        TextInput::make('hero_cta_url')
                            ->label('URL del CTA')
                            ->url()
                            ->placeholder('https://...'),
                        CuratorPicker::make('hero_image_media_id')
                            ->label('Imagen del hero'),
                        TextInput::make('hero_image_url')
                            ->label('URL de imagen alternativa (fallback)')
                            ->url()
                            ->placeholder('https://...'),
                    ]),

                // ── Benefits ─────────────────────────────────────────────────
                Section::make('Beneficios')
                    ->description('Exactamente 3 beneficios para mostrar en la página.')
                    ->schema([
                        Repeater::make('benefits')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('icon')
                                    ->label('Icono (Material Symbols)')
                                    ->placeholder('work_outline')
                                    ->helperText('Nombre del icono de Material Symbols, ej: work_outline, trending_up, people'),
                                Tabs::make('Texto por idioma')
                                    ->tabs([
                                        Tabs\Tab::make('Català')
                                            ->schema([
                                                TextInput::make('title.ca')
                                                    ->label('Título (Catalán)')
                                                    ->required(),
                                                Textarea::make('description.ca')
                                                    ->label('Descripción (Catalán)')
                                                    ->rows(2),
                                            ]),
                                        Tabs\Tab::make('Español')
                                            ->schema([
                                                TextInput::make('title.es')
                                                    ->label('Título (Español)')
                                                    ->required(),
                                                Textarea::make('description.es')
                                                    ->label('Descripción (Español)')
                                                    ->rows(2),
                                            ]),
                                        Tabs\Tab::make('English')
                                            ->schema([
                                                TextInput::make('title.en')
                                                    ->label('Title (English)')
                                                    ->required(),
                                                Textarea::make('description.en')
                                                    ->label('Description (English)')
                                                    ->rows(2),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->minItems(3)
                            ->maxItems(3)
                            ->addActionLabel('Añadir beneficio')
                            ->collapsible()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // ── Form Settings ─────────────────────────────────────────────
                Section::make('Configuración del formulario')
                    ->schema([
                        TextInput::make('form_destination_email')
                            ->label('Email de destino de candidaturas')
                            ->email()
                            ->required()
                            ->placeholder('rrhh@agcassessors.com'),
                        Tabs::make('Textos del formulario por idioma')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        Textarea::make('form_intro.ca')
                                            ->label('Intro del formulario (Catalán)')
                                            ->rows(2),
                                        Textarea::make('form_privacy_text.ca')
                                            ->label('Texto de privacidad (Catalán)')
                                            ->rows(2),
                                        TextInput::make('form_success_message.ca')
                                            ->label('Mensaje de éxito (Catalán)'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        Textarea::make('form_intro.es')
                                            ->label('Intro del formulario (Español)')
                                            ->rows(2),
                                        Textarea::make('form_privacy_text.es')
                                            ->label('Texto de privacidad (Español)')
                                            ->rows(2),
                                        TextInput::make('form_success_message.es')
                                            ->label('Mensaje de éxito (Español)'),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        Textarea::make('form_intro.en')
                                            ->label('Form intro (English)')
                                            ->rows(2),
                                        Textarea::make('form_privacy_text.en')
                                            ->label('Privacy text (English)')
                                            ->rows(2),
                                        TextInput::make('form_success_message.en')
                                            ->label('Success message (English)'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── Footer CTA ────────────────────────────────────────────────
                Section::make('Footer CTA')
                    ->schema([
                        Tabs::make('Footer CTA por idioma')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('footer_cta_title.ca')
                                            ->label('Título del CTA (Catalán)'),
                                        TextInput::make('footer_cta_button_text.ca')
                                            ->label('Texto del botón (Catalán)'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('footer_cta_title.es')
                                            ->label('Título del CTA (Español)'),
                                        TextInput::make('footer_cta_button_text.es')
                                            ->label('Texto del botón (Español)'),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('footer_cta_title.en')
                                            ->label('CTA title (English)'),
                                        TextInput::make('footer_cta_button_text.en')
                                            ->label('Button text (English)'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('careers_page', $this->form->getState());

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
