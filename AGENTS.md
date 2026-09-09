# 🤖 Directrices Técnicas, de Arquitectura y Negocio para Agentes de IA

Este repositorio contiene la API RESTful **baifa-api** para el sistema logístico de generadores eléctricos (BaiFa). Cualquier modelo de lenguaje o agente autónomo (Antigravity, Claude, Cursor, LLMs) que trabaje en este código DEBE adherirse estrictamente a estas directrices.

---

## 🚫 1. Reglas Operativas Estrictas (Ejecución Manual)

1. **NO ejecutar tests automáticamente:**
   * ❌ **PROHIBIDO** ejecutar `php artisan test` tras cada modificación.
   * ✅ **SOLO** ejecutar pruebas cuando el usuario (Juan) lo ordene expresamente (ej. *"corre los tests"*).
2. **NO ejecutar Laravel Pint automáticamente:**
   * ❌ **PROHIBIDO** ejecutar `./vendor/bin/pint` automáticamente.
   * ✅ **SOLO** ejecutarlo cuando el usuario lo solicite expresamente (ej. *"pasa pint"*, *"formatea el código"*).
3. **Regla de Oro de Docker:**
   * ❌ **NUNCA** ejecutar comandos de PHP, Composer o MySQL en el host.
   * ✅ **SIEMPRE** dentro del contenedor: `docker compose exec app <comando>`.

---

## 🧼 2. Estándar de Código Limpio y Arquitectura Equilibrada

Buscamos un desarrollo **básico, limpio, legible y escalable**, evitando tanto controladores sobrecargados (*fat controllers*) como el exceso de abstracción innecesaria (sobre-ingeniería / *over-engineering*):

```
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
   * Únicamente rutas que no requieren sesión (`/login`, `/register`, `/health`).
3. **Validación Obligatoria con Form Requests:**
   * **PROHIBIDO** validar con `$request->validate()` dentro del controlador.
   * Toda petición con carga de datos (`POST`, `PUT`, `PATCH`) debe tener su clase dedicada en `app/Http/Requests/<Modulo>/`.
   * Los mensajes de error de validación deben estar en **español**.

---

## 📤 4. Transformación de Respuestas (API Resources)

* **PROHIBIDO** retornar modelos Eloquent directamente en crudo (ej. `return User::all();`).
* **SIEMPRE** transformar las respuestas mediante clases **API Resource** en `app/Http/Resources/<Modulo>/`:
  * Para un registro: `return new ClienteResource($cliente);`
  * Para colecciones paginadas: `return ClienteResource::collection($clientes);`
* Garantiza que los nombres de campos en el JSON sean consistentes y no se expongan campos internos no deseados.

---

## 📝 5. Sistema de Logs Categorizado en Español (Monitoreo en Docker)

Para facilitar la supervisión del sistema en tiempo real a través de los logs de Docker (`docker compose logs -f app`), los errores y eventos clave deben registrarse siguiendo un estándar uniforme:

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

## 📖 6. Documentación Activa y en Español

1. **OpenAPI (`docs/openapi.yaml`):**
   * Cada endpoint debe documentarse en [`docs/openapi.yaml`](docs/openapi.yaml) antes de dar por completada la tarea.
   * **Todo el texto debe estar en español:** títulos, descripciones, nombres de parámetros, ejemplos y respuestas.
2. **Libro de Reglas de Negocio (`docs/business_rules.md`):**
   * Toda nueva condición o flujo indicado por el cliente debe registrarse con su código `RN-[MODULO]-[NUMERO]`.