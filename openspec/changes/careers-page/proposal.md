# Proposal: Trabaja con nosotros — Careers Page

## Intent

AGC Assessors lacks a dedicated careers page. Potential candidates have no structured channel to discover the firm's culture or submit applications. This change introduces a fully content-managed, multi-language careers page with a CV submission form, database persistence, and email notification — following existing admin settings patterns.

## Scope

### In Scope
- Admin settings page: all page copy editable in ca/es/en (hero, benefits, form section, footer CTA)
- Public page at localized URLs (`/treballa-amb-nosaltres`, `/es/trabaja-con-nosotros`, `/en/work-with-us`)
- Application form: name, surname, email, phone, department, message, CV upload, privacy consent
- Form submissions persisted to `job_applications` table
- Email notification dispatched to configured address on each submission (first Mailable in project)
- CV file storage via Laravel's local disk (configurable path)
- Responsive design aligned to existing design system (Outfit/Inter, `#00346f` primary)

### Out of Scope
- Individual job offer management (no CRUD for open positions)
- Applicant tracking workflow (status changes, internal notes)
- CV download or view from admin panel (future iteration)
- Public listing of open roles (static benefits content is sufficient for MVP)

## Capabilities

### New Capabilities
- `careers-page`: Public multi-language careers page with hero, benefits, application form, and footer CTA — content fully managed from Filament admin via `SiteSetting`
- `job-applications`: Form submission persistence, CV file storage, and email notification for candidate applications

### Modified Capabilities
- `contact-forms`: First concrete implementation of the queued Mailable pattern specified but never built; adds `JobApplicationMail` as the reference implementation

## Approach

Follow the `FooterSettingsPage` / `TrustBarSettingsPage` pattern exactly:
- Admin page extends `Filament\Pages\Page`, stores JSON via `SiteSetting::set('careers_page', ...)`
- Benefits use a Filament `Repeater` with fixed 3 items (icon, title×3, description×3 locales)
- Public controller reads settings, renders Blade view — no domain entity layer (consistent with existing newsletter/contact patterns where `Application/` is empty)
- `JobApplication` Eloquent model lives in `AGC\Infrastructure\Persistence\Eloquent\Models\`
- Controller validates via `FormRequest`, saves model, dispatches `JobApplicationMail` to queue
- CV files stored under `storage/app/private/cv-uploads/` (not public)

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/Filament/Pages/WorkWithUsSettingsPage.php` | New | Admin settings page + blade |
| `src/Infrastructure/Persistence/Eloquent/Models/JobApplication.php` | New | Eloquent model for submissions |
| `database/migrations/xxxx_create_job_applications_table.php` | New | Schema for submissions |
| `app/Http/Controllers/Public/WorkWithUsController.php` | New | index + store actions |
| `app/Http/Requests/StoreJobApplicationRequest.php` | New | FormRequest validation |
| `app/Mail/JobApplicationMail.php` | New | First Mailable — queued notification |
| `resources/views/public/pages/work-with-us.blade.php` | New | Public page (hero, benefits, form, CTA) |
| `resources/lang/{ca,es,en}/routes.php` | Modified | Add `careers` route key |
| `resources/lang/{ca,es,en}/messages.php` | Modified | Add form labels and UI strings |
| `routes/web.php` | Modified | Register GET + POST localized routes |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| No Mailable exists — mail infra untested | Med | Verify Mailpit is wired; test with `php artisan tinker` before feature test |
| CV file size / type abuse | Med | Validate `mimes:pdf,doc,docx`, `max:5120` in FormRequest; store outside `public/` |
| PR exceeds 400-line review budget (~530 lines estimated) | High | User approved `size:exception` for single PR |
| `SiteSetting` JSON grows large with 3×3 locale fields | Low | Mirrors existing pattern; no structural change to `site_settings` |

## Rollback Plan

1. Drop migration: `php artisan migrate:rollback` (removes `job_applications` table)
2. Delete `SiteSetting` row: `SiteSetting::where('key', 'careers_page')->delete()`
3. Remove routes (GET/POST) from `routes/web.php`
4. Delete new files — no existing files are destructively modified

## Dependencies

- `SiteSetting` model and `site_settings` table must exist (already present)
- Queue worker must be running for email dispatch (`--profile queue` or sync driver in dev)
- Mailpit container wired to SMTP (already configured per `openspec/config.yaml`)

## Success Criteria

- [ ] Careers page renders at all 3 locale URLs with content loaded from `SiteSetting`
- [ ] Form submission creates a `job_applications` row in the database
- [ ] CV file saved to `storage/app/private/cv-uploads/` (not publicly accessible)
- [ ] Email notification delivered to Mailpit in dev with all submission fields
- [ ] Admin settings page saves and reflects changes on page reload
- [ ] PHPStan level 8 passes on all new files
- [ ] All 3 locale routes resolve correctly (ca no prefix, `/es/`, `/en/`)
