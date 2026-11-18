# Claude Guidelines - baifa-api (BaiFa Power Tracking Backend)

## Domain Context
- **System:** Logistics tracking and management for diesel generators (BaiFa Power).
- **Local Ports:** API on `14001`, MySQL DB on `14002`, Frontend UI on `15001`.
- **Roles:** `admin` (superadmin), `manager` (operations), `employee` (operator), `client` (business client portal).
- **Documentation:** `../baifa-docs/` | Business rules: `../baifa-docs/business/business_rules_backend.md`.

## Strict Operational Rules
1. NEVER run tests (`php artisan test`) automatically. Run only when explicitly requested.
2. NEVER run linter (`./vendor/bin/pint`) automatically. Run only when explicitly requested.
3. All commands run inside Docker: `docker compose exec app <command>`.
4. Semantic Versioning & Release (Norma Técnica):
   - Docker releases/tags: `release-X.Y.Z` (e.g. `juaneliezer13/baifa-api:release-1.0.7`).
   - GitHub branches & tags: `release/vX.Y.Z` (e.g. `release/v1.0.7`). Never use bare `vX.Y.Z` tags.
   - Synchronize version in `docs/openapi.yaml` and `../baifa-docs/api-spec/openapi.yaml`.

## Clean Code & Architecture Rules
- Keep controllers thin and clean: Form Request (validation) -> Controller -> Model -> API Resource (JSON output).
- Private endpoints must be protected with `auth:sanctum` and `role:...`.
- Categorized logs in Spanish for Docker: `[MODULO_TIPO] Mensaje` (e.g. `[CLIENTES_ERROR]`, `[GENERADORES_INFO]`).
