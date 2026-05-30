# .atl/skill-registry.md — SDD Skill Registry
# =============================================================================
# AGC Assessors Project
# Generated: 2025-05-29
# =============================================================================

## Available Skills for This Project

### SDD Lifecycle Skills

| Skill | Trigger | Purpose | Status |
|-------|---------|---------|--------|
| sdd-explore | "explore", "sdd explore" | Clarify requirements before committing to a change | ✅ Ready |
| sdd-propose | "propose", "sdd propose" | Create a proposal with intent, scope, and tradeoffs | ✅ Ready |
| sdd-spec | "spec", "sdd spec", "especificar" | Write delta specs with requirements and test scenarios | ✅ Ready |
| sdd-design | "design", "sdd design" | Architecture & design decisions for the change | ✅ Ready |
| sdd-tasks | "tasks", "sdd tasks" | Break change into reviewable work units | ✅ Ready |
| sdd-apply | "apply", "sdd apply" | Implement tasks from specs | ✅ Ready |
| sdd-verify | "verify", "sdd verify", "verificar" | Run tests and prove implementation matches specs | ✅ Ready |
| sdd-archive | "archive", "sdd archive" | Sync delta specs after implementation | ✅ Ready |
| sdd-onboard | "onboard", "sdd onboard" | Walk through full SDD cycle on real codebase | ✅ Ready |

### Domain-Specific Skills

| Skill | Trigger | Applies To | Status |
|-------|---------|-----------|--------|
| nestjs-best-practices | Writing NestJS code | Not applicable (Laravel project) | ❌ N/A |
| react-19 | Writing React components | Not applicable (Blade templates + Filament) | ❌ N/A |
| tailwind-4 | Styling with Tailwind CSS | ✅ Homepage + Filament custom CSS | ✅ Ready |
| go-testing | Go test coverage, Bubbletea teatest | Not applicable (PHP project) | ❌ N/A |
| playwright | E2E testing with Playwright | Prepared for future E2E phase | ⏳ Deferred |
| typescript | TypeScript + strict patterns | Minimal (Node 24 for Vite, not primary) | ⏳ Optional |
| laravel-specialist | Laravel 13, Eloquent, APIs, Queues, Livewire | ✅ Backend implementation | ✅ Ready |
| supabase-postgres-best-practices | Postgres performance | ✅ PostgreSQL 16 - fully applicable | ✅ Ready |
| tauri-v2 | Tauri cross-platform apps | Not applicable (Web project) | ❌ N/A |

### General Development Skills

| Skill | Trigger | Applies To | Status |
|-------|---------|-----------|--------|
| branch-pr | Creating/preparing PRs | ✅ Git Flow branch strategy | ✅ Ready |
| chained-pr | Large PRs (400+ lines) | ✅ Split oversized changes | ✅ Ready |
| work-unit-commits | Commit splitting, reviewable units | ✅ Conventional commits | ✅ Ready |
| issue-creation | GitHub issues, bug reports | ✅ Issue-first workflow | ✅ Ready |
| comment-writer | PR feedback, collaboration | ✅ Warm, direct reviews | ✅ Ready |
| judgment-day | Dual review, adversarial review | ✅ Quality gates for critical changes | ✅ Ready |

### Documentation & Design Skills

| Skill | Trigger | Applies To | Status |
|-------|---------|-----------|--------|
| cognitive-doc-design | READMEs, guides, architecture docs | ✅ Reduce cognitive load | ✅ Ready |
| web-design-guidelines | Review UI for Web Guidelines compliance | ✅ Filament + Frontend | ✅ Ready |
| frontend-design | Build web components, pages, UI | ✅ Filament resources + public pages | ✅ Ready |

### Meta Skills

| Skill | Trigger | Purpose | Status |
|-------|---------|---------|--------|
| skill-creator | Create new skills | Document AI patterns | ✅ Ready |
| skill-improver | Audit / improve skills | Refactor existing skills | ✅ Ready |
| skill-registry | Update skill registry | Index available skills | ✅ Ready (this file) |

---

## Quick Reference: Which Skill to Load

### By Activity Type

**Writing Code:**
- PHP Domain/Infrastructure logic → No specific skill (use standard Laravel/PHP practices)
- Filament resources → `frontend-design` (for UI quality)
- Blade templates (homepage, public pages) → `frontend-design` + `tailwind-4`
- Vite config / frontend bundling → `tailwind-4` (CSS) + standard Node practices

**Testing:**
- Unit tests for Domain → No specific skill (Pest syntax is standard)
- Feature tests with database → No specific skill (Pest is standard)
- E2E with Playwright → `playwright` (deferred phase)

**Git & PRs:**
- Branching strategy → `branch-pr` (Git Flow)
- Large PR (400+ lines) → `chained-pr` (split into stacks)
- Commit messages → `work-unit-commits` (conventional commits)
- PR feedback → `comment-writer` (warm, direct reviews)

