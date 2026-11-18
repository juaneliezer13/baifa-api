# 🤖 Directrices Técnicas, de Arquitectura y Negocio para Agentes de IA — baifa-api

Este repositorio contiene la API RESTful **baifa-api** para el sistema logístico de generadores eléctricos (**BaiFa Power Tracking**). Cualquier modelo de lenguaje o agente autónomo (Antigravity, Claude, Cursor, LLMs) que trabaje en este código DEBE adherirse estrictamente a estas directrices.

---

## 🏭 0. Contexto de Negocio del Proyecto (BaiFa Power Tracking)

* **Propósito:** Plataforma web de rastreo logístico y gestión técnica de generadores eléctricos industriales (BaiFa Power).
* **Puertos Locales Estándar:**
  - **Backend API (`baifa-api`):** Puerto **`14001`** (`http://localhost:14001/api`)
  - **Base de Datos MySQL (`baifa_api_db`):** Puerto **`14002`** (`127.0.0.1:14002` -> `3306` interno)
  - **Frontend Web (`baifa-ui`):** Puerto **`15001`** (`http://localhost:15001`)
* **Roles de Usuario (RBAC):**
  - **`admin` (Superadministrador):** Control total, auditoría, gestión de usuarios internos y directorio fiscal de clientes.
  - **`manager` (Jefe / Gerente):** Supervisión operativa, métricas globales, seguimiento de inventario y aprobaciones.
  - **`employee` (Operador):** Registro de inspecciones y puntos de control de generadores en campo (sin gestión de usuarios).
  - **`client` (Cliente Empresarial):** Portal exclusivo de autogestión para seguimiento de sus equipos asignados.
* **Hoja de Ruta (5 Etapas — `release/v1.0.7`):**
  - **Etapa 1 (Accesos y Gestión de Identidades):** ✅ **Completada (100%)** — Auth Sanctum, Roles, Registro atómico con RIF y Razón Social, Módulo de Usuarios y Directorio de Clientes.
  - **Etapa 2 (Catálogo e Inventario de Generadores):** ✅ **Completada (100%)** — Fichas técnicas (kVA, motor diésel), seriales únicos e imágenes.
  - **Etapa 3 (Tracking Logístico y Checkpoints):** ✅ **Completada (100%)** — Pipeline manual de estados y consulta pública estilo MRW.
  - **Etapa 4 (Bitácora Histórica y Notificaciones):** ✅ **Completada (100%)** — Historial inmutable y correos transaccionales automáticos.
  - **Etapa 5 (Dashboard de Clientes y Reportes):** ✅ **Completada (100%)** — Portal personalizado de clientes y reportes operativos.
* **Documentación Centralizada del Proyecto:**
  - Repositorio global de documentación: [`../baifa-docs/README.md`](../baifa-docs/README.md)
  - Normas técnicas: [`../baifa-docs/NORMAS_TECNICAS.md`](../baifa-docs/NORMAS_TECNICAS.md)
  - Reglas de negocio del backend: [`../baifa-docs/business/business_rules_backend.md`](../baifa-docs/business/business_rules_backend.md)
  - Especificación de diseño Figma: [`../baifa-docs/business/diseno_figma.md`](../baifa-docs/business/diseno_figma.md)

---

## 🚫 1. Reglas Operativas Estrictas (Ejecución Manual)

1. **NO ejecutar tests automáticamente:**
   * ❌ **PROHIBIDO** ejecutar `php artisan test` tras cada modificación.
   * ✅ **SOLO** ejecutar pruebas cuando el usuario lo ordene expresamente (ej. *"corre los tests"*).
2. **NO ejecutar Laravel Pint automáticamente:**
   * ❌ **PROHIBIDO** ejecutar `./vendor/bin/pint` automáticamente.
   * ✅ **SOLO** ejecutarlo cuando el usuario lo solicite expresamente (ej. *"pasa pint"*, *"formatea el código"*).
3. **Regla de Oro de Docker:**
   * ❌ **NUNCA** ejecutar comandos de PHP, Composer o MySQL en el host.
   * ✅ **SIEMPRE** dentro del contenedor: `docker compose exec app <comando>`.

