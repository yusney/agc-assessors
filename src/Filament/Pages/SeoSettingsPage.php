<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

/**
 * Filament settings page for global SEO defaults.
 *
 * Stores per-locale meta title + description and a shared OG image URL
 * under the `seo.global.*` SiteSetting keys consumed by SeoComposer.
 *
 * No keywords field — meta keywords are out of scope per spec non-goal and
 * design decision D4. Do NOT add one.
 */
final class SeoSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'SEO Global';

    protected static ?string $title = 'Configuración — SEO Global';

    protected string $view = 'filament.pages.seo-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $ogImageValue = SiteSetting::get('seo.global.og_image_media_id');
        $ogImageForForm = null;

        if (is_int($ogImageValue) || (is_string($ogImageValue) && $ogImageValue !== '')) {
            $ogImageForForm = get_media_items([$ogImageValue])->toArray();
        } elseif (is_array($ogImageValue)) {
            $ogImageForForm = get_media_items($ogImageValue)->toArray();
        }

        $this->form->fill([
            'title' => [
                'ca' => SiteSetting::get('seo.global.ca.title') ?? '',
                'es' => SiteSetting::get('seo.global.es.title') ?? '',
                'en' => SiteSetting::get('seo.global.en.title') ?? '',
            ],
            'description' => [
                'ca' => SiteSetting::get('seo.global.ca.description') ?? '',
                'es' => SiteSetting::get('seo.global.es.description') ?? '',
                'en' => SiteSetting::get('seo.global.en.description') ?? '',
            ],
            'og_image_media_id' => $ogImageForForm ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                // ── Per-locale title & description ───────────────────────────
                Section::make('Meta por idioma')
                    ->description('Valores predeterminados que se usan cuando una página no tiene SEO propio.')
                    ->schema([
                        Tabs::make('Idiomas')
                            ->tabs([
                                Tabs\Tab::make('Català')
                                    ->schema([
                                        TextInput::make('title.ca')
                                            ->label('Título meta (Català)')
                                            ->maxLength(70)
                                            ->helperText('Máx. 70 caracteres. Aparece en pestaña del navegador y resultados de búsqueda.'),
                                        Textarea::make('description.ca')
                                            ->label('Descripción meta (Català)')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Máx. 160 caracteres. Fragmento que aparece en Google.'),
                                    ]),
                                Tabs\Tab::make('Español')
                                    ->schema([
                                        TextInput::make('title.es')
                                            ->label('Título meta (Español)')
                                            ->maxLength(70),
                                        Textarea::make('description.es')
                                            ->label('Descripción meta (Español)')
                                            ->rows(3)
                                            ->maxLength(160),
                                    ]),
                                Tabs\Tab::make('English')
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label('Meta title (English)')
                                            ->maxLength(70),
                                        Textarea::make('description.en')
                                            ->label('Meta description (English)')
                                            ->rows(3)
                                            ->maxLength(160),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── Shared OG image (not per-locale) ─────────────────────────
                Section::make('Imagen Open Graph (compartida)')
                    ->description('Imagen que aparece al compartir en redes sociales. Se usa para todas las páginas y todos los idiomas.')
                    ->schema([
                        CuratorPicker::make('og_image_media_id')
                            ->label('Imagen OG')
                            ->constrained()
                            ->helperText('Tamaño recomendado: 1200 × 630 px. Formato WebP preferido. Dejar vacío para usar la imagen por defecto.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        /** @var array<string, mixed> $state */
        $state = $this->data ?? [];

        /** @var array<string, string> $titles */
        $titles = is_array($state['title'] ?? null) ? $state['title'] : [];

        /** @var array<string, string> $descriptions */
        $descriptions = is_array($state['description'] ?? null) ? $state['description'] : [];

        foreach (['ca', 'es', 'en'] as $locale) {
            SiteSetting::set("seo.global.{$locale}.title", $titles[$locale] ?? '');
            SiteSetting::set("seo.global.{$locale}.description", $descriptions[$locale] ?? '');
        }

        $ogImageMediaId = null;
        $ogImageValue = $state['og_image_media_id'] ?? null;

        if (is_int($ogImageValue)) {
            $ogImageMediaId = $ogImageValue;
        } elseif (is_array($ogImageValue) && count($ogImageValue) > 0) {
            // CuratorPicker may return UUID-keyed arrays from Livewire state.
            // Recursively walk the structure and grab the first integer id.
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($ogImageValue)
            );
            foreach ($iterator as $key => $value) {
                if ($key === 'id' && is_int($value)) {
                    $ogImageMediaId = $value;
                    break;
                }
                if (is_int($value)) {
                    $ogImageMediaId = $value;
                    break;
                }
            }
        } elseif (is_string($ogImageValue) && $ogImageValue !== '') {
            $ogImageMediaId = (int) $ogImageValue;
        }

        SiteSetting::set('seo.global.og_image_media_id', $ogImageMediaId);

        Notification::make()
            ->title('Configuración SEO guardada correctamente')
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
