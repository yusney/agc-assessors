# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated for this application. Follow them closely.

## Foundational Context

- **PHP**: 8.4
- **Laravel**: 13
- **Filament**: 5
- **Stack**: PostgreSQL, Redis, Alpine.js, Tailwind CSS 4, Vite, pnpm
- **Architecture**: Clean Architecture — `AGC\Domain`, `AGC\Application`, `AGC\Infrastructure`, `AGC\Filament`

**Application purpose**: AGC Assessors — professional advisory firm website (fiscal, labour, accounting). Multi-language (ca/es/en), public frontend + Filament admin panel.

---

## Filament 5 — Critical Rules

> Source: https://filamentphp.com/docs/5.x — fetch `.md` URLs for any specific page.
> Full doc index: https://filamentphp.com/docs/llms.txt

### Namespaces (most common errors)

| Class | Correct namespace in Filament 5 |
|---|---|
| `EditAction` | `Filament\Actions\EditAction` |
| `DeleteAction` | `Filament\Actions\DeleteAction` |
| `CreateAction` | `Filament\Actions\CreateAction` |
| `ViewAction` | `Filament\Actions\ViewAction` |
| `ReplicateAction` | `Filament\Actions\ReplicateAction` |
| `RestoreAction` | `Filament\Actions\RestoreAction` |
| `ForceDeleteAction` | `Filament\Actions\ForceDeleteAction` |
| `Section` (schema layout) | `Filament\Schemas\Components\Section` |
| `Tabs` (schema layout) | `Filament\Schemas\Components\Tabs` |
| `Grid` (schema layout) | `Filament\Schemas\Components\Grid` |
| `form()` schema type | `Filament\Schemas\Schema` |
| `TextColumn` | `Filament\Tables\Columns\TextColumn` ✅ still here |
| `IconColumn` | `Filament\Tables\Columns\IconColumn` ✅ still here |
| `TernaryFilter` | `Filament\Tables\Filters\TernaryFilter` ✅ still here |

### Resource method signatures

```php
// CORRECT in Filament 5
public static function form(Schema $schema): Schema  // NOT Form $form
public static function table(Table $table): Table    // unchanged

// Property types
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-...';
protected static string|\UnitEnum|null $navigationGroup = 'Content';
// NOT: protected static ?string $navigationIcon  ← causes fatal error
```

### Actions in table()->actions([...])

```php
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

->actions([
    EditAction::make(),
    DeleteAction::make(),
])
// NOT: Tables\Actions\EditAction  ← class does not exist in Filament 5
```

### Schema layouts

```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Title')->schema([...]),
        Tabs::make('Name')->tabs([
            Tabs\Tab::make('Tab')->schema([...]),
        ]),
    ]);
}
```

### Documentation URLs (fetch on demand)

- Actions overview: https://filamentphp.com/docs/5.x/actions/overview.md
- Edit action: https://filamentphp.com/docs/5.x/actions/edit.md
- Delete action: https://filamentphp.com/docs/5.x/actions/delete.md
- Resources overview: https://filamentphp.com/docs/5.x/resources/overview.md
- Listing records: https://filamentphp.com/docs/5.x/resources/listing-records.md
- Schema layouts: https://filamentphp.com/docs/5.x/schemas/layouts.md
- Schema sections: https://filamentphp.com/docs/5.x/schemas/sections.md
- Schema tabs: https://filamentphp.com/docs/5.x/schemas/tabs.md
- Table actions: https://filamentphp.com/docs/5.x/tables/actions.md
- Table columns: https://filamentphp.com/docs/5.x/tables/columns/overview.md
- Table filters (ternary): https://filamentphp.com/docs/5.x/tables/filters/ternary.md
- Forms overview: https://filamentphp.com/docs/5.x/forms/overview.md
- Panel configuration: https://filamentphp.com/docs/5.x/panel-configuration.md

---

## Project-Specific Conventions

### Docker commands

```bash
# PHP / Artisan
docker compose exec php php artisan <cmd>

# Fix permissions before artisan (www-data = UID 33)
docker run --rm -v "$(pwd):/work" -u root alpine sh -c "chown -R 33:33 /work/storage /work/bootstrap/cache"

# Fix permissions for host editing (UID 1000)
docker run --rm -v "$(pwd):/work" -u root alpine sh -c "chown -R 1000:1000 /work"

# Frontend build
docker compose run --rm node sh -c "npm install -g pnpm && pnpm build"
```

### Architecture rules

- `AGC\Domain\*` — zero framework dependencies. Pure PHP entities, value objects, repository interfaces.
- `AGC\Application\*` — use cases. Depends only on domain interfaces.
- `AGC\Infrastructure\*` — Eloquent models + repositories. Implements domain interfaces.
- `AGC\Filament\*` — Filament Resources. Uses Eloquent models directly (NOT domain entities).
- `App\*` — Laravel controllers, providers, middleware.
- **Never** import Laravel/Filament classes into `AGC\Domain\*`.

### Namespace binding

```json
"AGC\\": "src/",
"App\\": "app/"
```

### Multi-language

- Locales: `ca` (default, no URL prefix), `es` (`/es/...`), `en` (`/en/...`)
- Package: `mcamara/laravel-localization`
- Translations: `resources/lang/{ca,es,en}/messages.php`
- Translatable models: `spatie/laravel-translatable` — fields stored as JSON `{"ca":"..","es":"..","en":".."}`
- In Blade: `$model->field()->get(app()->getLocale())`
- In routes: `LaravelLocalization::getLocalizedURL($locale, '/path')`

### CSS / Frontend

- Tailwind 4 — tokens in `@theme {}` block in `app.css`. No `tailwind.config.js`.
- Design system: Outfit (headlines), Inter (body), primary `#00346f`, accent `#00B4D8`
- Material Symbols Outlined via Google Fonts CDN in `layouts/public.blade.php`
- Alpine.js for interactivity
- pnpm lockfile: `pnpm-lock.yaml`

### Admin panel

- URL: `http://localhost:8080/admin`
- Login: `admin@agcassessors.com` / `Admin*123`
- Provider: `app/Providers/Filament/AdminPanelProvider.php`

---

## Conventions

- `declare(strict_types=1)` in every PHP file.
- `final class` for entities, value objects, use cases, and controllers.
- Repository interfaces return domain entities, never Eloquent models.
- Eloquent models live in `AGC\Infrastructure\Persistence\Eloquent\Models\`.
- When editing Filament Resources, always add explicit `use` imports — never rely on `Tables\Actions\*` alias.
- Check sibling files for naming conventions before creating new ones.

## Verification

```bash
# Syntax check
docker compose exec php php -l src/Filament/Resources/NewsResource.php

# Routes
docker compose exec php php artisan route:list

# HTTP smoke test
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/
```
