# 📋 Libro de Reglas de Negocio y Módulos - baifa-api

Este documento es la **fuente única de verdad** de todos los requerimientos y reglas funcionales solicitadas por el cliente para la plataforma de gestión y tracking de generadores eléctricos. Cada regla posee un código identificador único y un estado de avance.

---

## 🚦 Estados de Implementación
* 🔴 **[Planificado]**: Requerimiento definido por el cliente, pendiente de desarrollo.
* 🟡 **[En Progreso]**: En desarrollo activo en la rama correspondiente.
* 🟢 **[Completado]**: Desarrollado, documentado en OpenAPI y probado.

---

## 📦 Módulo 1: Autenticación y Control de Roles (RBAC)
* **Estado:** 🟢 [Completado]
* **Descripción:** Control de acceso general a la API mediante Laravel Sanctum con Bearer Tokens.

### Reglas de Negocio:
* **`RN-AUTH-01` [Jerarquía de Roles]:** El sistema cuenta con 4 roles:
  * `client`: Cliente final (acceso exclusivo a sus generadores y tracking).
  * `employee`: Personal operativo (actualización manual de checkpoints y operaciones).
  * `manager`: Jefe o Gerente (supervisión, reportes y gestión administrativa).
  * `admin`: Administrador global (control de usuarios internos, configuración del sistema).
* **`RN-AUTH-02` [Registro por Defecto]:** Todo usuario nuevo recibe el rol `client` salvo asignación administrativa.
* **`RN-AUTH-03` [Sesiones y Tokens]:** Los Bearer Tokens se emiten en `/login` y se destruyen inmediatamente en base de datos al hacer `/logout`.
* **`RN-AUTH-04` [Serialización de Rol]:** Las respuestas JSON deben incluir `role` (código) y `role_label` (nombre en español).

---

## 📦 Módulo 2: Gestión de Clientes (Directorio Fiscal)
* **Estado:** 🔴 [Planificado]
* **Descripción:** Directorio empresarial y fiscal de los clientes atendidos por la empresa.

### Reglas de Negocio:
* **`RN-CLI-01` [Operaciones Permitidas]:** Registro, consulta (con paginación y filtros), edición y eliminación lógica de clientes.
* **`RN-CLI-02` [Ficha de Datos Obligatoria]:** Cada cliente debe contar con:
  * Razón Social (nombre legal según registro fiscal).
  * Nombre Corto / Comercial de la empresa.
  * Número de RIF (único en el sistema).
  * Teléfono de oficinas.
  * Persona de Contacto: Nombre completo, Email de contacto, Teléfono de contacto.
* **`RN-CLI-03` [Vinculación de Usuarios]:** Un registro de cliente fiscal puede asociarse a uno o más usuarios con rol `client` para que puedan autenticarse y consultar sus generadores.
* **`RN-CLI-04` [Permisos de Gestión]:** Solo usuarios con rol `employee`, `manager` o `admin` pueden gestionar el directorio de clientes. Los usuarios `client` no tienen acceso al directorio fiscal.

---

## 📦 Módulo 3: Registro y Gestión de Generadores
* **Estado:** 🔴 [Planificado]
* **Descripción:** Inventario y control de salida de generadores eléctricos hacia locaciones del cliente.

### Reglas de Negocio:
* **`RN-GEN-01` [Salida de Warehouse]:** Al registrar la salida inicial de un generador desde el almacén, es obligatorio capturar:
  * Número de Serial del generador (único y obligatorio).
  * Cliente asignado (vinculado al Directorio Fiscal).
  * Fecha estimada de llegada / entrega (ETA).
  * Fotografía referencial del equipo (almacenamiento de imagen con validación de formato jpg/png).
  * Notas o especificaciones iniciales del equipo (opcional).
* **`RN-GEN-02` [Estatus Inicial]:** Todo generador registrado inicia en estatus: `warehouse` (En almacén) o `in_transit` (En tránsito hacia locación).
* **`RN-GEN-03` [Estatus Disponibles del Generador]:**
  * `warehouse` (En almacén)
  * `in_transit` (En tránsito)
  * `checkpoint` (En punto de control intermedio)
  * `delivered` (Entregado en locación)
  * `installed` (Instalado y operativo)
