# Claude Guidelines - baifa-api (BaiFa Power Tracking Backend)

## Domain Context
- System: Logistics tracking and management for diesel generators (BaiFa Power).
- Roles: `admin` (superadmin), `manager` (operations), `employee` (operator), `client` (business client portal).
- Documentation: `../baifa-docs/` | Business rules: `../baifa-docs/business/business_rules_backend.md`.

## Strict Operational Rules
1. NEVER run tests (`php artisan test`) automatically. Run only when explicitly requested.
2. NEVER run linter (`./vendor/bin/pint`) automatically. Run only when explicitly requested.
3. All commands run inside Docker: `docker compose exec app <command>`.
4. Semantic Versioning & Release (Norma Técnica):
   - Escalar en versionado semántico (SemVer: MAJOR.MINOR.PATCH) en cada entrega o hito completado.
   - Docker releases/tags: estándar `release-X.Y.Z` (ej. `juaneliezer13/baifa-api:release-1.1.1` o `release-1.0.3`).
   - GitHub branches y tags: estándar `release/vX.Y.Z` (ej. rama `release/v1.1.1` y tag `release/v1.1.1`, o `release/v1.0.3`).
   - Sincronizar versión en `docs/openapi.yaml`.

## Clean Code & Architecture Rules
- Keep controllers thin and clean: no fat controllers, no excessive abstraction layers.
- Flow: Form Request (validation) -> Controller -> Model -> API Resource (JSON output).
- Private endpoints must be protected with `auth:sanctum` and `role:...`. Public endpoints (`/login`, `/register`, `/health`) are not.
- Categorized logs in Spanish for Docker: `[MODULO_TIPO] Mensaje` (e.g. `[CLIENTES_ERROR]`, `[CLIENTES_INFO]`).
- Maintain `docs/openapi.yaml` in Spanish for every endpoint.
- Business rules go into `../baifa-docs/business/business_rules_backend.md`.