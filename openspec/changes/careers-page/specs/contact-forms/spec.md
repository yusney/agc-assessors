# Delta for contact-forms

## MODIFIED Requirements

### Requirement: Email Notifications via Mailpit/SMTP

The system MUST queue email notifications for successful form submissions.
A concrete `Mailable` class MUST exist for each form type that triggers a
notification. `JobApplicationMail` serves as the canonical reference implementation.
(Previously: requirement existed but no Mailable was ever built — purely aspirational)

#### Scenario: Successful lead capture

- GIVEN a valid lead capture form is submitted
- WHEN the action processes the request
- THEN a Mailable MUST be dispatched to the queue
- AND an email MUST be delivered to the configured administrative address.

#### Scenario: JobApplicationMail as reference implementation

- GIVEN `app/Mail/JobApplicationMail.php` exists and follows ShouldQueue
- WHEN any future Mailable is added to the project
- THEN it MUST follow the same pattern: `final class`, `ShouldQueue`, constructor
  accepts a model, recipient resolved from `SiteSetting` with fallback to
  `config('mail.from.address')`
