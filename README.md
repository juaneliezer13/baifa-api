# baifa-api 🚀

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Nginx](https://img.shields.io/badge/Nginx-Alpine-009639?style=for-the-badge&logo=nginx&logoColor=white)](https://nginx.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**baifa-api** es una API RESTful desarrollada con el framework Laravel y desplegada mediante una arquitectura modular en contenedores Docker (Nginx + PHP 8.4-FPM + MySQL 8.0). Está concebida siguiendo los estándares modernos de desarrollo de software, documentación OpenAPI y soporte optimizado para desarrollo asistido por Inteligencia Artificial (Antigravity, Claude, LLMs).

---

## 🏛️ Arquitectura del Sistema

El entorno de desarrollo está 100% contenerizado para garantizar paridad entre entornos:

```
                  +-------------------------------------------------+
                  |                   HOST / CLIENT                 |
                  |     (Navegador, Postman, Frontend, curl)        |
                  +-------------------------------------------------+
                                           |
                                      Puerto 8000
                                           v
+-----------------------------------------------------------------------------------+
| Red Docker: baifa_network                                                         |
|                                                                                   |
|  +--------------------+       FastCGI (9000)      +----------------------------+  |
|  |  baifa_api_web     | ------------------------> |       baifa_api_app        |  |
|  |  (Nginx Alpine)    |                           |       (PHP 8.4-FPM)        |  |
|  +--------------------+                           |   + Composer + Exts        |  |
|                                                   +----------------------------+  |
|                                                                  |                |
|                                                          MySQL (3306)             |
|                                                                  v                |
|                                                   +----------------------------+  |
|                                                   |        baifa_api_db        |  |
|                                                   |        (MySQL 8.0)         |  |
|                                                   |   Volumen: baifa_api_dbdata|  |
|                                                   +----------------------------+  |
+-----------------------------------------------------------------------------------+
```

Para más detalles, consulta [docs/architecture.md](docs/architecture.md).

---

## 📋 Requisitos Previos

* **Docker Engine** (v24.0 o superior) y **Docker Compose** (v2.x).
* En Windows: **WSL 2** con distribución Ubuntu recomendada.
* **Git** (v2.x).

> [!NOTE]
> No necesitas tener PHP, Composer ni MySQL instalados en tu máquina anfitriona; todo se ejecuta dentro de los contenedores Docker.

---

## ⚡ Inicio Rápido

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/baifa-api.git
cd baifa-api
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
```

### 3. Levantar los contenedores Docker
```bash
docker compose up -d
```
> La primera vez construirá la imagen personalizada de PHP 8.4 con las extensiones (`pdo_mysql`, `gd`, `zip`, etc.) y descargará las imágenes de Nginx y MySQL.

### 4. Generar App Key y Ejecutar Migraciones
```bash
# Generar clave de aplicación (si no está generada en el .env)
docker compose exec app php artisan key:generate

# Ejecutar migraciones en MySQL
docker compose exec app php artisan migrate
```

### 5. Probar el Endpoint de Salud
```bash
curl http://localhost:8000/api/v1/health
```
**Respuesta esperada:**
```json
{
  "status": "ok",
  "app": "baifa-api",
  "database": "connected",
  "timestamp": "2026-09-09T11:44:03+00:00"
}
```

---

## 📖 Documentación de la API

La especificación completa de la API se encuentra documentada bajo el estándar **OpenAPI 3.1**:

* **Especificación OpenAPI:** [`docs/openapi.yaml`](docs/openapi.yaml)
* **Endpoints Principales:**
  * `GET /api/v1/health`: Estado de los servicios y conectividad con la base de datos MySQL.
  * `GET /up`: Health check interno de Laravel.

Puedes visualizar y probar la especificación OpenAPI importando el archivo `docs/openapi.yaml` en **Postman**, **Swagger Editor** o la extensión **OpenAPI (Swagger) Editor** de VS Code.

---

## 🛠️ Comandos de Desarrollo Frecuentes

Todos los comandos se ejecutan a través de Docker Compose:

```bash
# Ver estado de los contenedores
docker compose ps

# Ver logs de todos los servicios en tiempo real
docker compose logs -f

# Crear un nuevo controlador de API
docker compose exec app php artisan make:controller Api/V1/NombreController --api

# Crear una migración y su modelo
docker compose exec app php artisan make:model Nombre -m

# Ejecutar migraciones
docker compose exec app php artisan migrate

# Revertir última migración
docker compose exec app php artisan migrate:rollback

# Ejecutar pruebas unitarias y de integración
docker compose exec app php artisan test

# Dar formato al código con Laravel Pint
docker compose exec app ./vendor/bin/pint

# Instalar una nueva dependencia de Composer
docker compose exec app composer require nombre/paquete
```

---

## 🤖 Directrices para Modelos de Lenguaje y Agentes de IA

Este repositorio incluye instrucciones específicas para asistentes de código y modelos de lenguaje (como Google Antigravity, Claude, Cursor y GitHub Copilot):

* [`AGENTS.md`](AGENTS.md): Reglas de entorno, comandos permitidos, arquitectura y cómo documentar cambios automáticamente.
* [`CLAUDE.md`](CLAUDE.md): Directrices para interacciones con Claude.
* [`.cursorrules`](.cursorrules): Reglas de estilo y contexto para Cursor / IDEs modernos.
* Decisiones de Arquitectura (ADR): Consultar en [`docs/adr/`](docs/adr/).

---

## 🤝 Contribuir

Agradecemos todas las contribuciones al proyecto. Por favor consulta [`CONTRIBUTING.md`](CONTRIBUTING.md) para conocer nuestro flujo de trabajo con Git, la convención de commits (Conventional Commits) y los estándares de código.

Consulta el historial de cambios en [`CHANGELOG.md`](CHANGELOG.md).

---

## 📄 Licencia

Este proyecto está licenciado bajo los términos de la [Licencia MIT](LICENSE).