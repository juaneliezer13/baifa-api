# baifa-api 🚀

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Nginx](https://img.shields.io/badge/Nginx-Alpine-009639?style=for-the-badge&logo=nginx&logoColor=white)](https://nginx.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**baifa-api** es la API RESTful del sistema **BaiFa Power Tracking**, desarrollada con Laravel 12 y desplegada mediante contenedores Docker (Nginx Alpine + PHP 8.4-FPM + MySQL 8.0), con su especificación documentada bajo el estándar **OpenAPI 3.1**.

---

## 🏛️ Arquitectura y Puertos Locales

El entorno está 100% contenerizado:

```text
HOST (Navegador / Postman / Frontend en Puerto 15001)
           │
      Puerto 14001
           ▼
[ baifa_api_webserver ] (Nginx Alpine - Puerto interno 80)
           │ FastCGI (Puerto 9000)
           ▼
[ baifa_api_app ] (PHP 8.4-FPM + Laravel 12)
           │ MySQL (Puerto interno 3306 / Puerto Host 14002)
           ▼
[ baifa_api_db ] (MySQL 8.0 - Volumen persistente: baifa_api_dbdata)
```

| Servicio | Contenedor | Puerto Interno | Puerto Host |
| :--- | :--- | :---: | :---: |
| **API HTTP (Nginx)** | `baifa_api_webserver` | `80` | **`14001`** |
| **PHP-FPM (Laravel)** | `baifa_api_app` | `9000` | Red interna |
| **Base de Datos MySQL** | `baifa_api_db` | `3306` | **`14002`** |

---

## ⚡ Inicio Rápido

### 1. Configurar entorno
```bash
cp .env.example .env
```

### 2. Levantar los contenedores
```bash
docker compose up -d --build
```

### 3. Instalar dependencias, migrar y sembrar datos iniciales
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

### 4. Probar la API
```bash
curl http://localhost:14001/api/v1/health
```

**Respuesta:**
```json
{
  "status": "ok",
  "app": "baifa-api",
  "database": "connected",
  "timestamp": "..."
}
```

---

## 📖 Documentación Centralizada y OpenAPI

* 📄 **Especificación OpenAPI 3.1:** [`docs/openapi.yaml`](docs/openapi.yaml)
* 📚 **Repositorio de Documentación General:** [`../baifa-docs/README.md`](../baifa-docs/README.md)
* 🏷️ **Historial de Releases:** [`../baifa-docs/releases/README.md`](../baifa-docs/releases/README.md)

---

## 🛠️ Comandos Frecuentes

```bash
# Ver estado de los contenedores
docker compose ps

# Ver logs en vivo
docker compose logs -f

# Ejecutar comandos Artisan
docker compose exec app php artisan <comando>

# Ejecutar migraciones y seeders
docker compose exec app php artisan migrate --seed

# Ejecutar pruebas automatizadas
docker compose exec app php artisan test

# Formatear código con Laravel Pint
docker compose exec app ./vendor/bin/pint
```

---

## 📄 Licencia

Este proyecto está bajo la [Licencia MIT](LICENSE).
