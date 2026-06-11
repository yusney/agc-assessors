# Plan técnico — SEO local multi-oficina para `/es/oficinas`

> **Objetivo**: que AGC Assessors posicione en búsquedas locales del tipo *"asesoría fiscal en [ciudad]"* en Caldes de Montbui, Sant Celoni, Mollet del Vallès, Granollers, Prats de Lluçanès y Manlleu.
> **Stack**: Laravel 13 · Filament 5 · spatie/laravel-translatable · Leaflet + OpenStreetMap (mapa visual) · Google Maps directions (link saliente "Cómo llegar").
> **Arquitectura**: Clean Architecture (Domain / Application / Infrastructure / Filament). Se respeta la separación de capas.

---

## Tabla de contenidos

1. [Diagnóstico actual (medido)](#1-diagnóstico-actual-medido)
2. [Cambios propuestos — resumen](#2-cambios-propuestos--resumen)
3. [Capa 1 — Infraestructura (migración + modelo)](#3-capa-1--infraestructura-migración--modelo)
4. [Capa 2 — Dominio (entidad)](#4-capa-2--dominio-entidad)
5. [Capa 3 — Filament (Resource)](#5-capa-3--filament-resource)
6. [Capa 4 — Composer SEO (schema LocalBusiness × N)](#6-capa-4--composer-seo-schema-localbusiness--n)
7. [Capa 5 — Vista pública (Blade)](#7-capa-5--vista-pública-blade)
8. [Capa 6 — Traducciones i18n](#8-capa-6--traducciones-i18n)
9. [Sitemap XML](#9-sitemap-xml)
10. [Verificación local (criterios de aceptación)](#10-verificación-local-criterios-de-aceptación)
11. [Riesgos y rollback](#11-riesgos-y-rollback)
12. [Orden de ejecución](#12-orden-de-ejecución)

---

## 1. Diagnóstico actual (medido)

Auditoría con Chrome DevTools sobre `http://localhost:8080/es/oficinas`:

| Métrica | Estado | Problema |
|---|---|---|
| Title | `Nuestras oficinas – AGC Assessors` | Mejorable (sin keyword local) |
| Meta description | 66 chars, genérica | Falta ciudad, keyword, CTA |
| H1 | 1 ✓ | OK |
| **H2** | **0** | Crítico |
| **Schema `LocalBusiness`** | **0** | Crítico |
| `<address>` semántico | **0** | Crítico |
| Tabla resumen de oficinas | No existe | Importante |
| Texto único total | 228 palabras (≈ 38/oficina) | Thin content |
| Oficinas renderizadas | 6 (Caldes, Sant Celoni, Mollet, Granollers, Prats de Lluçanès, Manlleu) | OK |
| Mapa Leaflet | 1 mapa global con 6 markers | OK, sin cambios |
| Imagen por oficina | 1 imagen (`coverUrl()`) o inicial con gradiente | OK |
| Link "Cómo llegar" | Apunta a `google.com/maps/dir` | OK (no se cambia) |
| Hreflang | ca, es, en, x-default | OK |
| Canonical | OK | OK |
| Idioma 3 traducciones | OK (ca, es, en) | OK |

---

## 2. Cambios propuestos — resumen

| # | Capa | Cambio | Tipo |
|---|---|---|---|
| C1 | Infraestructura | Nueva migración: añade `opening_hours`, `service_area` (JSON), `image_alt` | DB |
| C2 | Dominio | Actualizar entidad `Office` con nuevos getters | Código |
| C3 | Filament | Añadir campos en `OfficeResource::form()` | UI admin |
| C4 | SeoComposer | Nuevo método `getOfficesLocalBusinessSchemas()` que emite N × `LocalBusiness` en un `@graph` | SEO |
| C5 | Vista | Reestructurar `offices/index.blade.php`: tabla resumen + bloques H2 por oficina + `<address>` + texto único | Blade |
| C6 | i18n | Ampliar `messages.php` (ca, es, en): nuevo copy de intro, CTA, leyenda tabla, pueblos cercanos | i18n |
| C7 | Sitemap | Confirmar que `/oficinas` se incluye en el sitemap con hreflang | SEO |
| C8 | Documentación | Actualizar `docs/SEO-GUIDE.md` con el patrón multi-sede (Leaflet+OSM para mapa, Google Maps para link) | Docs |

---

## 3. Capa 1 — Infraestructura (migración + modelo)

### 3.1 Nueva migración

**Archivo**: `database/migrations/2026_06_10_120000_add_local_seo_fields_to_offices.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            // Horario en formato JSON (por idioma) — schema OpeningHoursSpecification se construye en runtime
            $table->json('opening_hours')->nullable()->after('email');

            // Pueblos / municipios atendidos desde esta oficina — long-tail SEO local
            $table->json('service_area')->nullable()->after('opening_hours');

            // Alt text de la imagen de portada (por idioma)
            $table->json('image_alt')->nullable()->after('service_area');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->dropColumn(['opening_hours', 'service_area', 'image_alt']);
        });
    }
};
```

**Justificación**:
- `opening_hours` en JSON para soportar traducciones (`"weekday_text": {"ca": ["dilluns: 9:00–18:00", ...], "es": [...], "en": [...]}`).
- `service_area` es **long-tail SEO local** puro: "asesoría fiscal en Sentmenat", "asesoría fiscal en La Garriga", etc.
- `image_alt` por idioma (translatable).

### 3.2 Modelo `EloquentOffice`

**Archivo**: `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php`

```php
<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class EloquentOffice extends Model
{
    use HasTranslations;

    protected $table = 'offices';

    /** @var array<int, string> */
    public array $translatable = [
        'name', 'address', 'city', 'description',
        'opening_hours', 'service_area', 'image_alt',  // NEW
    ];

    protected $fillable = [
        'name', 'address', 'city', 'description',
        'opening_hours', 'service_area', 'image_alt',  // NEW
        'phone', 'email', 'lat', 'lng',
        'cover_media_id', 'is_active',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_active' => 'boolean',
        'name' => 'array',
        'address' => 'array',
        'city' => 'array',
        'description' => 'array',
        'opening_hours' => 'array',   // NEW
        'service_area' => 'array',    // NEW
        'image_alt' => 'array',       // NEW
    ];
}
```

---

## 4. Capa 2 — Dominio (entidad)

### 4.1 Actualizar entidad `Office`

**Archivo**: `src/Domain/Offices/Entities/Office.php`

```php
<?php

declare(strict_types=1);

namespace AGC\Domain\Offices\Entities;

use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class Office
{
    public function __construct(
        private readonly int $id,
        private readonly TranslatableString $name,
        private readonly TranslatableString $address,
        private readonly TranslatableString $city,
        private readonly TranslatableString $description,
        private readonly ?TranslatableString $openingHours,   // NEW
        private readonly ?TranslatableString $serviceArea,    // NEW
        private readonly ?TranslatableString $imageAlt,       // NEW
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly ?float $lat,
        private readonly ?float $lng,
        private readonly ?string $coverUrl,
        private readonly bool $isActive,
    ) {}

    public function id(): int { return $this->id; }
    public function name(): TranslatableString { return $this->name; }
    public function address(): TranslatableString { return $this->address; }
    public function city(): TranslatableString { return $this->city; }
    public function description(): TranslatableString { return $this->description; }
    public function phone(): ?string { return $this->phone; }
    public function email(): ?string { return $this->email; }
    public function lat(): ?float { return $this->lat; }
    public function lng(): ?float { return $this->lng; }
    public function coverUrl(): ?string { return $this->coverUrl; }
    public function isActive(): bool { return $this->isActive; }

    // NEW getters
    public function openingHours(): ?TranslatableString { return $this->openingHours; }
    public function serviceArea(): ?TranslatableString { return $this->serviceArea; }
    public function imageAlt(): ?TranslatableString { return $this->imageAlt; }

    /**
     * Devuelve el array de pueblos/zonas atendidas en el idioma actual.
     *
     * @return array<int, string>
     */
    public function serviceAreaList(string $locale): array
    {
        if ($this->serviceArea === null) {
            return [];
        }

        $value = $this->serviceArea->get($locale);
        if ($value === null) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $value) ?: [])));
    }
}
```

### 4.2 Actualizar repositorio Eloquent

**Archivo**: `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php` (o el nombre que tenga en el proyecto)

Hay que añadir un mapper que convierta `EloquentOffice` → `Office` con los 3 nuevos campos. **Localizar el repositorio concreto en el proyecto y actualizar el constructor de la entidad.** No incluyo el snippet exacto aquí porque depende de cómo el proyecto mapee hoy — el dev debe buscar `new Office(` y añadir los 3 nuevos argumentos.

> **Verificación**: `grep -rn "new Office(" src/`

---

## 5. Capa 3 — Filament (Resource)

### 5.1 Actualizar `OfficeResource::form()`

**Archivo**: `src/Filament/Resources/OfficeResource.php`

Añadir los siguientes bloques en el form, después del `Section::make('Imatge i contacte')` actual y antes del cierre:

```php
Section::make('Horaris i cobertura')->schema([
    Grid::make(2)->schema([
        Textarea::make('opening_hours.ca')
            ->label('Horari (ca)')
            ->helperText('Una línia per dia. Ex: "dilluns a dijous: 9:00–18:00"')
            ->rows(3),
        Textarea::make('opening_hours.es')
            ->label('Horario (es)')
            ->helperText('Una línea por día. Ej: "lunes a jueves: 9:00–18:00"')
            ->rows(3),
        Textarea::make('opening_hours.en')
            ->label('Opening hours (en)')
            ->helperText('One line per day. Ex: "Monday to Thursday: 9:00–18:00"')
            ->rows(3),
    ]),
    Textarea::make('service_area.ca')
        ->label('Pobles / zones que atenem (ca)')
        ->helperText('Un per línia. Ex: "Sentmenat", "Palau-solità i Plegamans"')
        ->rows(4),
    Textarea::make('service_area.es')
        ->label('Pueblos / zonas que atendemos (es)')
        ->helperText('Uno por línea. Ej: "Sentmenat", "Palau-solità i Plegamans"')
        ->rows(4),
    Textarea::make('service_area.en')
        ->label('Towns / areas served (en)')
        ->helperText('One per line. Ex: "Sentmenat", "Palau-solità i Plegamans"')
        ->rows(4),
    TextInput::make('image_alt.ca')
        ->label('Text alternatiu imatge (ca)')
        ->maxLength(125)
        ->helperText('Descriu la imatge per a lectors de pantalla i SEO.'),
    TextInput::make('image_alt.es')
        ->label('Texto alternativo imagen (es)')
        ->maxLength(125)
        ->helperText('Describe la imagen para lectores de pantalla y SEO.'),
    TextInput::make('image_alt.en')
        ->label('Image alt text (en)')
        ->maxLength(125)
        ->helperText('Describe the image for screen readers and SEO.'),
])->columnSpanFull(),
```

> **Justificación de cada campo**:
> - `opening_hours`: alimenta el `OpeningHoursSpecification` del schema `LocalBusiness`.
> - `service_area`: long-tail SEO local. Aparece como lista de "También atendemos en..." en cada oficina.
> - `image_alt`: atributo `alt` semántico en la imagen de portada (accesibilidad + SEO).

### 5.2 Tabla de Filament

Añadir columnas en `OfficeResource::table()`:

```php
->columns([
    Tables\Columns\TextColumn::make('name')
        ->label('Nom')
        ->getStateUsing(fn (EloquentOffice $record): string => $record->getTranslation('name', 'ca')),
    Tables\Columns\TextColumn::make('city')
        ->label('Ciutat')
        ->getStateUsing(fn (EloquentOffice $record): string => $record->getTranslation('city', 'ca')),
    Tables\Columns\TextColumn::make('phone')
        ->label('Telèfon')
        ->searchable(),
    Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
    // NEW
    Tables\Columns\TextColumn::make('lat')
        ->label('Lat')
        ->toggleable(isToggledHiddenByDefault: true),
    Tables\Columns\TextColumn::make('lng')
        ->label('Lng')
        ->toggleable(isToggledHiddenByDefault: true),
])
```

---

## 6. Capa 4 — Composer SEO (schema LocalBusiness × N)

**Archivo**: `app/Http/View/Composers/SeoComposer.php`

### 6.1 Inyectar dependencia

```php
<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Models\Media;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SeoComposer
{
    public function __construct(
        private readonly OfficeRepositoryInterface $officeRepository,  // NEW
    ) {}

    public function compose(View $view): void
    {
        // ... existing code ...

        // NEW: emit LocalBusiness per office
        $officeSchemas = $this->getOfficesLocalBusinessSchemas();
        foreach ($officeSchemas as $schema) {
            $schemas[] = $schema;
        }

        $view->with('schemas', $schemas);
        // ... rest unchanged
    }
}
```

### 6.2 Nuevo método privado

Añadir al final de la clase, antes del cierre `}`:

```php
/**
 * Emits one LocalBusiness schema per active office.
 * Each office gets its own @id and full NAP data so Google can
 * index them as distinct local entities even though they share
 * a single page URL.
 *
 * @return array<int, array<string, mixed>>
 */
private function getOfficesLocalBusinessSchemas(): array
{
    $offices = $this->officeRepository->findAllActive();
    if ($offices === []) {
        return [];
    }

    $siteUrl = rtrim(config('app.url', 'https://agcassessors.com'), '/');
    $baseName = SiteSetting::get('site_name', config('app.name', 'AGC Assessors'));
    $locale = (string) app()->getLocale();

    $items = [];
    foreach ($offices as $office) {
        $cityValue = $office->city()->get($locale) ?? $office->city()->get('ca') ?? '';
        $addressValue = $office->address()->get($locale) ?? $office->address()->get('ca') ?? '';
        $nameValue = $office->name()->get($locale) ?? $office->city()->get('ca') ?? '';

        if ($cityValue === '' || $addressValue === '') {
            continue;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => $siteUrl . '/oficinas#office-' . $office->id(),
            'name' => $baseName . ' - ' . $cityValue,
            'url' => $siteUrl . '/oficinas#office-' . $office->id(),
            'description' => $office->description()->get($locale)
                ?? $office->description()->get('ca')
                ?? null,
            'telephone' => $office->phone(),
            'email' => $office->email(),
            'image' => $office->coverUrl(),
            'priceRange' => '€€',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $addressValue,
                'addressLocality' => $cityValue,
                'addressRegion' => 'Barcelona',
                'addressCountry' => 'ES',
            ],
            'parentOrganization' => [
                '@type' => 'AccountingService',
                'name' => $baseName,
                'url' => $siteUrl,
            ],
        ];

        // Remove nulls to keep JSON-LD clean
        $schema = array_filter($schema, static fn ($v) => $v !== null && $v !== '');

        // Geo coordinates
        if ($office->lat() !== null && $office->lng() !== null) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $office->lat(),
                'longitude' => (float) $office->lng(),
            ];
        }

        // Opening hours (parse from translatable opening_hours JSON)
        $openingHoursValue = $office->openingHours()?->get($locale)
            ?? $office->openingHours()?->get('ca')
            ?? null;

        if (is_string($openingHoursValue) && $openingHoursValue !== '') {
            $schema['openingHoursSpecification'] = $this->buildOpeningHoursSpec($openingHoursValue);
        }

        // Service area — boost long-tail local SEO
        $serviceAreaList = $office->serviceAreaList($locale);
        if ($serviceAreaList !== []) {
            $schema['areaServed'] = array_map(
                static fn (string $area): array => [
                    '@type' => 'City',
                    'name' => $area,
                ],
                $serviceAreaList
            );
        }

        $items[] = $schema;
    }

    return $items;
}

/**
 * Parses a free-text opening hours string into schema.org OpeningHoursSpecification.
 * Accepts lines like:
 *   "Lunes a jueves: 9:00-18:00"
 *   "Friday: 9:00-14:00"
 *   "Lunes: cerrado"
 *
 * @return array<int, array<string, mixed>>
 */
private function buildOpeningHoursSpec(string $raw): array
{
    $dayMap = [
        'ca' => ['dilluns' => 'Monday', 'dimarts' => 'Tuesday', 'dimecres' => 'Wednesday',
                 'dijous' => 'Thursday', 'divendres' => 'Friday', 'dissabte' => 'Saturday', 'diumenge' => 'Sunday'],
        'es' => ['lunes' => 'Monday', 'martes' => 'Tuesday', 'miércoles' => 'Wednesday', 'miercoles' => 'Wednesday',
                 'jueves' => 'Thursday', 'viernes' => 'Friday', 'sábado' => 'Saturday', 'sabado' => 'Saturday', 'domingo' => 'Sunday'],
        'en' => ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'],
    ];

    $locale = (string) app()->getLocale();
    $map = $dayMap[$locale] ?? $dayMap['es'];
    $reverseMap = array_flip($map);

    $specs = [];
    $lines = preg_split('/[\n;]+/', $raw) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || stripos($line, 'cerrado') !== false || stripos($line, 'tancat') !== false || stripos($line, 'closed') !== false) {
            continue;
        }

        // Try to extract day(s) and time range
        if (!preg_match('/^(.+?):\s*(\d{1,2}:\d{2})\s*[–\-—to]+\s*(\d{1,2}:\d{2})/iu', $line, $m)) {
            continue;
        }

        $dayPart = mb_strtolower(trim($m[1]));
        $opens = $m[2];
        $closes = $m[3];

        // Match "lunes a jueves" → array of days
        $matchedDays = [];
        if (preg_match('/^(.+?)\s+(?:a|to|al?)\s+(.+)$/iu', $dayPart, $dm)) {
            $start = $dm[1];
            $end = $dm[2];
            foreach ($map as $caName => $enName) {
                if (str_contains($dayPart, $caName)) {
                    $startIdx = array_search($enName, $reverseMap, true);
                    $endIdx = array_search($end, $map, true);
                    if ($startIdx !== false && $endIdx !== false && $endIdx >= $startIdx) {
                        $allDays = array_values($map);
                        $matchedDays = array_slice($allDays, $startIdx, $endIdx - $startIdx + 1);
                    }
                    break;
                }
            }
        }

        if ($matchedDays === []) {
            foreach ($map as $caName => $enName) {
                if (str_contains($dayPart, $caName)) {
                    $matchedDays = [$enName];
                    break;
                }
            }
        }

        foreach ($matchedDays as $day) {
            $specs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $day,
                'opens' => $opens,
                'closes' => $closes,
            ];
        }
    }

    return $specs;
}
```

### 6.3 Registrar binding del repositorio

Verificar que `OfficeRepositoryInterface` está registrado en un Service Provider (probablemente `AppServiceProvider` o un `RepositoryServiceProvider`). Si no lo está, añadirlo:

```php
$this->app->bind(
    \AGC\Domain\Offices\Repositories\OfficeRepositoryInterface::class,
    \AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentOfficeRepository::class,
);
```

### 6.4 Verificar resultado

Tras los cambios, en `/es/oficinas` debe haber en el HTML:

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "@id": "https://agcassessors.com/oficinas#office-1",
  "name": "AGC Assessors - Caldes de Montbui",
  ...
}
</script>
```

Uno por cada oficina activa (6 en total).

---

## 7. Capa 5 — Vista pública (Blade)

**Archivo**: `resources/views/public/pages/offices/index.blade.php`

### 7.1 SEO header

Cambiar el `seo_title` y `seo_description` para apuntar a las nuevas claves i18n (ver § 8):

```blade
@section('seo_title', __('messages.offices.seo_title') . ' | AGC Assessors')
@section('seo_description', __('messages.offices.seo_description'))
@section('seo_og_type', 'website')
```

### 7.2 Estructura nueva del contenido

El blade debe reorganizarse así:

```blade
{{-- 1. Hero (igual que ahora) --}}
{{-- 2. Mapa Leaflet global con 6 markers (igual que ahora) --}}
{{-- 3. NUEVO: Tabla resumen --}}
{{-- 4. NUEVO: Por cada oficina, un bloque semántico con H2 + <address> + descripción + pueblos cercanos + mini-mapa --}}
{{-- 5. Footer CTA --}}
```

### 7.3 Snippet: Tabla resumen (insertar después del mapa global)

```blade
{{-- Summary table — all offices scannable for crawlers and users --}}
@if(!empty($offices))
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 mb-12" aria-labelledby="resumen-oficinas">
    <h2 id="resumen-oficinas" class="sr-only">{{ __('messages.offices.summary_heading') }}</h2>
    <div class="overflow-x-auto rounded-[1rem] border border-[#E2E8F0]">
        <table class="w-full text-left text-[14px]">
            <caption class="sr-only">{{ __('messages.offices.summary_caption') }}</caption>
            <thead class="bg-[#F8FAFC] text-[#475569]">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">{{ __('messages.offices.col_city') }}</th>
                    <th scope="col" class="px-4 py-3 font-semibold">{{ __('messages.offices.col_address') }}</th>
                    <th scope="col" class="px-4 py-3 font-semibold">{{ __('messages.offices.col_phone') }}</th>
                    <th scope="col" class="px-4 py-3 font-semibold">{{ __('messages.offices.col_hours') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0]">
                @foreach($offices as $office)
                <tr class="hover:bg-[#F8FAFC] transition-colors">
                    <th scope="row" class="px-4 py-3 font-semibold text-[#1E293B]">
                        <a href="#office-{{ $office->id() }}" class="hover:text-[#00346f]">
                            {{ $office->city()->get(app()->getLocale()) }}
                        </a>
                    </th>
                    <td class="px-4 py-3 text-[#424751]">
                        {{ $office->address()->get(app()->getLocale()) }}
                    </td>
                    <td class="px-4 py-3 text-[#424751]">
                        @if($office->phone())
                            <a href="tel:{{ $office->phone() }}" class="hover:text-[#00346f]">
                                {{ $office->phone() }}
                            </a>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[#64748B] text-[13px]">
                        {{ \Illuminate\Support\Str::limit(
                            $office->openingHours()?->get(app()->getLocale())
                                ?? $office->openingHours()?->get('ca')
                                ?? '',
                            40
                        ) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif
```

### 7.4 Snippet: Bloque por oficina (sustituir el grid actual)

```blade
{{-- Per-office semantic blocks with H2 + <address> + unique content + service area --}}
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pb-28">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($offices as $office)
        <article id="office-{{ $office->id() }}" itemscope itemtype="https://schema.org/LocalBusiness" class="group bg-white rounded-[1.5rem] border border-[#E2E8F0] overflow-hidden hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
            {{-- Image with proper alt text --}}
            <div class="relative h-[220px] overflow-hidden">
                @if($office->coverUrl())
                    <img src="{{ $office->coverUrl() }}"
                         alt="{{ $office->imageAlt()?->get(app()->getLocale()) ?? $office->imageAlt()?->get('ca') ?? $office->name()->get(app()->getLocale()) }}"
                         itemprop="image"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#00346f]/10 to-[#00B4D8]/20 flex items-center justify-center">
                        <span class="font-headline text-[80px] font-bold text-[#00346f]/10 select-none" aria-hidden="true">
                            {{ mb_substr($office->city()->get(app()->getLocale()), 0, 1) }}
                        </span>
                    </div>
                @endif
                <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-4 py-1.5 rounded-full shadow-sm">
                    <span class="text-[13px] font-semibold text-[#00346f]" itemprop="addressLocality">
                        {{ $office->city()->get(app()->getLocale()) }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6">
                {{-- H2 = one per office — required for SEO local differentiation --}}
                <h2 class="font-headline text-[22px] font-semibold text-[#1E293B] mb-3 leading-tight" itemprop="name">
                    {{ __('messages.offices.office_in') }} {{ $office->city()->get(app()->getLocale()) }}
                </h2>

                {{-- Semantic <address> with full NAP — strong local SEO signal --}}
                <address itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" class="not-italic flex flex-col gap-2.5 mb-4">
                    <meta itemprop="streetAddress" content="{{ $office->address()->get(app()->getLocale()) }}">
                    <meta itemprop="addressLocality" content="{{ $office->city()->get(app()->getLocale()) }}">
                    <meta itemprop="addressRegion" content="Barcelona">
                    <meta itemprop="addressCountry" content="ES">

                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#00B4D8] mt-0.5 flex-shrink-0" aria-hidden="true">location_on</span>
                        <span class="text-[14px] text-[#424751] leading-snug">
                            {{ $office->address()->get(app()->getLocale()) }}
                        </span>
                    </div>

                    @if($office->phone())
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0" aria-hidden="true">call</span>
                        <a href="tel:{{ $office->phone() }}"
                           itemprop="telephone"
                           class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                            {{ $office->phone() }}
                        </a>
                    </div>
                    @endif

                    @if($office->email())
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0" aria-hidden="true">mail</span>
                        <a href="mailto:{{ $office->email() }}"
                           itemprop="email"
                           class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                            {{ $office->email() }}
                        </a>
                    </div>
                    @endif

                    @if($office->openingHours())
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] mt-0.5 flex-shrink-0" aria-hidden="true">schedule</span>
                        <span class="text-[14px] text-[#64748B] leading-snug whitespace-pre-line">
                            {{ $office->openingHours()->get(app()->getLocale()) ?? $office->openingHours()->get('ca') }}
                        </span>
                    </div>
                    @endif
                </address>

                {{-- Unique content per office (min 100-150 words) --}}
                @if($office->description())
                <p class="text-[14px] text-[#424751] leading-relaxed mb-4">
                    {{ $office->description()->get(app()->getLocale()) ?? $office->description()->get('ca') }}
                </p>
                @endif

                {{-- Service area — long-tail local SEO --}}
                @php $serviceArea = $office->serviceAreaList(app()->getLocale()); @endphp
                @if($serviceArea !== [])
                <div class="mb-5">
                    <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#64748B] mb-2">
                        {{ __('messages.offices.also_serving') }}
                    </p>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach($serviceArea as $area)
                        <li class="text-[12px] text-[#00346f] bg-[#00346f]/8 px-2.5 py-1 rounded-full border border-[#00346f]/15">
                            {{ $area }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- CTA: "Cómo llegar" → Google Maps directions (link saliente, igual que hoy) --}}
                @if($office->lat() !== null && $office->lng() !== null)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->lat() }},{{ $office->lng() }}"
                   target="_blank" rel="noopener noreferrer"
                   itemprop="hasMap"
                   class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#00346f] border-b-2 border-[#00346f]/30 hover:border-[#00346f] pb-0.5 transition-colors w-fit group/link">
                    {{ __('messages.offices.directions') }}
                    <span class="material-symbols-outlined text-[16px] transition-transform group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5" aria-hidden="true">&#xf8ce;</span>
                </a>
                @endif
            </div>
        </article>
        @endforeach
    </div>
</section>
```

### 7.5 Cambios secundarios

- **Eliminar el contador "N oficinas" del hero** (reemplazado por la tabla resumen).
- **Mantener el script de scroll-to-anchor** (sigue funcionando con los `id="office-{id}"`).
- **Mantener el mapa Leaflet global** (sigue siendo útil para vista general).

---

## 8. Capa 6 — Traducciones i18n

**Archivos**: `resources/lang/{ca,es,en}/messages.php`

### 8.1 Reemplazar bloque `offices` existente

```php
// ca/messages.php
'offices' => [
    'title'             => 'Les nostres oficines',
    'subtitle'          => 'Trobarem sempre una oficina a prop teu.',
    'seo_title'         => 'Oficines d\'assessoria fiscal, laboral i comptable',
    'seo_description'   => '6 oficines d\'AGC Assessors al Vallès Oriental i Osona. Assessoria fiscal, laboral i comptable amb atenció personalitzada. Troba la més propera.',
    'offices_count'     => 'oficines',
    'summary_heading'   => 'Resum de les oficines',
    'summary_caption'   => 'Llista completa de les oficines d\'AGC Assessors',
    'col_city'          => 'Ciutat',
    'col_address'       => 'Adreça',
    'col_phone'         => 'Telèfon',
    'col_hours'         => 'Horari',
    'office_in'         => 'Oficina de',
    'also_serving'      => 'També atenem a',
    'empty'             => 'Les nostres oficines es publicaran aviat.',
    'directions'        => 'Com arribar',
],
```

```php
// es/messages.php
'offices' => [
    'title'             => 'Nuestras oficinas',
    'subtitle'          => 'Siempre encontrarás una oficina cerca de ti.',
    'seo_title'         => 'Oficinas de asesoría fiscal, laboral y contable',
    'seo_description'   => '6 oficinas de AGC Assessors en el Vallès Oriental y Osona. Asesoría fiscal, laboral y contable con atención personalizada. Encuentra la más cercana.',
    'offices_count'     => 'oficinas',
    'summary_heading'   => 'Resumen de oficinas',
    'summary_caption'   => 'Lista completa de las oficinas de AGC Assessors',
    'col_city'          => 'Ciudad',
    'col_address'       => 'Dirección',
    'col_phone'         => 'Teléfono',
    'col_hours'         => 'Horario',
    'office_in'         => 'Oficina de',
    'also_serving'      => 'También atendemos en',
    'empty'             => 'Nuestras oficinas se publicarán pronto.',
    'directions'        => 'Cómo llegar',
],
```

```php
// en/messages.php
'offices' => [
    'title'             => 'Our offices',
    'subtitle'          => 'You will always find an office near you.',
    'seo_title'         => 'Tax, labour and accounting advisory offices',
    'seo_description'   => '6 AGC Assessors offices in Vallès Oriental and Osona. Tax, labour and accounting advisory with personalized service. Find the closest one.',
    'offices_count'     => 'offices',
    'summary_heading'   => 'Office summary',
    'summary_caption'   => 'Complete list of AGC Assessors offices',
    'col_city'          => 'City',
    'col_address'       => 'Address',
    'col_phone'         => 'Phone',
    'col_hours'         => 'Hours',
    'office_in'         => 'Office in',
    'also_serving'      => 'Also serving',
    'empty'             => 'Our offices will be published soon.',
    'directions'        => 'Get directions',
],
```

### 8.2 Rellenar contenido único por oficina

**Esto es manual** desde el panel de Filament (o por tinker/seed). Para cada oficina hay que rellenar:

- **Title SEO / Description SEO**: ya viene del i18n global (no es por oficina). Si más adelante se quiere SEO por oficina, hay que crear columnas `seo_title` y `seo_description` en la tabla `offices`. **Fuera del alcance de este PR** (se puede hacer en PR-2 si el cliente lo pide).
- **`description`**: 100-150 palabras únicas por sede, en ca, es, en.
- **`opening_hours`**: formato libre multilínea, una línea por día/grupo de días.
- **`service_area`**: pueblos/zonas atendidas, uno por línea.
- **`image_alt`**: alt text de la imagen de portada, en ca, es, en.

**Plantilla sugerida para `description`** (en español):

> *"Nuestra oficina de [Ciudad] abrió sus puertas en [año] en el corazón de [zona/comarca]. Más de [X] años después, seguimos atendiendo a empresas, autónomos y particulares de [Ciudad] y municipios cercanos. Liderada por [nombre del responsable de la sede, si aplica], nuestro equipo local conoce en profundidad la realidad económica y fiscal de la zona. Especializados en [sector/servicio destacado], ofrecemos atención cercana con el respaldo de un equipo multidisciplinar de más de [N] profesionales."*

---

## 9. Sitemap XML

**Archivo**: `app/Http/Controllers/Public/SitemapController.php`

Verificar que `/oficinas` está incluida con sus variantes de idioma. La línea 24 ya muestra que existe:

```php
'/oficines' => 'offices.index',
```

Confirmar que también incluye `/es/oficinas` y `/en/offices`. Si no, añadirlas con el mismo `route()`.

> No debería hacer falta tocar nada — solo verificar que están las 3 variantes.

---

## 10. Verificación local (criterios de aceptación)

Antes de merge, ejecutar estas comprobaciones en local con `php artisan serve` o `docker compose exec php php artisan serve`.

### 10.1 Schema LocalBusiness

```bash
# Comprobar que hay 6 schemas LocalBusiness en la página
curl -s http://localhost:8080/es/oficinas | grep -c '"@type": "LocalBusiness"'
# Esperado: 6
```

Alternativa con DevTools (como auditoría manual):

```javascript
// En la consola del navegador, en /es/oficinas
[...document.querySelectorAll('script[type="application/ld+json"]')].map(s => {
  const j = JSON.parse(s.textContent);
  if (j['@type'] === 'LocalBusiness') return j.name;
  if (j['@graph']) return j['@graph'].filter(g => g['@type'] === 'LocalBusiness').map(g => g.name);
  return null;
}).flat().filter(Boolean);
```

### 10.2 NAP semántico

```javascript
// Debe haber 6 <address> con itemprop="telephone"
document.querySelectorAll('address[itemprop="address"]').length;  // → 6
document.querySelectorAll('[itemprop="telephone"]').length;        // → 6
```

### 10.3 H2 diferenciados

```javascript
// Debe haber 6 H2 con "Oficina de [ciudad]"
[...document.querySelectorAll('h2')].map(h => h.textContent.trim());
// → ["Oficina de Caldes de Montbui", "Oficina de Sant Celoni", ...]
```

### 10.4 Hreflang

```javascript
// Debe seguir emitiendo ca, es, en, x-default
[...document.querySelectorAll('link[rel="alternate"][hreflang]')].map(l => l.hreflang);
// → ["ca", "en", "es", "x-default"]
```

### 10.5 Rich Results Test

URL: https://search.google.com/test/rich-results
Input: `https://agcassessors.com/es/oficinas`
Esperado: 0 errores, 6 `LocalBusiness` validados, 0 warnings críticos.

### 10.6 Lighthouse / PageSpeed

- Performance ≥ 85
- SEO ≥ 95
- Best Practices ≥ 95
- Accesibilidad ≥ 90 (la tabla con `<th scope>` y los `<address>` semánticos suman)

### 10.7 Schema en `messages.php`

```bash
docker compose exec php php artisan tinker --execute="echo trans('messages.offices.seo_title');"
```

Debe devolver el título SEO nuevo en el idioma actual.

### 10.8 Limpieza de cache

```bash
docker compose exec php php artisan view:clear
docker compose exec php php artisan cache:clear
docker compose exec php php artisan config:clear
```

---

## 11. Riesgos y rollback

### 11.1 Riesgos identificados

| Riesgo | Probabilidad | Mitigación |
|---|---|---|
| Romper el render del mapa Leaflet al tocar el blade | Media | El bloque del mapa es independiente del nuevo bloque. No tocar la sección del mapa. |
| Conflicto en el constructor de `Office` (signature change) | Alta | `final class Office` con constructor `readonly` — añadir parámetros es BC-break. **Actualizar TODOS los `new Office(...)` del repo** antes de mergear. |
| Service binding no registrado | Media | Verificar `OfficeRepositoryInterface` está bound en un provider. Si no, añadir. |
| Schema JSON-LD mal formado → Google ignora todos | Baja | Validar con Rich Results Test antes de producción. |
| Alt text vacío masivo en imágenes previas | Baja | El `?? $office->name()->get(locale)` cae al nombre de la oficina como fallback. |
| Cache de vistas sirve blade viejo | Alta | `view:clear` + recargar con `Ctrl+Shift+R`. |

### 11.2 Plan de rollback

Si algo falla en producción, los pasos son reversibles individualmente:

1. **Revertir blade**: `git checkout HEAD -- resources/views/public/pages/offices/index.blade.php`
2. **Revertir SeoComposer**: `git checkout HEAD -- app/Http/View/Composers/SeoComposer.php`
3. **Revertir migración** (solo si rompe): `php artisan migrate:rollback --step=1`
4. **Revertir i18n**: `git checkout HEAD -- resources/lang/`

**Los cambios de Filament Resource y EloquentOffice NO se revierten fácilmente** si se han metido datos. Antes del primer despliegue, hacer backup de la tabla:

```bash
docker compose exec php php artisan db:show
mysqldump -u user -p agc offices > offices_backup_$(date +%Y%m%d).sql
# O en Postgres:
pg_dump -t offices agc > offices_backup_$(date +%Y%m%d).sql
```

---

## 12. Orden de ejecución

Para minimizar el riesgo, ejecutar en este orden. Cada paso debe pasar su verificación antes de continuar.

| Paso | Acción | Verificación |
|---|---|---|
| **1** | Backup de la tabla `offices` | `pg_dump -t offices agc > backup.sql` |
| **2** | Backup de la tabla `migrations` (o snapshot) | Anotar última migración ejecutada |
| **3** | Crear rama: `git checkout -b feat/local-seo-offices` | OK |
| **4** | Crear migración `2026_06_10_120000_add_local_seo_fields_to_offices.php` | `php artisan migrate:status` la ve como pendiente |
| **5** | Ejecutar migración: `docker compose exec php php artisan migrate` | `pg_dump -t offices` muestra las nuevas columnas nullable |
| **6** | Actualizar `EloquentOffice.php` con nuevos `$translatable`, `$fillable`, `$casts` | `php -l` no da error de sintaxis |
| **7** | Actualizar entidad `Office` con nuevos getters | `grep -rn "new Office(" src/` para localizar callers |
| **8** | Actualizar todos los call sites de `new Office(...)` (probablemente en `EloquentOfficeRepository`) | Tests pasan / app carga |
| **9** | Actualizar `OfficeResource.php` (form + table) | Cargar `/admin/offices` y ver los nuevos campos |
| **10** | Añadir método `getOfficesLocalBusinessSchemas()` y constructor con DI en `SeoComposer` | `php -l` OK, página `/es/oficinas` no rompe |
| **11** | Verificar binding de `OfficeRepositoryInterface` en providers | `php artisan tinker` → `app(OfficeRepositoryInterface::class)` |
| **12** | Reemplazar `messages.php` (ca, es, en) con nuevas claves `offices.*` | `php artisan tinker --execute="dump(trans('messages.offices'));"` |
| **13** | Reemplazar `resources/views/public/pages/offices/index.blade.php` con la nueva estructura | Página carga, mapa Leaflet intacto |
| **14** | Ejecutar todas las verificaciones de §10 | Todas en verde |
| **15** | Commit con mensaje conventional: `feat(seo): local multi-office schema + summary table for /oficinas` | OK |
| **16** | Validar en Google Rich Results Test | 0 errores |
| **17** | Validar en PageSpeed Insights | Score ≥ 85 |
| **18** | Mergear a `main` / abrir PR | Revisión por pares |

---

## Anexo: comando de búsqueda útil

```bash
# Encontrar todos los call sites de la entidad Office
grep -rn "new Office(" src/

# Encontrar todos los usos de SeoComposer
grep -rn "SeoComposer" app/

# Verificar que la migración es la última
ls database/migrations/ | tail -5

# Comprobar que los schemas se emiten
curl -s http://localhost:8080/es/oficinas | grep -c "LocalBusiness"
```

---

**Fin del plan.** Cambios contenidos, reversibles individualmente, y cada paso tiene verificación automática. Si se ejecuta en orden y pasa todas las verificaciones, el resultado es: 6 schemas `LocalBusiness` válidos, 1 tabla resumen accesible, 6 bloques H2 con NAP semántico, y 6 oficinas con contenido único — exactamente lo que Google necesita para posicionar AGC en búsquedas locales.
