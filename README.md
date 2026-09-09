# baifa-api 🚀

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Nginx](https://img.shields.io/badge/Nginx-Alpine-009639?style=for-the-badge&logo=nginx&logoColor=white)](https://nginx.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**baifa-api** es una API RESTful desarrollada con Laravel y desplegada mediante contenedores Docker (Nginx + PHP 8.4-FPM + MySQL 8.0). Diseñada de forma ágil y práctica, manteniendo su especificación documentada bajo el estándar **OpenAPI 3.1**.

---

## 🏛️ Arquitectura

El entorno está 100% contenerizado:

```
HOST (Navegador / Postman / Frontend)
           │
      Puerto 8000
           ▼
[ baifa_api_webserver ] (Nginx Alpine)
           │ FastCGI (Puerto 9000)
           ▼
[ baifa_api_app ] (PHP 8.4-FPM + Laravel)
           │ MySQL (Puerto 3306)
           ▼
[ baifa_api_db ] (MySQL 8.0 - Volumen persistente: baifa_api_dbdata)
```

---

## 📋 Requisitos

* **Docker Engine** y **Docker Compose** v2+.
* En Windows: **WSL 2** (Ubuntu).
* **Git**.

> [!NOTE]
> No necesitas tener PHP, Composer ni MySQL instalados en tu máquina anfitriona; todo se ejecuta dentro de Docker.

---

## ⚡ Inicio Rápido

### 1. Clonar y configurar entorno
```bash
git clone https://github.com/tu-usuario/baifa-api.git
cd baifa-api
cp .env.example .env
```

### 2. Levantar los contenedores
```bash
docker compose up -d
```

### 3. Generar clave y ejecutar migraciones
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 4. Probar la API
```bash
curl http://localhost:8000/api/v1/health
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

## 📖 Documentación de Endpoints (OpenAPI)

La documentación de los endpoints se encuentra en:

📄 **[`docs/openapi.yaml`](docs/openapi.yaml)**

Puedes visualizarla o probarla directamente:
* Importando `docs/openapi.yaml` en **Postman** o **Insomnia**.
* Pegándolo en [editor.swagger.io](https://editor.swagger.io/).
* Usando la extensión **OpenAPI (Swagger) Editor** en VS Code.

---

## 🛠️ Comandos Frecuentes

```bash
# Ver estado de los contenedores
docker compose ps

# Ver logs en vivo
docker compose logs -f

# Detener los contenedores
docker compose down

# Iniciar los contenedores
docker compose up -d

# Ejecutar comandos Artisan
docker compose exec app php artisan <comando>

# Ejecutar migraciones
docker compose exec app php artisan migrate

# Ejecutar pruebas automatizadas
docker compose exec app php artisan test

# Formatear código con Laravel Pint
docker compose exec app ./vendor/bin/pint
```

---

## 📄 Licencia

Este proyecto está bajo la [Licencia MIT](LICENSE).