* **`RN-GEN-04` [Integridad de Datos]:** No se puede registrar un generador sin asociarlo a un cliente existente ni sin un número de serial válido.

---

## 📦 Módulo 4: Puntos de Control y Tracking Manual (Logística)
* **Estado:** 🔴 [Planificado]
* **Descripción:** Trazabilidad operativa manual de los generadores a lo largo de su ruta de entrega.

### Reglas de Negocio:
* **`RN-TRK-01` [Edición Estrictamente Manual]:** Los cambios de estatus y avance por puntos de control **NO deben ser automáticos ni por GPS/sensores**. Son registrados manualmente por el personal administrativo/operativo a medida que el equipo avanza.
* **`RN-TRK-02` [Historial de Trazabilidad]:** Cada cambio de punto de control debe persistir en un historial inmutable con:
  * Generador ID.
  * Punto de control / Checkpoint (nombre o descripción de la ubicación/fase).
  * Estatus asignado (`in_transit`, `checkpoint`, `delivered`, `installed`).
  * Fecha y hora exacta del registro.
  * Usuario responsable que realizó el cambio manual.
  * Observaciones o notas adicionales del traslado.
* **`RN-TRK-03` [Actualización del Estado Principal]:** Al registrar un nuevo checkpoint en el historial, el estatus principal y el checkpoint actual del generador deben actualizarse en la tabla principal para consultas rápidas.

---

## 📦 Módulo 5: Panel y Buscador de Tracking para Clientes
* **Estado:** 🔴 [Planificado]
* **Descripción:** Portal privado de autoconsulta para que los clientes finales sigan su generador en tiempo real.

### Reglas de Negocio:
* **`RN-PRT-01` [Aislamiento de Clientes (Multi-Tenant básico)]:** Un usuario con rol `client` **ÚNICAMENTE** puede visualizar los generadores que pertenecen a su empresa/ficha de cliente. Bajo ninguna circunstancia puede ver equipos de otros clientes.
* **`RN-PRT-02` [Buscador de Generadores]:** El cliente puede buscar sus generadores por número de serial, fecha o estatus para saber exactamente en qué punto de control se encuentra su equipo.
* **`RN-PRT-03` [Reporte de Línea de Tiempo (Timeline)]:** La API debe proveer un endpoint que devuelva el historial completo ordenado cronológicamente con cada punto de control superado, fechas, observaciones y estatus actual.

---

## 📦 Módulo 6: Administración de Usuarios y Permisos Internos
* **Estado:** 🔴 [Planificado]
* **Descripción:** Gestión del personal de la empresa con control de accesos.

### Reglas de Negocio:
* **`RN-USR-01` [CRUD de Usuarios Internos]:** Los administradores (`admin`) pueden listar, crear, editar, asignar roles y desactivar usuarios del equipo interno (`employee`, `manager`, `admin`).
* **`RN-USR-02` [Protección de Auto-Eliminación]:** Un administrador no puede eliminarse ni desactivarse a sí mismo.

---

## 📦 Módulo 7: Reportes y Analítica de Supervisión
* **Estado:** 🔴 [Planificado]
* **Descripción:** Reportes consolidados para la toma de decisiones gerenciales.

### Reglas de Negocio:
* **`RN-REP-01` [Reporte de Generadores en Tránsito]:** Listado consolidado con filtros por estatus actual (`warehouse`, `in_transit`, `checkpoint`, `delivered`, `installed`), rango de fechas estimadas de llegada y cliente asignado.
* **`RN-REP-02` [Reporte Global de Clientes y Equipos Activos]:** Vista gerencial que consolida el total de clientes registrados, total de generadores despachados, instalados y en ruta.
* **`RN-REP-03` [Acceso Restringido a Reportes]:** Solo los roles `manager` y `admin` pueden consultar los endpoints de reportes consolidados.