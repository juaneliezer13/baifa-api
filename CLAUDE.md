# Claude Code Guidelines - baifa-api

Laravel RESTful API in Docker Compose.

## Commands
- Run via Docker: `docker compose exec app <command>`
- Migrations: `docker compose exec app php artisan migrate`
- Tests: `docker compose exec app php artisan test`
- Code formatting: `docker compose exec app ./vendor/bin/pint`

## Guidelines
- API prefix: `/api/v1/...`
- Controllers in `app/Http/Controllers/Api/V1/`
- Use Form Requests and API Resources.
- Maintain only `docs/openapi.yaml` and `README.md` for project documentation.