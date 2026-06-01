# Delta for careers-form-submission

## ADDED Requirements

### Requirement: job_applications Table Migration

The system MUST create a `job_applications` table with columns:
`id`, `name`, `last_name`, `email`, `phone` (nullable), `department`,
`message`, `cv_path` (nullable, string), `privacy_accepted` (boolean),
`ip_address` (string, nullable), `created_at`, `updated_at`.

#### Scenario: Migration runs cleanly

- GIVEN a fresh database
- WHEN `php artisan migrate` is executed
- THEN `job_applications` table MUST exist with the correct schema
- AND rolling back MUST drop the table with no errors

---

### Requirement: JobApplication Eloquent Model

The system MUST provide a `JobApplication` model in
`AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication`.
The `$fillable` array MUST include all form fields. The model MUST cast
`privacy_accepted` to `bool`.

#### Scenario: Model creates record

- GIVEN valid form data array
- WHEN `JobApplication::create($data)` is called
- THEN a row MUST be inserted in `job_applications`
- AND `privacy_accepted` retrieved MUST be boolean `true`

---

### Requirement: StoreJobApplicationRequest Validation

The system MUST validate submissions via `StoreJobApplicationRequest`.
Rules:

| Field | Rule |
|-------|------|
| `name` | required, string, max:100 |
| `last_name` | required, string, max:100 |
| `email` | required, email, max:255 |
| `phone` | nullable, string, max:30 |
| `department` | required, string, max:100 |
| `message` | required, string, max:2000 |
| `privacy_accepted` | required, accepted |
| `cv` | nullable, file, mimes:pdf,doc,docx, max:5120 |

#### Scenario: Missing required fields

- GIVEN a POST request with no `email` field
- WHEN the request reaches `WorkWithUsController@store`
- THEN the response MUST redirect back with `errors` in the session
- AND no `job_applications` row MUST be created

#### Scenario: CV file exceeds 5 MB

- GIVEN a valid form but `cv` file is 6 MB
- WHEN the request is submitted
- THEN validation MUST fail with error on `cv`
- AND the file MUST NOT be stored

#### Scenario: Invalid CV MIME type

- GIVEN `cv` is a `.exe` file
- WHEN the request is submitted
- THEN validation MUST fail with error on `cv`

#### Scenario: Privacy not accepted

- GIVEN `privacy_accepted` is absent or `"0"`
- WHEN the request is submitted
- THEN validation MUST fail with error on `privacy_accepted`

---

### Requirement: CV File Storage

When a CV file is present and valid, the system MUST store it at
`storage/app/private/cv-uploads/{uuid}.{ext}`. The path MUST NOT be
under `public/` or accessible via a public URL.

#### Scenario: CV stored in private disk

- GIVEN a valid PDF upload
- WHEN the form submission succeeds
- THEN `Storage::disk('local')->exists('cv-uploads/{filename}')` MUST return `true`
- AND a GET request to any public URL MUST NOT return the file content

---

### Requirement: Form Submission Persistence

On a valid submission the controller MUST:
1. Store the CV file (if present)
2. Create a `JobApplication` record
3. Dispatch `JobApplicationMail` to the queue
4. Flash success message from `settings['form_success_message']` (active locale)
5. Redirect back to the careers page

#### Scenario: Happy path submission

- GIVEN all required fields are valid, no CV attached
- WHEN a POST request is sent to the store route
- THEN HTTP response MUST be 302 redirect to the careers page
- AND `job_applications` MUST contain one new row
- AND the session MUST contain a `success` flash key

#### Scenario: Happy path with CV

- GIVEN all required fields are valid and a PDF is attached
- WHEN a POST request is sent
- THEN a row is created with non-null `cv_path`
- AND the file exists in private storage

---

### Requirement: Rate Limiting

The store route MUST be protected by a rate limiter of 3 submissions per IP
per 60 minutes.

#### Scenario: Exceeds rate limit

- GIVEN an IP has submitted 3 valid applications in the last hour
- WHEN a 4th submission is attempted from the same IP
- THEN the response MUST be 429 (Too Many Requests)
- AND no new row MUST be created

---

## Validation

```bash
# Run feature tests
docker compose exec php php artisan test tests/Feature/WorkWithUsControllerTest.php

# Inspect submitted row
docker compose exec php php artisan tinker --execute="dump(\AGC\Infrastructure\Persistence\Eloquent\Models\JobApplication::latest()->first());"

# Verify private storage
docker compose exec php php artisan tinker --execute="dump(\Illuminate\Support\Facades\Storage::disk('local')->files('cv-uploads'));"
```
