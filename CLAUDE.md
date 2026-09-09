# Claude Guidelines - baifa-api

## Strict Operational Rules
1. DO NOT run tests (`php artisan test`) automatically. Only run tests when explicitly asked by the user.
2. DO NOT run linter (`./vendor/bin/pint`) automatically. Only run Pint when explicitly asked by the user.
3. Always execute commands inside Docker: `docker compose exec app <command>`.
4. Capture any business rules given by the client into `docs/business_rules.md` before or during development.
5. Maintain only `docs/openapi.yaml` and `README.md` for technical documentation.

## Tech Conventions
- Laravel 12 RESTful API (`/api/v1/...`)
- Roles: `client`, `employee`, `manager`, `admin` (`App\Enums\UserRole`)
- Form Requests for validation, API Resources for JSON transformation.