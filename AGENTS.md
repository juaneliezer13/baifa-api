# 🤖 Directrices Operativas y de Negocio para Agentes de IA

Este repositorio contiene la API RESTful **baifa-api** para un mini-SaaS. Cualquier agente de IA (Google Antigravity, Claude, Cursor, LLMs) que trabaje en este proyecto DEBE cumplir estrictamente con las siguientes directrices técnicas, operativas y de negocio.

---

## 🚫 1. Reglas Operativas Estrictas (Lo que NO debes hacer automáticamente)

1. **NO ejecutar tests de forma automática:**
   * ❌ **PROHIBIDO** ejecutar `php artisan test` tras cada modificación de código.
   * ✅ **SOLO** ejecuta las pruebas cuando el usuario (Juan) te lo indique expresamente de forma manual (ej. *"corre los tests"*, *"haz las pruebas"*).
2. **NO ejecutar Laravel Pint de forma automática:**
   * ❌ **PROHIBIDO** ejecutar `./vendor/bin/pint` automáticamente al guardar o editar código.
   * ✅ **SOLO** ejecuta Pint cuando el usuario te lo solicite expresamente (ej. *"pasa pint"*, *"formatea el código"*).
3. **Regla de Oro de Docker:**
   * ❌ **NUNCA** ejecutes `php`, `composer` ni comandos de base de datos directamente en el host.
   * ✅ **SIEMPRE** ejecuta dentro del contenedor Docker: `docker compose exec app <comando>`.

---

## 📋 2. Gestión Obligatoria de Reglas de Negocio

El proyecto cuenta con un documento centralizado para las peticiones del cliente:
📄 **[`docs/business_rules.md`](docs/business_rules.md)**

### Tus responsabilidades respecto al negocio:
1. **Captura inmediata de requerimientos:**
   Cada vez que el usuario mencione una condición, flujo o requerimiento solicitado por el cliente (ej. *"el cliente pide que los empleados no puedan ver reportes de ventas"*), **debes registrarlo de inmediato** en `docs/business_rules.md` asignándole un código (ej. `RN-VENTAS-02`) y su estado (🔴 Planificado).
2. **Consulta previa antes de programar:**
   Antes de codificar un módulo o endpoint, consulta `docs/business_rules.md` para asegurarte de respetar todas las reglas y restricciones del cliente.
3. **Actualización de estado:**
   Cuando termines de implementar una regla de negocio, actualiza su estado a 🟢 [Completado] en `docs/business_rules.md`.

---

## 🏗️ 3. Estándares de Programación de la API

1. **Rutas:** Registrar en `routes/api.php` bajo el prefijo `/api/v1/`.
2. **Controladores:** Ubicar en `app/Http/Controllers/Api/V1/`.
3. **Validación:** Usar siempre **Form Requests** (`app/Http/Requests/...`). No validar directamente en controladores.
4. **Respuestas:** Usar siempre **API Resources** (`app/Http/Resources/...`) para garantizar respuestas JSON predecibles.
5. **Roles de Usuario:** Usar el enum `App\Enums\UserRole` (`client`, `employee`, `manager`, `admin`).
6. **Protección de Rutas:** Usar `auth:sanctum` para autenticación y `role:nombre_rol` para permisos.

---

## 📖 4. Documentación Activa del Proyecto

Solo se mantienen dos archivos de documentación técnica:
1. **[`docs/openapi.yaml`](docs/openapi.yaml):** Actualizar cada vez que se cree, modifique o elimine un endpoint.
2. **[`README.md`](README.md):** Actualizar si hay cambios en la instalación o comandos de ejecución del proyecto.