---

## 🧼 2. Estándar de Código Limpio y Arquitectura Equilibrada

Buscamos un desarrollo **limpio, legible y escalable**, evitando tanto controladores sobrecargados (*fat controllers*) como el exceso de abstracción innecesaria (*over-engineering*):

```text
Solicitud HTTP ──> [Form Request (Validación)] ──> [Controlador Limpio] ──> [Modelo / BD] ──> [API Resource (JSON)]
                                                          │ (Si falla)
                                                          ▼
                                            [Log Categorizado en Docker]
```

### Reglas de los Controladores:
* **Controladores Delgados:** El controlador únicamente orquesta la petición:
  1. Recibe el **Form Request** ya validado.
  2. Ejecuta la operación contra el Modelo Eloquent (o transacción si abarca varias tablas).
  3. Registra logs categorizados si ocurre una excepción.
  4. Retorna un **API Resource** con el código de estado HTTP correspondiente.
* **Sin sobre-abstracción:** No crear capas Repository, Interfaces o DTOs artificiales para operaciones CRUD directas. Usar Eloquent de forma idiomática y limpia.

---

## 🔒 3. Seguridad y Validación de Endpoints

1. **Endpoints Privados:**
   * Todo endpoint operativo del negocio debe estar protegido obligatoriamente con `auth:sanctum`.
   * Si el recurso está reservado a roles específicos, proteger con el middleware `role:admin,manager,employee`.
2. **Endpoints Públicos:**
   * Únicamente rutas que no requieren sesión (`/login`, `/register`, `/forgot-password`, `/reset-password`, `/health`, `/tracking/{serial_number}`).
3. **Validación Obligatoria con Form Requests:**
   * **PROHIBIDO** validar con `$request->validate()` dentro del controlador.
   * Toda petición con carga de datos (`POST`, `PUT`, `PATCH`) debe tener su clase dedicada en `app/Http/Requests/<Modulo>/`.
   * Los mensajes de error de validación deben estar en **español**.

---

## 📤 4. Transformación de Respuestas (API Resources)

* **PROHIBIDO** retornar modelos Eloquent directamente en crudo (ej. `return User::all();`).
* **SIEMPRE** transformar las respuestas mediante clases **API Resource** en `app/Http/Resources/`:
  * Para un registro: `return new ClientResource($client);`
  * Para colecciones: `return ClientResource::collection($clients);`

---

## 📝 5. Sistema de Logs Categorizado en Español (Monitoreo en Docker)

### Formato de Registro:
```php
Log::error("[MODULO_TIPO] Mensaje explicativo en español", [
    'user_id' => $request->user()?->id,
    'contexto' => $datosRelevantes,
    'error' => $e->getMessage(),
]);
```

### Prefijos de Categoría por Módulo:
* **Autenticación:** `[AUTH_INFO]`, `[AUTH_ERROR]`, `[AUTH_WARN]`
* **Clientes:** `[CLIENTES_INFO]`, `[CLIENTES_ERROR]`
* **Generadores:** `[GENERADORES_INFO]`, `[GENERADORES_ERROR]`
* **Tracking / Puntos de Control:** `[TRACKING_INFO]`, `[TRACKING_ERROR]`
* **Reportes:** `[REPORTES_INFO]`, `[REPORTES_ERROR]`

---

## 📖 6. Documentación Activa y Versionado Semántico

1. **OpenAPI (`docs/openapi.yaml` y `../baifa-docs/api-spec/openapi.yaml`):**
   * Cada endpoint debe documentarse en español y mantenerse sincronizado en ambos archivos.
2. **Estándar de Nomenclatura para Releases (Docker vs GitHub):**
   * **Docker Hub:** `release-X.Y.Z` (ej. `juaneliezer13/baifa-api:release-1.0.7`).
   * **GitHub (Ramas y Tags):** `release/vX.Y.Z` (ej. `release/v1.0.7`). Prohibido usar tags sin el prefijo `release/`.
