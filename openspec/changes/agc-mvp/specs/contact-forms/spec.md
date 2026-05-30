# Contact Forms Specification

## Purpose
Defines the validation, processing, and notifications for general contact, subscription, lead capture, and service forms.

## Requirements

### Requirement: FormRequest Validation
The system MUST validate all form submissions using Laravel `FormRequest` classes in the Http layer.

#### Scenario: Invalid form submission
- GIVEN a user submits a contact form without an email address
- WHEN the request reaches the controller
- THEN the `FormRequest` MUST intercept it and return validation errors
- AND the Action MUST NOT be executed.

### Requirement: Email Notifications via Mailpit/SMTP
The system MUST queue email notifications for successful form submissions.

#### Scenario: Successful lead capture
- GIVEN a valid lead capture form is submitted
- WHEN the action processes the request
- THEN a Mailable MUST be dispatched to the queue
- AND an email MUST be delivered to the configured administrative address.