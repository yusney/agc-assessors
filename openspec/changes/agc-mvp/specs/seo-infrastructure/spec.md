# SEO Infrastructure Specification

## Purpose
Defines the `SEOData` Domain Value Object, meta tags, JSON-LD, sitemap XML, and hreflang alternate links logic.

## Requirements

### Requirement: Domain SEOData Value Object
The system MUST use a framework-agnostic `SEOData` Value Object in the Domain layer to encapsulate SEO information.

#### Scenario: Creating SEOData from a Domain Model
- GIVEN a Domain Model (e.g., Post)
- WHEN a mapper converts an Eloquent model to the Domain Model
- THEN a static factory method `SEOData::fromPost($post)` MUST generate the SEO Data without Eloquent dependencies.

### Requirement: Hreflang Alternate Links
The system MUST generate `hreflang` alternate links for all translatable public pages.

#### Scenario: Rendering page headers
- GIVEN a user visits a translatable page at `/ca/services`
- WHEN the page header is rendered
- THEN `<link rel="alternate" hreflang="es" href=".../es/services">` MUST be present
- AND the corresponding `en` link MUST be present.

### Requirement: Sitemap XML Generation
The system MUST provide a dynamic `sitemap.xml` containing all pages, posts, services, and offices.

#### Scenario: Fetching sitemap
- GIVEN the entities exist in the database
- WHEN a client requests `/sitemap.xml`
- THEN the system MUST return a valid XML sitemap including all active URLs across all locales.