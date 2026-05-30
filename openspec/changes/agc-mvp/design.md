# Design: AGC Assessors MVP

## Technical Approach

Migrate the static CMS site to Laravel 13 + Filament 5 using Strict Clean Architecture. The Domain layer is completely isolated from the framework (no Eloquent, no Spatie helpers, no Filament dependencies). It uses Value Objects for SEO, single-responsibility Actions for CQRS-lite operations, and Repository interfaces. The Infrastructure layer implements these repositories with Eloquent models, bridging data with Mappers. The Http/Filament layers consume Domain Actions to perform reads/writes. The frontend uses Alpine.js for interactivity and Tailwind 4 for styling, strictly scoped and optimized.

## Architecture Decisions

### Decision: Eloquent-Domain Mapping
**Choice**: Explicit Mapper classes (e.g., `PostMapper`) that map Eloquent models to Domain entities and vice versa.
**Alternatives considered**: Direct use of Eloquent models in the domain, or generic AutoMapper libraries.
**Rationale**: Keeps Domain completely independent of Laravel. Explicit mappers provide clear type safety and predictability without reflection overhead.

### Decision: SEOData Value Object Location
**Choice**: `SEOData` resides in the Domain layer as a pure PHP Value Object. Static factories (`SEOData::fromPost`) receive Domain entities.
**Alternatives considered**: Eloquent accessors returning arrays or putting SEOData in the Infrastructure.
**Rationale**: SEO representation is core business logic for public display. Ensuring it takes Domain models prevents Eloquent leakage.

### Decision: Filament Business Logic
**Choice**: Filament Resources interact exclusively through Domain Actions and Repositories.
**Alternatives considered**: Standard Filament Eloquent queries and inline mutation hooks.
**Rationale**: Filament should only be a UI adapter. Relying on Domain Actions guarantees the exact same business rules apply whether triggered by an API, a command line, or Filament.

### Decision: Multi-language Implementation
**Choice**: `mcamara/laravel-localization` handles Http routing and session middleware; `spatie/laravel-translatable` manages JSON storage in Infrastructure.
**Alternatives considered**: Custom routing, separate tables per language.
**Rationale**: Proven packages that solve edge cases. Keeping them in their respective bounded contexts ensures Clean Architecture compliance while benefiting from the ecosystem.

## Data Flow

    User / Admin
         │ (HTTP Request)
         ▼
    Http Layer (Controllers / Filament Resources)
         │ (Calls single-responsibility Action)
         ▼
    Domain Layer (Actions -> e.g., CreatePostAction)
         │ (Passes Domain Entity / DTO)
         ▼
    Domain Repositories (Interface)
         │ (Method Call)
         ▼
    Infrastructure Layer (EloquentRepositories)
         │ (Mapper converts Domain <-> Eloquent)
         ▼
    MariaDB Database (via Eloquent Models)

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Domain/Post/Entities/Post.php` | Create | Pure PHP Domain Entity |
| `app/Domain/Shared/ValueObjects/SEOData.php` | Create | Domain VO for SEO tags |
| `app/Domain/Post/Repositories/PostRepositoryInterface.php` | Create | Interface for data access |
| `app/Domain/Post/Actions/CreatePostAction.php` | Create | Business logic to create posts |
| `app/Infrastructure/Persistence/Eloquent/Models/Post.php` | Create | Eloquent model with spatie/translatable |
| `app/Infrastructure/Persistence/Mappers/PostMapper.php` | Create | Maps Eloquent Post <-> Domain Post |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentPostRepository.php` | Create | Implements interface |
| `app/Http/Controllers/PostController.php` | Create | Handles web requests and invokes actions |
| `app/Filament/Resources/PostResource.php` | Create | Admin CRUD adapter using Actions |
| `app/Providers/RepositoryServiceProvider.php` | Create | Binds interfaces to Eloquent implementations |
| `database/migrations/xxxx_create_content_sections_table.php` | Create | Dynamic homepage zones table |

## Interfaces / Contracts

```php
// Domain/Post/Repositories/PostRepositoryInterface.php
namespace App\Domain\Post\Repositories;

use App\Domain\Post\Entities\Post;
use Illuminate\Support\Collection;

interface PostRepositoryInterface
{
    public function findById(int $id): ?Post;
    public function findBySlug(string $slug, string $locale): ?Post;
    public function save(Post $post): Post;
    public function delete(int $id): bool;
}
```

```php
// Domain/Shared/ValueObjects/SEOData.php
namespace App\Domain\Shared\ValueObjects;

final readonly class SEOData
{
    public function __construct(
        public string $title,
        public string $description,
        public array $openGraph,
    ) {}
}
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit (Domain) | Actions, VOs, Entities | Strict TDD (Pest). No framework boot. Mock Repositories. |
| Integration (Infra) | Eloquent Repositories, Mappers | Pest tests hitting an in-memory SQLite or test MariaDB with transactions. |
| Feature (Http/Web) | Controllers, Routing, Middleware | Pest tests for HTTP status codes, correct view data, and locale fallback assertions. |

## Migration / Rollout

No data migration required as this is a greenfield MVP replacing a static CMS.
Deployment via Docker Compose on the production server after testing.

## Open Questions

- [ ] Will the `content_sections` JSON payloads require validation classes inside the Domain layer, or can the FormRequest / Filament handle validation purely in the Http layer?
- [ ] Should the Spatie Media Library URL generation be abstracted behind a Domain interface to prevent exposing Spatie URLs directly to the Domain?