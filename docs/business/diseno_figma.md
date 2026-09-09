# 🎨 Especificación del Prototipo UI/UX (Figma Make) - BaiFa Logistics

* **URL del Prototipo Interactivo:** [https://layer-ethics-21492417.figma.site](https://layer-ethics-21492417.figma.site)
* **Nombre de la Aplicación en Diseño:** *Baifa - Logistics Platform*
* **Frontend Tecnológico Planeado:** Vue.js / Nuxt + Tailwind CSS

---

## 🧭 Estructura de Navegación y Vistas del Prototipo

El prototipo cuenta con una barra de navegación lateral (*Sidebar*) con las siguientes secciones:

1. **📊 Panel de Control (Dashboard Principal):**
   * **Tarjetas de Métricas:**
     * `Generadores activos` (total en proceso).
     * `En Warehouse` (en almacén principal).
     * `En Tránsito` (despachados en ruta).
     * `Entregados/Instalados` (finalizados).
     * `Clientes activos` (empresas activas).
   * **Pipeline Visual:** Vista gráfica de generadores agrupados por fase logística.
   * **Tabla de Generadores Recientes:** Acceso rápido a las últimas unidades en movimiento.

2. **⚡ Generadores (Gestión de Equipos y Salidas):**
   * Listado tabular con filtros por estado (`en_warehouse`, `en_transito`, `en_punto_control`, `entregado`, `instalado`).
   * **Modal / Formulario de Registro de Salida:**
     * `Serial del generador *` (ej. `GEN-2026-0041`).
     * `Cliente asignado *` (selector del directorio fiscal).
     * `Fecha estimada de llegada *` (ETA).
     * `URL / Archivo de foto referencial` del generador.
     * `Notas de ingreso al warehouse`.
   * **Modal de Actualización Manual de Estado (Checkpoint):**
     * `Nuevo estado *` (selector).
     * `Nombre del punto de control *` (ej. *Punto de Control Valencia*).
     * `Notas del cambio de estado *` (transportista, placa, observaciones).

3. **🏢 Clientes (Directorio Fiscal de Empresas):**
   * Métricas superiores: `Total clientes`, `Activos`, `Inactivos`.
   * Listado con buscador por RIF, Razón Social o Nombre Corto.
   * **Campos de la Ficha de Cliente:**
     * `companyFiscalName`: Razón Social (ej. *Industrias Venezolanas Toro S.A.*).
     * `companyShortName`: Nombre Corto (ej. *IndVen Toro*).
     * `rif`: RIF venezolano (ej. *J-12345678-9*).
     * `officePhone`: Teléfono de oficinas (ej. *0212-555-0100*).
     * **Bloque Persona de Contacto:**
       * `contactName`: Nombre del contacto (ej. *Carlos Mendoza*).
       * `email`: Email de contacto (ej. *cmendoza@indventoro.com.ve*).
       * `contactPhone`: Teléfono directo (ej. *0414-555-0101*).
     * `active`: Switch booleano (Activo / Inactivo).

4. **📍 Rastrear Generador (Portal / Vista de Tracking):**
   * Buscador centrado por número de serial.
   * Ficha del generador con foto, serial, cliente asignado y ETA.
   * **Línea de Tiempo (Timeline de Checkpoints):**
     * Historial cronológico con cada cambio de estatus.
     * Nombre del punto de control, fecha/hora, observaciones y usuario responsable (`changedBy`).

5. **👥 Usuarios del Sistema:**
   * Métricas: `Total usuarios`, `Activos`, `Inactivos`, `Con acceso admin`.
   * Listado de personal y modal para asignar roles:
     * `superadmin` / `admin` (Administrador)
     * `manager` (Jefe / Gerente)
     * `operator` / `employee` (Operador)
     * `viewer` / `client` (Visualizador / Cliente)

6. **📈 Reportes (Analítica y Consultas Generales):**
   * Tablas filtrables con exportación y consultas de generadores en tránsito, entregados y directorio consolidado.

---

## 🎨 Paleta de Estatus y Colores en UI (Tailwind)
* `en_warehouse`: Violeta (`text-violet-400`, `bg-violet-400/10`)
* `en_transito`: Azul cielo (`text-sky-400`, `bg-sky-400/10`)
* `en_punto_control`: Amarillo (`text-yellow-400`, `bg-yellow-400/10`)
* `entregado`: Verde (`text-green-400`, `bg-green-400/10`)
* `instalado`: Esmeralda (`text-emerald-300`, `bg-emerald-300/10`)