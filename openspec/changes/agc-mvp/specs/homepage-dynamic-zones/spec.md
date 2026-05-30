# Homepage Dynamic Zones Specification

## Purpose
Defines the `content_sections` relational table, polymorphic types, and ordered display for the dynamic homepage.

## Requirements

### Requirement: Relational Storage for Zones
The system MUST store homepage zones in a dedicated `content_sections` table with `page_id`, `type`, `data` (JSON), and `order` columns.

#### Scenario: Rendering homepage sections
- GIVEN the homepage has multiple sections configured
- WHEN the homepage is loaded
- THEN the system MUST query the `content_sections` table ordered by the `order` column
- AND render the specific blade component for each `type` (e.g., Hero, Carousel).

### Requirement: Strict Zone Types
The system MUST support exactly 6 zone types: Hero, Carousel, Features Grid, Stats Bar, Testimonial, and CTA.

#### Scenario: Adding an unsupported zone type
- GIVEN a developer attempts to add a section with type "InvalidType"
- WHEN the section is validated
- THEN the system MUST reject it (via Enum validation).