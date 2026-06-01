# Tasks: Trabaja con nosotros — Careers Page

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~530 |
| 400-line budget risk | High |
| Chained PRs recommended | No |
| Suggested split | Single PR (size:exception approved) |
| Delivery strategy | exception-ok |
| Chain strategy | size-exception |
| Decision needed before apply | No |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: size-exception
400-line budget risk: High

## Phase 1: Foundation (Migration, Model, Routes, Translations)

- [x] 1.1 Create `database/migrations/xxxx_create_job_applications_table.php` with columns: `id`, `name`, `last_name`, `email`, `phone` (nullable), `department`, `message`, `cv_path` (nullable, string), `privacy_accepted` (boolean), `ip_address` (nullable, string), `created_at`, `updated_at`. Run `php artisan migrate` and verify table exists.
- [x] 1.2 Create `src/Infrastructure/Persistence/Eloquent/Models/JobApplication.php` — `final class` extending `Model`, `$fillable` with all form fields, `$casts` for `privacy_accepted => bool`. Verify via tinker: `JobApplication::create($data)`.
- [x] 1.3 Add `careers` route key to `resources/lang/{ca,es,en}/routes.php` (`ca: 'treballa-amb-nosaltres'`, `es: 'trabaja-con-nosotros'`, `en: 'work-with-us'`).
- [x] 1.4 Add all form labels and UI strings to `resources/lang/{ca,es,en}/messages.php` under `careers` key: page_title, hero_cta, benefits_title, form_intro, form_labels (name, last_name, email, phone, department, message, cv, privacy, submit), form_success, footer_cta. Verify via tinker: `__('messages.careers.page_title')`.
- [x] 1.5 Register GET + POST routes in `routes/web.php` inside the locale group using `LaravelLocalization::transRoute('routes.careers')` and `WorkWithUsController`. Verify with `php artisan route:list | grep treballa`.

## Phase 2: Admin Settings Page (Filament)

- [x] 2.1 Create `src/Filament/Pages/WorkWithUsSettingsPage.php` — `final class` extending `Page`, navigation group `Configuración`, statePath `data`. Hero section: `hero_title` (translatable via Tabs ca/es/en), `hero_subtitle`, `hero_cta_text`, `hero_cta_url` (TextInput::url), `hero_image` (CuratorPicker). Benefits: fixed Repeater `minItems(3)->maxItems(3)` with icon, title (translatable), description (translatable). Form section: `form_intro` (translatable), `form_privacy_text` (translatable), `form_success_message` (translatable), `form_destination_email` (email validation). Footer CTA: `footer_cta_title`, `footer_cta_button_text` (both translatable). `mount()` fills from `SiteSetting::get('careers_page', [])`. `save()` calls `SiteSetting::set('careers_page', ...)` + success notification.
- [x] 2.2 Create `resources/views/filament/pages/work-with-us-settings.blade.php` — `<x-filament-panels::page>` wrapper, `wire:submit="save"`, render `$this->form`, submit button. Follows footer-settings pattern exactly.
- [ ] 2.3 Verify settings page: navigate to admin panel, confirm "Trabaja con nosotros" appears under "Configuración". Fill and save all sections, reload, confirm values persist. Test email validation on `form_destination_email`. Verify no 4th benefit can be added.

## Phase 3: Public Page + Form (Blade + Controller)

- [x] 3.1 Create `app/Http/Requests/StoreJobApplicationRequest.php` — `final class` extending `FormRequest`. Rules: `name`/`last_name` (required, string, max:100), `email` (required, email, max:255), `phone` (nullable, string, max:30), `department` (required, string, max:100), `message` (required, string, max:2000), `privacy_accepted` (required, accepted), `cv` (nullable, file, mimes:pdf,doc,docx, max:5120).
- [x] 3.2 Create `app/Http/Controllers/Public/WorkWithUsController.php` — `final class`. `index()` loads `SiteSetting::get('careers_page', [])`, resolves locale-aware content, returns `view('public.pages.work-with-us')`. `store(StoreJobApplicationRequest)` stores CV to `storage/app/private/cv-uploads/{uuid}.{ext}` if present, creates `JobApplication` record, dispatches `JobApplicationMail` to queue, flashes success message from settings, redirects back. Add `throttle:3,60` rate limit middleware.
- [x] 3.3 Create `resources/views/public/pages/work-with-us.blade.php` — extend `layouts.public`. Hero: responsive section with `<img>` if hero_image set, title, subtitle, CTA button pointing to hero_cta_url. Benefits grid: 3 cards with icon, title, description, responsive (1 col mobile, 2 tablet, 3 desktop) via Tailwind grid. Include `public.components.job-application-form`. Footer CTA: title + scroll-to-form button. SEO yields: title, description.
- [x] 3.4 Create `resources/views/public/components/job-application-form.blade.php` — `<form method="POST">` with CSRF, fields: name, last_name, email, phone, department (select: fiscal/laboral/comptable/altres), message (textarea), CV (file input), privacy checkbox. Display validation errors inline. Intro text from `settings['form_intro']`, privacy text from `settings['form_privacy_text']` (Blade-rendered).
- [x] 3.5 Smoke test: curl all 3 locale URLs (200) — PASSED.

## Phase 4: Email Notification (Mailable)

- [x] 4.1 Create `app/Mail/JobApplicationMail.php` — `final class` implementing `ShouldQueue`, constructor accepts `JobApplication`. `content()` returns view `mail.job-application`. `attachments()` attaches CV from private storage if `cv_path` not null. `envelope()` sets subject "Nueva candidatura: {name} — {department}". Resolve `to` address from `SiteSetting::get('careers_page')['form_destination_email']` with fallback to `config('mail.from.address')`.
- [x] 4.2 Create `resources/views/mail/job-application.blade.php` — email body with all application fields: name, last_name, email, phone, department, message, submission date. Clean minimal HTML.
- [ ] 4.3 Verify email: submit a form, check Mailpit at `http://localhost:8025` for the notification. Confirm body contains applicant name + department. Verify CV attachment present when uploaded.

## Phase 5: QA + PHPStan

- [ ] 5.1 Run PHPStan level 8 on all new files: PHPStan not installed in project. PHP syntax check passed for all 5 files.
- [ ] 5.2 Manual end-to-end: admin saves content → public page reflects it in all 3 locales → form submits → DB has row + CV on disk → email in Mailpit → success flash visible.
