# 📋 Libro de Reglas de Negocio, Alcance y Hoja de Ruta - baifa-api

Este documento es la **fuente única de verdad** de todos los requerimientos, reglas funcionales, hitos y acuerdos comerciales establecidos con el cliente para el desarrollo del **Sistema Web de Tracking Logístico de Generadores Eléctricos**.

---

## 🧭 Hoja de Ruta Comercial Aceptada (5 Etapas Progresivas)

El proyecto se desarrolla y entrega bajo una **Metodología de Entregas Progresivas y Funcionales** dividida en 5 etapas consecutivas (Total acordado: **$1,500.00 USD** | Tiempo estimado: **12 a 16 semanas**):

| Etapa | Módulo / Entregable Principal | Alcance Funcional | Inversión Acordada | Anticipo (60%) | Cierre (40%) | Estado |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: |
| **1** | **Accesos y Directorio** | Control de Accesos, Roles de Usuario y Directorio Fiscal de Clientes. | $245.00 USD | $147.00 | $98.00 | 🟡 **[En Progreso]** (Auth listo, Clientes pendiente) |
| **2** | **Catálogo de Inventario** | Catálogo de Inventario, Ficha de Generadores, seriales únicos y cargador de fotos. | $285.00 USD | $171.00 | $114.00 | 🔴 **[Planificado]** |
| **3** | **Puntos de Control** | Puntos de Control (*checkpoints*), Órdenes de Envío y actualización manual de ruta. | $400.00 USD | $240.00 | $160.00 | 🔴 **[Planificado]** |
| **4** | **Bitácora Histórica** | Bitácora Histórica inmutable con observers/triggers y registro de trazabilidad. | $245.00 USD | $147.00 | $98.00 | 🔴 **[Planificado]** |
| **5** | **Dashboard y Reportes** | Portal de Autoconsulta para Clientes y Tablero de Reportes Generales de Supervisión. | $325.00 USD | $195.00 | $130.00 | 🔴 **[Planificado]** |
| **TOTAL** | **Sistema Integral Operativo** | **Plataforma completa (Backend API + Base de Datos + Frontend)** | **$1,500.00 USD** | **$900.00** | **$600.00** | **10% Avance** |

---

## 📜 Protocolo de Aceptación y Reglas Comerciales
* **Criterio de Avance:** No se inicia una etapa nueva si la anterior no está probada y validada al 100% por el cliente.
* **Flujo Financiero:** Cada etapa contempla un 60% al inicio y un 40% contra entrega demostrada y aprobada.
* **Alcance Exclusivo:** El servicio contempla diseño lógico, desarrollo de código fuente (API Laravel, Base de datos y Frontend Vue/Nuxt). No incluye infraestructura de hosting/servidores ni dominios.

---

## 📦 Detalle de Reglas de Negocio por Módulo

### 🔹 ETAPA 1: Control de Accesos y Directorio de Clientes

#### Módulo 1.1: Autenticación y Control de Roles (RBAC) 🟢 [Completado]
* **`RN-AUTH-01` [Jerarquía de Roles]:**
  * `client` (Cliente): Visualización exclusiva de sus propios generadores y tracking.
  * `employee` (Empleado): Operadores logísticos (actualización manual de checkpoints).
  * `manager` (Jefe / Gerente): Supervisión, métricas y gestión de su equipo.
  * `admin` (Administrador): Control global de accesos y configuración técnica.
* **`RN-AUTH-02` [Registro por Defecto]:** Todo usuario público nuevo recibe rol `client`.
* **`RN-AUTH-03` [Sesiones y Tokens]:** Bearer Tokens con Laravel Sanctum revocables en `/logout`.
* **`RN-AUTH-04` [Formato de Respuestas]:** Retornar siempre `role` y `role_label` en español.

#### Módulo 1.2: Ficha Fiscal y Directorio de Clientes 🔴 [Planificado - SIGUIENTE PASO]
* **`RN-CLI-01` [CRUD Completo]:** Registro, consulta paginada, edición y eliminación lógica de clientes institucionales.
* **`RN-CLI-02` [Campos Obligatorios de la Ficha Fiscal]:**
  * Razón Social (nombre legal según registro fiscal).
  * Nombre Corto / Comercial de la empresa.
  * Número de RIF (único en el sistema con validación de formato).
  * Teléfono de oficinas.
  * Persona de Contacto: Nombre completo, Email de contacto, Teléfono directo.
* **`RN-CLI-03` [Vinculación de Usuarios]:** Capacidad de asociar una ficha de cliente a cuentas de usuario con rol `client`.
* **`RN-CLI-04` [Permisos]:** Solo accesible para personal interno (`employee`, `manager`, `admin`).

---

### 🔹 ETAPA 2: Catálogo de Inventario y Ficha de Generadores 🔴 [Planificado]

* **`RN-GEN-01` [Registro Inicial de Salida]:** Al despachar o registrar un generador se debe capturar:
  * Número de Serial de fábrica (único y obligatorio).
  * Cliente asignado (vinculado a la Ficha Fiscal de la Etapa 1).
  * Fecha estimada de llegada (ETA).
  * Fotografía referencial del activo (cargador de imágenes con almacenamiento en storage).
  * Capacidad y modelo del generador.
* **`RN-GEN-02` [Estados del Generador]:**
  * `warehouse` (En almacén)
  * `in_transit` (En tránsito)
  * `checkpoint` (En punto de control)
  * `delivered` (Entregado en locación)
  * `installed` (Instalado y operativo)

---

### 🔹 ETAPA 3: Puntos de Control y Órdenes de Envío 🔴 [Planificado]

* **`RN-TRK-01` [Gestión Estrictamente Manual]:** Los cambios de estatus y avance de ruta **NO son automáticos ni por GPS**. Son editados manualmente por los operadores a medida que el equipo avanza.
* **`RN-TRK-02` [Puntos de Control Personalizables]:** Los operadores pueden registrar la llegada a puntos de control específicos (nombre de checkpoint, fecha, observaciones).

---

### 🔹 ETAPA 4: Bitácora Histórica y Registro de Trazabilidad 🔴 [Planificado]

* **`RN-BIT-01` [Bitácora Inmutable]:** Cada cambio de estatus o punto de control dispara un registro inmutable en base de datos con:
  * ID del generador.
  * Punto de control y estatus asignado.
  * Fecha y hora exacta.
  * Usuario responsable que realizó la modificación.
  * Observaciones de la parada/movimiento.

---

### 🔹 ETAPA 5: Portal de Clientes y Tablero de Reportes 🔴 [Planificado]

* **`RN-PRT-01` [Aislamiento Estricto para Clientes]:** El cliente solo puede consultar y rastrear generadores asociados a su empresa.
* **`RN-PRT-02` [Buscador y Línea de Tiempo]:** Buscador de generador por serial y vista interactiva del historial cronológico completo de puntos de control.
* **`RN-REP-01` [Reportes de Supervisión]:** Reporte de generadores en tránsito (filtrable por estatus y cliente), directorio de clientes y estado global de generadores (exclusivo para `manager` y `admin`).
---

## 🎨 Prototipo de Interfaz y Referencia Visual (Figma)
* **URL del Prototipo Interactivo:** [https://layer-ethics-21492417.figma.site](https://layer-ethics-21492417.figma.site)
* **Especificación detallada de vistas, modales y campos:** [docs/business/diseno_figma.md](business/diseno_figma.md)
* **Estructura de Vistas:** Panel de Control (Dashboard), Catálogo de Generadores, Directorio de Clientes, Rastrear Generador (Timeline), Usuarios del Sistema y Reportes.