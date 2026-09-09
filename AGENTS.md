# Directrices para Agentes de IA y Modelos de Lenguaje (Antigravity, Claude, LLMs)

Este repositorio contiene la API RESTful **baifa-api**. Cualquier modelo de lenguaje o agente autónomo (Google Antigravity, Claude, Cursor, ChatGPT, etc.) que trabaje en este código DEBE adherirse a las siguientes directrices operativas y de arquitectura.

---

## ⚠️ Regla de Oro del Entorno (Docker Obligatorio)

* **NUNCA ejecutes `php`, `composer` ni `mysql` directamente en el shell del host.** El host puede no tener estas herramientas instaladas.
* **TODOS los comandos de Laravel, Composer y base de datos deben ejecutarse a través de Docker Compose:**
  ```bash
  # Artisan
  docker compose exec app php artisan <comando>

  # Composer
  docker compose exec app composer <comando>

  # Testing
  docker compose exec app php artisan test

  # Formateo (Pint)
  docker compose exec app ./vendor/bin/pint
  ```

---

## 🏗️ Pila Tecnológica y Contenedores

| Servicio | Nombre Contenedor | Tecnologías |
| :--- | :--- | :--- |
| `app` | `baifa_api_app` | PHP 8.4-FPM, Composer, extensiones: `pdo_mysql`, `gd`, `zip`, `bcmath`, etc. |
| `webserver` | `baifa_api_webserver` | Nginx Alpine (escucha en `http://localhost:8000`, pasa FastCGI a `app:9000`) |
| `db` | `baifa_api_db` | MySQL 8.0 (puerto `3306`, BD: `baifa_api`, usuario: `baifa_user`) |

---

## 📐 Convenciones de Código y Arquitectura

1. **Rutas y Versionado:**
   * Todas las rutas de API se registran en `routes/api.php` con prefijo `/v1/` (generando `/api/v1/...`).
   * No uses vistas Blade ni rutas HTML para funcionalidades de la API.
2. **Controladores y Respuestas:**
   * Ubica los controladores en `app/Http/Controllers/Api/V1/`.
   * Usa siempre **API Resources** (`php artisan make:resource`) para transformar respuestas de modelos Eloquent a JSON. No devuelvas modelos Eloquent directamente.
   * Valida entradas mediante **Form Requests** dedicados (`php artisan make:request`) en lugar de validar en el controlador.
3. **Manejo de Errores:**
   * Las excepciones deben devolver respuestas JSON coherentes con código de estado HTTP adecuado (400, 401, 403, 404, 422, 500).
4. **Pruebas Automatizadas:**
   * Cada nuevo endpoint debe incluir al menos una prueba de integración (Feature Test) en `tests/Feature/`.
   * Ejecuta siempre `docker compose exec app php artisan test` antes de considerar una tarea completada.

---

## 📝 Reglas de Documentación Continua

Cada vez que realices modificaciones en la API:
1. **Especificación OpenAPI:** Actualiza inmediatamente [`docs/openapi.yaml`](docs/openapi.yaml) con los nuevos endpoints, esquemas de solicitud/respuesta y códigos de error.
2. **Changelog:** Registra los cambios en la sección `[Unreleased]` de [`CHANGELOG.md`](CHANGELOG.md).
3. **Decisiones Significativas:** Si introduces un nuevo paquete de terceros, cambias el motor de base de datos o alteras el flujo de autenticación, redacta un nuevo ADR en [`docs/adr/`](docs/adr/).
4. **Commits:** Usa la convención de [Conventional Commits](CONTRIBUTING.md) (`feat:`, `fix:`, `docs:`, `test:`, `refactor:`).