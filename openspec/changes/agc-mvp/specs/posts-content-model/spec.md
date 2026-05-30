# Posts Content Model Specification

## Purpose
Defines the structure, translatable fields, and CQRS-lite actions for Posts and Categories.

## Requirements

### Requirement: Post Translatable Fields
The system MUST support translatable title, content, and slugs for Posts using JSON in the database.

#### Scenario: Saving a multilingual post
- GIVEN a user submits a Post with `ca` and `es` titles
- WHEN the `CreatePostAction` is executed
- THEN the Post MUST be saved via the Repository with the JSON translatable structure.

### Requirement: Action Pattern for Writes
The system MUST perform all write operations (create, update, delete) for Posts and Categories through dedicated Action classes.

#### Scenario: Updating a post category
- GIVEN a Post and a new Category
- WHEN the `UpdatePostAction` is invoked
- THEN the action MUST enforce business rules and use the Repository interface to persist the update.

### Requirement: Clean Architecture Repositories
The system MUST define PostRepository interfaces in the Domain layer and implement them in the Infrastructure layer.

#### Scenario: Fetching paginated posts
- GIVEN a request for the blog index
- WHEN the controller queries posts
- THEN it MUST depend on the Domain `PostRepositoryInterface`
- AND the Infrastructure Eloquent repository MUST resolve the query without leaking Eloquent to the controller.