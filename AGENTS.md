# Directrices para Agentes de IA y Modelos de Lenguaje

Este repositorio contiene la API RESTful **baifa-api**. Cualquier modelo de lenguaje o asistente (Antigravity, Claude, Cursor, ChatGPT) que trabaje aquí debe seguir estas reglas esenciales:

---

## ⚠️ Regla de Oro (Docker Obligatorio)

* **NUNCA ejecutes `php`, `composer` ni `mysql` directamente en el host.**
* **TODOS los comandos deben ejecutarse dentro de Docker:**
  ```bash
  docker compose exec app php artisan <comando>
  docker compose exec app composer <comando>
  docker compose exec app php artisan test
  docker compose exec app ./vendor/bin/pint
  ```

---

## 🏗️ Pila Tecnológica

* **PHP 8.4-FPM** (`baifa_api_app`): Laravel, Composer y extensiones (`pdo_mysql`, etc.).
* **Nginx** (`baifa_api_webserver`): Puerto 8000 en el host -> FastCGI `app:9000`.
* **MySQL 8.0** (`baifa_api_db`): Puerto 3306, base de datos `baifa_api`.

---

## 📐 Convenciones de Código

1. **Rutas:** Registrar en `routes/api.php` con prefijo `/v1/` (`/api/v1/...`).
2. **Controladores:** Ubicar en `app/Http/Controllers/Api/V1/`.
3. **Validación y Transformación:**
   * Usar **Form Requests** (`app/Http/Requests/...`) para validación.
   * Usar **API Resources** (`app/Http/Resources/...`) para serializar respuestas a JSON.
4. **Respuestas JSON:** Siempre responder en JSON con códigos de estado HTTP correctos.

---

## 📝 Documentación del Proyecto (Únicos Archivos Activos)

Este proyecto mantiene una documentación ágil y centralizada:
1. **[`docs/openapi.yaml`](docs/openapi.yaml):** Cada vez que crees, modifiques o elimines un endpoint, actualiza esta especificación OpenAPI con sus métodos, parámetros y esquemas de respuesta.
2. **[`README.md`](README.md):** Si se añade un cambio importante en la instalación o comandos de uso, actualiza el README.

*(No se requiere mantener changelogs, ADRs ni guías de contribución complejas).*