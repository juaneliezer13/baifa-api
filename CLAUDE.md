# Claude Guidelines - baifa-api

## Strict Operational Rules
1. NEVER run tests (`php artisan test`) automatically. Run only when explicitly requested.
2. NEVER run linter (`./vendor/bin/pint`) automatically. Run only when explicitly requested.
3. All commands run inside Docker: `docker compose exec app <command>`.

## Clean Code & Architecture Rules
- Keep controllers thin and clean: no fat controllers, no excessive abstraction layers.
- Flow: Form Request (validation) -> Controller -> Model -> API Resource (JSON output).
- Private endpoints must be protected with `auth:sanctum` and `role:...`. Public endpoints (`/login`, `/register`, `/health`) are not.
- Categorized logs in Spanish for Docker: `[MODULO_TIPO] Mensaje` (e.g. `[CLIENTES_ERROR]`, `[CLIENTES_INFO]`).
- Maintain `docs/openapi.yaml` in Spanish for every endpoint.
- Business rules go into `docs/business_rules.md`.