**Design & Architecture:**
- System design decision → `sdd-design` (before implementation)
- API design → `sdd-design` (via OpenSpec)
- Database schema → `supabase-postgres-best-practices` (PostgreSQL 16)

**SDD Workflow:**
- Exploring a new feature → `sdd-explore` (clarify requirements)
- Proposing a change → `sdd-propose` (scope, intent, tradeoffs)
- Specifying details → `sdd-spec` (requirements, test cases)
- Implementing tasks → `sdd-apply` (code implementation)
- Verifying completion → `sdd-verify` (tests, proof)

**Troubleshooting:**
- Memory leak / memory profile → `chrome-devtools_take_heapsnapshot` (if frontend issue)
- Performance trace → `chrome-devtools_performance_start_trace` (if frontend issue)
- Codebase search → Use Engram memory: `mem_search("topic")` or file tools: `glob()`, `grep()`

---

## Project Conventions (Implicit in Skills)

### Architecture
- **Pattern**: Clean Architecture (Domain-driven)
- **Principle**: Domain knows nothing about Filament, Eloquent, or HTTP
- **Layers**: Domain → Infrastructure → Http/Filament

### Testing
- **Mode**: Strict TDD (level 8 PHPStan, 70%+ coverage on Domain)
- **Runners**: Pest/PHPUnit
- **Tools**: Laravel Pint (code style), PHPStan (static analysis)

### Git & Commits
- **Strategy**: Git Flow (main, develop, feature/*, bugfix/*)
- **Commits**: Conventional Commits (feat:, fix:, refactor:, test:, docs:, chore:)
- **PRs**: Issue-first, chained PRs for 400+ lines

### Frontend & Styling
- **CSS Framework**: Tailwind CSS 4
- **Templates**: Blade (Filament) + Blade (public pages)
- **Bundler**: Vite 6 (Node 24)

### Documentation
- **Pattern**: Cognitive load reduction
- **Tools**: Markdown (PRD, DOCKER.md, openspec/)
- **Approach**: Concepts before code, architecture analogies

---

## Engagement Pattern

### For New Features or Bug Fixes

1. **Explore** (if unclear): `sdd-explore` → clarify requirements
2. **Propose**: `sdd-propose` → scope, intent, tradeoffs
3. **Design**: `sdd-design` → architecture decisions
4. **Spec**: `sdd-spec` → detailed requirements + test scenarios
5. **Break into Tasks**: `sdd-tasks` → reviewable work units
6. **Implement**: `sdd-apply` → code implementation (use domain skills as needed)
7. **Verify**: `sdd-verify` → run tests, prove correctness
8. **Archive**: `sdd-archive` → sync final delta spec

### For Code Reviews

1. Use `comment-writer` for warm, direct feedback
2. For 400+ line PRs, suggest split via `chained-pr`
3. For critical changes, propose `judgment-day` (dual blind review)

### For Documentation

1. Use `cognitive-doc-design` to reduce cognitive load
2. Ensure web UI follows Web Interface Guidelines → `web-design-guidelines`

---

## Technology Stack (Skill Eligibility Summary)

| Tech | Skill(s) | Status |
|------|---------|--------|
| Laravel 13 | laravel-specialist, sdd-* | ✅ Full support |
| Filament 5 | frontend-design, web-design-guidelines | ✅ Full support |
| PHP 8.4 | laravel-specialist | ✅ Full support |
| Pest/PHPUnit | (no specific skill; use standard Pest docs) | ✅ Standard |
| PostgreSQL 16 | supabase-postgres-best-practices | ✅ Full support |
| Tailwind CSS 4 | tailwind-4 | ✅ Full support |
| Vite 6 | (no specific skill; use standard Vite docs) | ✅ Standard |
| Node 24 | typescript (optional), react-19 (N/A) | ⏳ Optional |
| Docker Compose | (no specific skill; use Docker docs) | ✅ Standard |
| Git / GitHub | branch-pr, chained-pr, work-unit-commits, issue-creation, comment-writer | ✅ Full support |

---

## Known Limitations & Future Skills

- **Laravel-specific best practices** → `laravel-specialist` skill loaded ✅
- **No Filament-specific architecture skill** (use Filament docs + frontend-design)
- **PostgreSQL-specific optimization** → `supabase-postgres-best-practices` fully applicable
- **E2E testing deferred** (Playwright skill available when phase 2 starts)
- **CI/CD not yet configured** (no GitHub Actions / GitLab CI skill installed)

---

**Registry Version**: 1.1  
**Last Updated**: 2025-05-29  
**Maintainer**: SDD-init (automated) + laravel-specialist  
**Next Review**: After first feature implementation
