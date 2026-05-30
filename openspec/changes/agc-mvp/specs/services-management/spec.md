# Services Management Specification

## Purpose
Defines the services entity, translatable specializations, and per-department forms.

## Requirements

### Requirement: Seeded Services
The system MUST seed 6 initial services corresponding to the business departments.

#### Scenario: Seeding services
- GIVEN an empty database
- WHEN the database is seeded
- THEN 6 service records MUST be created.

### Requirement: Per-Department Forms
The system MUST link each service to a specific contact form or department email for lead capture.

#### Scenario: Submitting a service inquiry
- GIVEN a user is on the "Tax Advisory" service page
- WHEN they submit the inquiry form
- THEN the email notification MUST be routed to the specific department associated with that service.