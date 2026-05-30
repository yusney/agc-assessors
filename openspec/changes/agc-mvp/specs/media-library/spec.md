# Media Library Specification

## Purpose
Defines the Spatie Media Library integration, WebP transforms, and responsive image configurations.

## Requirements

### Requirement: Centralized Media Management
The system MUST manage all entity file attachments (images, documents) using Spatie Media Library in the Infrastructure layer.

#### Scenario: Uploading an office image
- GIVEN an admin uploads an image for an Office in Filament
- WHEN the record is saved
- THEN the image MUST be stored via Spatie Media Library
- AND associated with the Office Eloquent model.

### Requirement: WebP and Responsive Images
The system MUST automatically generate WebP variants and responsive `srcset` for public images.

#### Scenario: Rendering a post thumbnail
- GIVEN a Post has a thumbnail image attached
- WHEN the public Blade view renders the image
- THEN it MUST output an `<img>` tag with `srcset` containing multiple resolutions
- AND the source files MUST be in WebP format.