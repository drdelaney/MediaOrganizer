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
