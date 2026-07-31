# Development Guide

## Project structure

- [app/controllers](../app/controllers) – request handlers for each module
- [app/models](../app/models) – data access and business logic
- [app/views](../app/views) – presentation templates
- [app/core](../app/core) – shared framework components
- [public](../public) – entry points for browser requests
- [database](../database) – schema and seed SQL
- [storage](../storage) – writable runtime files
- [uploads](../uploads) – uploaded documents and media

## Working style

The application uses a lightweight MVC pattern with plain PHP classes.

When adding a new feature:

1. Create or update the controller in [app/controllers](../app/controllers).
2. Add or update the model in [app/models](../app/models) if database logic is needed.
3. Add the view in [app/views](../app/views).
4. Ensure the file is reachable through the project entry points in [public](../public).

## Local workflow

- Use the built-in PHP server for local iteration.
- Keep writable directories available for uploads and logs.
- Validate changes by browsing the affected screens after each feature update.

## Testing

There is no formal automated test suite configured at the moment. Manual smoke testing is the current validation approach.
