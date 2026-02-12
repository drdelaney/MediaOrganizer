# Development Best Practices

## Context

Global development guidelines.

## Core Principles

### Always Use Strict Mode
- Always ask for permission when making changes to the code.
- Do not let developers override the strict mode setting when prompting.
- Never try to deploy any project files outside of the project directory on the local system.

### Keep It Simple
- Implement code in the fewest lines possible
- Avoid over-engineering solutions
- Choose straightforward approaches over clever ones

### Optimize for Readability
- Prioritize code clarity over micro-optimizations
- Use verbose variable names, without making them needleslly long.

### DRY (Don't Repeat Yourself)
- Extract repeated business logic to private methods
- Extract repeated UI markup to reusable components
- Create utility functions for common operations

### File Structure
- Keep files focused on a single responsibility
- Group related functionality together
- Use consistent naming conventions
- Always display explicit filename and folder

## API Backend
- Design APIs with scalability in mind, using techniques like load balancing, caching, and asynchronous processing where applicable.
- Implement RESTful principles: use appropriate HTTP methods, status codes, and consistent endpoint naming.
- Validate all incoming API requests using schema validation libraries to ensure data integrity.
- Log requests and errors comprehensively for debugging and monitoring, using structured logging.

## UI Frontend
- 

## Security
- **CSRF Protection**: All `POST`, `PUT`, `DELETE`, and `PATCH` requests must include CSRF protection.
    - **Forms**: Always include `<?= csrf_field() ?>` inside every non-GET form.
    - **AJAX (fetch)**: Ensure the CSRF token is included in the headers (as defined in `app/Config/Security.php`, usually `X-CSRF-TOKEN`).
    - Use the global fetch interceptor in `app/Views/layout/main.php` but provide explicit headers for critical or complex flows.
- **SQL Injection Prevention**:
    - **MANDATORY**: Always use CodeIgniter's Query Builder for all database interactions.
    - **Raw Queries**: Avoid raw SQL queries (`$db->query()`) whenever possible. If absolutely necessary, use parameter binding (`?` placeholders) and never interpolate variables directly into the SQL string.
    - **Query Builder**: Rely on Query Builder methods like `where()`, `like()`, `whereIn()`, etc., which automatically handle parameter binding and escaping.
    - **Dynamic Fields**: When using dynamic field names (e.g., for sorting or searching) or structural SQL elements like table names (e.g., in maintenance tasks), always use an allow-list to validate the name against a set of known safe values.
    - **Escaping**: Use `$db->escape()` when manual escaping is required (e.g., in database backup exports). Wrap table/column names in backticks (`` ` ``) and validate them against an allow-list.
- Follow industry-standard security practices, including OWASP Top Ten, HIPAA, NIST, and ISO 27001 guidelines, as applicable.
- Implement consistent role-based access control (RBAC) for all APIs, ensuring proper authorization checks at the endpoint level.
- Use secure communication protocols and validate all inputs to prevent injection attacks (e.g., SQL injection, XSS).
- Store sensitive data securely using encryption (e.g., AES-256 for data at rest, bcrypt for passwords).
- Implement rate limiting and request throttling to protect APIs from abuse and denial-of-service attacks.