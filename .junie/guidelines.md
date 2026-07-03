# Project Guidelines

## Security & CSRF Compliance
- **MANDATORY**: All state-changing requests (`POST`, `PUT`, `DELETE`, `PATCH`) must include CSRF protection.
- **SQL Injection Prevention**: Always use CodeIgniter's Query Builder for all database interactions. Avoid raw queries and string interpolation. Use allow-lists for dynamic field names.
- **HTML Forms**:
  - Always include `<?= csrf_field() ?>` inside every non-GET form.
  - Prefer POST over GET unless defined by the user.
- **AJAX (fetch)**: 
    - Include the CSRF token in the headers using `<?= csrf_header() ?>` and `<?= csrf_hash() ?>`.
    - Example: `'<?= csrf_header() ?>': '<?= csrf_hash() ?>'`.
    - Rely on the global fetch interceptor in `app/Views/layout/main.php` as a baseline, but always add explicit headers to critical interactive flows for maximum reliability.
- **Verification**: When adding or modifying a form or AJAX call, always verify that the action is allowed and doesn't trigger a CSRF error.

## General
- Follow the project structure and coding standards defined in `ai_docs/`.
- Ensure all new features are consistent with the existing UI (Bootstrap 5).
- Always check for existing helpers or models before creating new ones.
- There is a database table named 'movie_id'. This should NEVER be renamed in the code.
- Never edit any files located in `_install`
- Do not touch protected code igniter files and folders such as `system`.
- Any setting defined in .env that is not related to the database or production settings should allow override from the .env file, but allow to be set via the database in configuration.
- Do not remove values from .env or .env.example if defined in the database.
- Any option defined in settings should provide a tooltip with the .env override variable name.
- If any new API endpoints are added for Media search, make sure to add support for searching within the badges.
- Use existing CodeIgniter helpers and models wherever possible.