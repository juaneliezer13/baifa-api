# ADR 0002: Arquitectura API-First y Versionado de Rutas

* **Estado:** Aceptado
* **Fecha:** 2026-09-09
* **Autor:** Juan Chirinos

## Contexto
baifa-api funcionará como backend para múltiples clientes (web, móvil u otros servicios). Es imperativo que las respuestas sean predecibles, siempre en formato JSON y preparadas para evolución sin romper compatibilidad.

## Decisión
1. Las rutas de negocio se ubican en `routes/api.php` bajo el prefijo `/api/v1/`.
2. Las excepciones de la API deben serializarse en formato JSON automáticamente (`shouldRenderJsonWhen` en `bootstrap/app.php`).
3. Toda adición de endpoint debe documentarse bajo la especificación OpenAPI en `docs/openapi.yaml`.

## Consecuencias
* Permite versionar la API (`v1`, `v2`) a futuro sin afectar clientes existentes.
* Garantiza que los errores 404, 500 o de validación nunca devuelvan HTML a clientes API.