# 📋 Libro de Reglas de Negocio y Módulos - baifa-api

Este documento es la **fuente única de verdad** de todos los requerimientos y reglas funcionales solicitadas por el cliente para el mini-SaaS. Cada vez que el cliente defina una condición o comportamiento, debe registrarse en este archivo antes de comenzar su programación.

---

## 🚦 Estados de Implementación
* 🔴 **[Planificado]**: Regla o módulo definido por el cliente, aún no programado.
* 🟡 **[En Progreso]**: Actualmente en desarrollo.
* 🟢 **[Completado]**: Desarrollado, probado y en producción/develop.

---

## 📦 Módulo 1: Autenticación y Control de Accesos (RBAC)
* **Estado:** 🟢 [Completado]
* **Descripción:** Gestión de acceso al SaaS, control de sesiones mediante Bearer Tokens y diferenciación por tipo de usuario.

### Reglas de Negocio:
* **`RN-AUTH-01` [Jerarquía de Roles]:** El sistema cuenta con 4 roles bien diferenciados:
  * `client` (Cliente): Usuario final consumidor de los servicios del SaaS.
  * `employee` (Empleado): Personal operativo de la empresa/organización.
  * `manager` (Jefe / Gerente): Supervisión de operaciones, reportes y gestión de su equipo.
  * `admin` (Administrador): Control total de la plataforma y configuración técnica.
* **`RN-AUTH-02` [Registro por Defecto]:** Todo usuario que se registre a través del endpoint público de registro recibe automáticamente el rol `client` a menos que un administrador le asigne otro rol.
* **`RN-AUTH-03` [Sesiones y Tokens]:** El sistema emite Bearer Tokens vía Laravel Sanctum. Al cerrar sesión (`/logout`), el token actual debe destruirse en base de datos inmediatamente.
* **`RN-AUTH-04` [Formato de Respuestas]:** Toda respuesta debe incluir el identificador del rol (`role`) y su nombre formal en español (`role_label`).

---

## 📦 Módulos Futuros (Borrador / Por Definir)

> A medida que el cliente comunique nuevos requerimientos (por ejemplo: suscripciones, clientes, inventario, facturación, órdenes, etc.), se crearán nuevas secciones aquí con sus reglas `RN-[MODULO]-[NUMERO]`.

---

## 💡 Instrucciones para Agentes de IA
* Antes de desarrollar cualquier funcionalidad, revisa las reglas `RN-` del módulo correspondiente.
* Si el usuario te indica un nuevo requerimiento del cliente durante la conversación, **anótalo inmediatamente aquí** bajo el módulo correspondiente.