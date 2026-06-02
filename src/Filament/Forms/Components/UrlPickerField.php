<?php

declare(strict_types=1);

namespace AGC\Filament\Forms\Components;

use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;
use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Route;

/**
 * TextInput with a suffix button that opens a searchable picker of
 * internal application routes, including published DB records.
 *
 * Usage:
 *   UrlPickerField::make('cta_url')->label('URL del botón')
 */
final class UrlPickerField extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->placeholder('https://... o seleccionar ruta interna')
            ->suffixAction(
                Action::make('pickRoute')
                    ->label('Rutas internas')
                    ->icon('heroicon-o-link')
                    ->iconButton()
                    ->tooltip('Seleccionar ruta de la aplicación')
                    ->form([
                        Select::make('selected_route')
                            ->label('Página o sección')
                            ->options(fn () => self::buildRouteOptions())
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->placeholder('Busca por nombre o URL...'),
                    ])
                    ->action(function (array $data, Set $set, TextInput $component): void {
                        $set($component->getStatePath(isAbsolute: false), $data['selected_route']);
                    })
            );
    }

    /**
     * Build grouped options:
     *
     * Páginas principales  → static named routes (no params)
     * Servicios            → /serveis  +  one entry per published service
     * Noticias             → /actualitat  +  one entry per published news
     * Equipo               → static team routes
     * Otras rutas          → anything else
     */
    private static function buildRouteOptions(): array
    {
        $skip = [
            'filament.', 'debugbar.', 'sanctum.', 'ignition.',
            'livewire.', 'horizon.', 'telescope.', 'storage.', 'up',
        ];

        $groups = [
            'Páginas principales' => [],
            'Páginas' => [],
            'Servicios' => [],
            'Noticias' => [],
            'Equipo' => [],
            'Otras rutas' => [],
        ];

        // ── Static routes (no required params) ──────────────────────────
        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods())) {
                continue;
            }

            $name = $route->getName();
            if (! $name) {
                continue;
            }

            foreach ($skip as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    continue 2;
                }
            }

            // Skip routes that require parameters
            if (preg_match('/\{[^?}]+\}/', $route->uri())) {
                continue;
            }

            $url = '/'.ltrim($route->uri(), '/');
            $label = $url;

            match (true) {
                str_starts_with($name, 'services') => $groups['Servicios'][$url] = $label,
                str_starts_with($name, 'news') => $groups['Noticias'][$url] = $label,
                str_starts_with($name, 'team') => $groups['Equipo'][$url] = $label,
                in_array($name, ['home', 'contact', 'pages.show']) => $groups['Páginas principals'][$url] = $label,
                default => $groups['Otras rutas'][$url] = $label,
            };
        }

        // ── Published services ───────────────────────────────────────────
        ServiceModel::where('active', true)
            ->orderBy('sort_order')
            ->get(['slug', 'name'])
            ->each(function (ServiceModel $s) use (&$groups): void {
                $url = '/serveis/'.$s->slug;
                $name = is_array($s->name)
                    ? ($s->name['ca'] ?? $s->name['es'] ?? reset($s->name))
                    : $s->name;
                $groups['Servicios'][$url] = '  ↳ '.$name;
            });

        // ── Published news ───────────────────────────────────────────────
        NewsModel::where('published', true)
            ->orderByDesc('published_at')
            ->get(['slug', 'title'])
            ->each(function (NewsModel $n) use (&$groups): void {
                $url = '/actualitat/'.$n->slug;
                $title = is_array($n->title)
                    ? ($n->title['ca'] ?? $n->title['es'] ?? reset($n->title))
                    : $n->title;
                $groups['Noticias'][$url] = '  ↳ '.$title;
            });

        // ── Published pages ─────────────────────────────────────────────
        PageModel::where('published', true)
            ->orderBy('slug')
            ->get(['slug', 'title'])
            ->each(function (PageModel $p) use (&$groups): void {
                $url = '/pages/'.$p->slug;
                $title = is_array($p->title)
                    ? ($p->title['ca'] ?? $p->title['es'] ?? reset($p->title))
                    : $p->title;
                $groups['Páginas'][$url] = '  ↳ '.$title;
            });

        // ── Remove empty groups ──────────────────────────────────────────
        return array_filter($groups, fn ($g) => ! empty($g));
    }
}
