## Exploration: AGC Assessors MVP

### Current State
The PRD is coherent and mostly ready for proposal, but it mixes a few architectural conventions:
- OpenSpec says the artifact store is file-based and the project is still pre-Laravel bootstrap.
- The PRD defines a Clean Architecture split, but some files blur layer boundaries (for example, `Domain/*/Models` plus `SEOData::fromPost()` relying on translation accessors).
- Locale, SEO, homepage zones, services/offices, and forms are all defined, but some rules conflict or need normalization.

Main inconsistency found: locale fallback is not stable. The PRD says fallback should be `es` then `ca`, while project config says default/fallback is `ca`.

### Affected Areas
- `app/Domain/**` — domain entities, actions, value objects, repository contracts.
- `app/Infrastructure/**` — Eloquent models, mappers, repository implementations, media handling.
- `app/Http/**` — locale middleware, controllers, requests, view models, routes.
- `app/Filament/**` — CRUD resources, homepage zone editor, SEO fields.
- `openspec/config.yaml` — already states project-wide fallback/stack decisions; should align with PRD.
- `openspec/changes/agc-mvp/*` — proposal/spec/design will depend on the decisions locked here.

### Approaches
1. **Strict Clean Architecture with thin adapters** — keep Domain fully framework-agnostic; all Spatie/Filament/Eloquent concerns stay in Infrastructure/Http/Filament.
   - Pros: strongest testability, easiest future API/CLI reuse, clear boundaries.
   - Cons: more mapping code, more upfront ceremony.
   - Effort: Medium

2. **Pragmatic Clean Architecture** — allow a few framework-aware helpers in Domain-adjacent value objects where they reduce boilerplate.
   - Pros: faster delivery, less mapping overhead.
   - Cons: boundary drift risk, harder to keep PHPStan/architecture clean over time.
   - Effort: Low

### Recommendation
Use **Strict Clean Architecture with thin adapters**. The PRD already commits to domain purity and future extensibility; the MVP scope is large enough that boundary shortcuts will become maintenance debt quickly.

### Risks
- Locale fallback conflict (`es` vs `ca`) can create SEO and UX inconsistencies.
- Homepage dynamic zones need a concrete storage model (single polymorphic table vs JSON blob vs section tables).
- “Exactly 6 services / 6 offices” is a content invariant that must be enforced by seeders and admin validation, not just docs.
- `spatie/laravel-translatable` can leak into Domain if translation helpers are used directly there.
- `SEOData` and schema generation need a stable contract so controllers/views do not duplicate logic.

### Ready for Proposal
Yes, with clarifications. The orchestrator should tell the user we can proceed once these are confirmed: locale fallback rule, homepage zone storage model, and whether the “exactly 6” content rules are hard constraints or seeded MVP data.
