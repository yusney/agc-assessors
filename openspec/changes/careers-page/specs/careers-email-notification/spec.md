# Delta for careers-email-notification

## ADDED Requirements

### Requirement: JobApplicationMail Mailable Class

The system MUST provide `app/Mail/JobApplicationMail.php` as the first concrete
Mailable in the project. The class MUST be `final`, implement `ShouldQueue`,
and accept a `JobApplication` model in its constructor.

#### Scenario: Mailable instantiates correctly

- GIVEN a `JobApplication` model instance
- WHEN `new JobApplicationMail($application)` is called
- THEN no exception MUST be thrown
- AND the instance MUST implement `Illuminate\Contracts\Queue\ShouldQueue`

---

### Requirement: Email Content

The email MUST include:
- Subject: `"Nueva candidatura: {applicant name} — {department}"` (or translated equivalent)
- All application fields: name, last_name, email, phone, department, message, submission date
- If `cv_path` is not null, MUST attach the CV file from private storage

#### Scenario: Email with CV attachment

- GIVEN a `JobApplication` with `cv_path = 'cv-uploads/abc123.pdf'`
- WHEN `JobApplicationMail` is built
- THEN `$this->attachFromStorage('cv-uploads/abc123.pdf', 'disk' => 'local')` MUST
  be called
- AND the attachment MUST appear in the email

#### Scenario: Email without CV

- GIVEN a `JobApplication` with `cv_path = null`
- WHEN `JobApplicationMail` is built
- THEN no attachment MUST be present in the email
- AND the email MUST still render without errors

---

### Requirement: Recipient Address

The email MUST be sent to the address stored in
`SiteSetting::get('careers_page')['form_destination_email']`. If that setting is
empty or null, MUST fall back to `config('mail.from.address')`.

#### Scenario: Configured destination used

- GIVEN `form_destination_email` is `"rrhh@agcassessors.com"`
- WHEN `JobApplicationMail` is dispatched
- THEN the `to` address MUST be `"rrhh@agcassessors.com"`

#### Scenario: No destination configured

- GIVEN `form_destination_email` is `null`
- WHEN `JobApplicationMail` is dispatched
- THEN the `to` address MUST equal `config('mail.from.address')`

---

### Requirement: Queue Dispatch

The controller MUST dispatch `JobApplicationMail` via `Mail::to(...)->queue(...)`
(or `dispatch`), NOT `send()`, so it runs asynchronously.

#### Scenario: Mail is queued, not sent synchronously

- GIVEN `QUEUE_CONNECTION=redis` in `.env`
- WHEN a valid form is submitted
- THEN the controller MUST return a redirect immediately (not block on SMTP)
- AND the Redis queue MUST contain one pending job

#### Scenario: Dev mail captured by Mailpit

- GIVEN `MAIL_HOST=mailpit` and `QUEUE_CONNECTION=sync` in dev `.env`
- WHEN a valid form is submitted
- THEN the email MUST appear in Mailpit UI at `http://localhost:8025`
- AND the email body MUST contain the applicant's name and department

---

## Validation

```bash
# PHPStan on Mailable
docker compose exec php vendor/bin/phpstan analyse app/Mail/JobApplicationMail.php --memory-limit=1G

# Trigger test email via tinker
docker compose exec php php artisan tinker --execute="
  \$app = \AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication::latest()->first();
  \Illuminate\Support\Facades\Mail::to('test@test.com')->send(new \App\Mail\JobApplicationMail(\$app));
  echo 'sent';
"

# Verify in Mailpit
open http://localhost:8025
```
