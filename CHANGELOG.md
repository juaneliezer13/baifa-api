# Historial de Cambios (Changelog)

Todos los cambios notables en el proyecto **baifa-api** serán documentados en este archivo.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [Unreleased]

### Por Añadir
- Autenticación y emisión de tokens de API.
- Modelos y controladores de recursos de negocio.

---

## [1.0.0] - 2026-09-09

### Añadido
- Inicialización del proyecto Laravel (última versión estable) configurado como API RESTful.
- Arquitectura completa basada en Docker Compose:
  - Servicio `app` con PHP 8.4-FPM, extensiones compiladas (`pdo_mysql`, `gd`, `zip`, etc.) y Composer.
  - Servicio `webserver` con Nginx Alpine configurado para FastCGI y puerto 8000.
  - Servicio `db` con MySQL 8.0, health checks y volumen persistente.
- Soporte para rutas API en `routes/api.php` y configuración JSON en `bootstrap/app.php`.
- Endpoint de diagnóstico de salud `/api/v1/health` con comprobación en vivo de la conexión a MySQL.
- Especificación OpenAPI 3.1 en `docs/openapi.yaml`.
- Documentación de arquitectura en `docs/architecture.md` y registros de decisiones (ADR 0001 y ADR 0002).
- Directrices para modelos de lenguaje y agentes autónomos (`AGENTS.md`, `CLAUDE.md`, `.cursorrules`).
- Plantillas de GitHub para Pull Requests e Issues (`bug_report.md`, `feature_request.md`).
- Repositorio Git inicializado con convención Conventional Commits.