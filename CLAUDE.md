# Claude Code Guidelines - baifa-api

This is **baifa-api**, a modern Laravel RESTful API running inside Docker Compose.

## Build & Run Commands
- Always run commands via Docker: `docker compose exec app <command>`
- Run migrations: `docker compose exec app php artisan migrate`
- Run tests: `docker compose exec app php artisan test`
- Code formatting: `docker compose exec app ./vendor/bin/pint`
- View logs: `docker compose logs -f`

## Code Guidelines
- RESTful API with version prefix: `/api/v1/...`
- Controllers belong to `app/Http/Controllers/Api/V1/`
- Use Form Requests for input validation and API Resources for JSON responses.
- Always keep `docs/openapi.yaml` and `CHANGELOG.md` updated when modifying endpoints.