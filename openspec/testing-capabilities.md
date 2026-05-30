con# AGC Assessors — Testing Capabilities & Quality Gates
# =============================================================================
# Generated: 2025-05-29
# Scope: Strict TDD enforcement (level 8 PHPStan, Pest/PHPUnit, Laravel Pint)
# =============================================================================

## Testing Matrix

| Layer | Test Type | Tool | Status | Command | Location |
|-------|-----------|------|--------|---------|----------|
| Domain | Unit | Pest/PHPUnit | Enabled | `make test` | tests/Unit/ |
| Infrastructure | Feature | Pest/PHPUnit | Enabled | `make test` | tests/Feature/ |
| Filament | Feature | Pest/PHPUnit | Enabled | `make test` | tests/Feature/Filament/ |
| Http/Public | Feature | Pest/PHPUnit | Enabled | `make test` | tests/Feature/Http/ |
| E2E | Integration | Playwright | Prepared | TBD | tests/E2E/ |

## Code Quality Gates

### PHPStan (Static Analysis)
- **Level**: 8 (strict)
- **Command**: `make phpstan`
- **Docker**: `docker compose exec php vendor/bin/phpstan analyse --memory-limit=1G`
- **Config**: `phpstan.neon` (auto-generated on `composer install`)
- **Baseline**: `phpstan-baseline.neon` (optional, for legacy code)
- **Gate**: Must pass level 8 before merge
- **Enforcement**: Pre-commit hook (via `composer lint`)

### Laravel Pint (Code Style)
- **Standard**: PSR-12 (Laravel conventions)
- **Command**: `make pint`
- **Dry Run**: `make pint-dry`
- **Docker**: `docker compose exec php vendor/bin/pint`
- **Auto Fix**: Enabled on every run
- **Enforcement**: Pre-commit hook (via Laravel Pint installer)

### Code Coverage
- **Target**: > 70% for Domain layer
- **Command**: `make test-coverage`
- **Tool**: PHPUnit Code Coverage
- **Reports**: tests/coverage/ (HTML report)

## Test Isolation & Conventions

### Unit Tests (Domain Layer)
- **Purpose**: Test business logic in isolation (Actions, ValueObjects, Repositories)
- **Setup**: Pest syntax (`test()` or `it()`)
- **Mocking**: Mock Eloquent dependencies via Repository interfaces
- **Database**: NO database access (pure logic)
- **Location**: `tests/Unit/Domain/` (e.g., `tests/Unit/Domain/Posts/CreatePostActionTest.php`)

### Feature Tests (Infrastructure + Http)
- **Purpose**: Test Eloquent models, repositories, Filament resources, HTTP routes
- **Setup**: Pest syntax with `test()` databases trait
- **Database**: Wrapped in transactions (auto-rollback per test)
- **Factories**: Use Eloquent factories for fixtures
- **Location**: `tests/Feature/` (e.g., `tests/Feature/Posts/PostRepositoryTest.php`)

### E2E Tests (Deferred)
- **Tool**: Playwright
- **Purpose**: Full browser testing of user workflows
- **Status**: Infrastructure prepared, implementation deferred to phase 2
- **Location**: `tests/E2E/` (to be created)

## Pre-Commit Hooks (Recommended)

Add to `.git/hooks/pre-commit`:
```bash
#!/bin/bash
docker compose exec php composer lint
docker compose exec php composer test
```

Or use `captainhook` (Laravel auto-installer via composer):
```bash
composer require captainhook/captainhook --dev
composer require captainhook/hook-installer --dev
```

## CI/CD (Future Phase)

Recommended pipeline (to be configured on GitHub/GitLab):
1. Lint (Pint)
2. Static Analysis (PHPStan level 8)
3. Unit Tests (Pest)
4. Feature Tests (Pest)
5. Code Coverage (70% threshold)
6. Build Docker image
7. Deploy to staging

## Troubleshooting

### Tests fail with "could not find package"
- Run `docker compose exec php composer install`

### PHPStan memory limit exceeded
- Already set to 1G in Makefile; if still fails, increase to 2G

### Pint conflicts with project style
- Check `.pint.json` or `pint.json` in project root (should inherit Laravel preset)

## Next Steps

1. ✅ Testing framework configured (Pest + PHPUnit)
2. ✅ PHPStan level 8 enforced
3. ⏳ Write first unit test for Domain\Posts\CreatePostAction
4. ⏳ Implement CreatePostAction logic
5. ⏳ Configure pre-commit hooks
6. ⏳ Set up GitHub Actions / GitLab CI

---

**Last Updated**: 2025-05-29  
**Responsibility**: SDD-init phase (automated)
