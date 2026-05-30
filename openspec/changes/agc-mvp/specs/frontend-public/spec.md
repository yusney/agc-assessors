# Frontend Public Specification

## Purpose
Defines the Blade views, Tailwind CSS structure, Vite build, and Alpine.js interactions for the public site.

## Requirements

### Requirement: Alpine.js for Interactivity
The system MUST use Alpine.js for all client-side UI interactivity (dropdowns, modals, reading progress) instead of React or Vue.

#### Scenario: Interacting with reading progress
- GIVEN a user is reading a long Post
- WHEN they scroll down the page
- THEN an Alpine.js component MUST update a progress bar width corresponding to the scroll percentage.

### Requirement: Separated Tailwind Configuration
The system MUST maintain a root `tailwind.config.js` exclusively for the public frontend, targeting a sub-20KB minified and gzipped CSS output.

#### Scenario: Building frontend assets
- GIVEN the developer runs `npm run build`
- WHEN Vite processes the CSS
- THEN it MUST purge unused classes based solely on the `resources/views/**` files
- AND the final CSS bundle MUST NOT include Filament's internal Tailwind classes.