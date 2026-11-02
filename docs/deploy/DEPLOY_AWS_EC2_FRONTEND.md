# Guía de Despliegue de Frontend (baifa-ui) en AWS EC2

Esta guía describe el procedimiento paso a paso para construir la imagen de Docker de **baifa-ui** (Nuxt 4 + Vuetify 3), publicarla en Docker Hub y desplegarla en la instancia AWS EC2 junto a **baifa-api**.

---

## 1. Arquitectura de Puertos en EC2

| Servicio | Contenedor | Puerto Interno | Puerto Host (Público) | Dominio / URL Pública |
| :--- | :--- | :---: | :---: | :--- |
| **Frontend Web** | `baifa-ui` | 3000 | **12001** | `http://baifa.batdev.lat:12001` |
| **Backend API** | `baifa-api` | 80 | **11001** | `http://baifa-api.batdev.lat:11001/api` |
| **Base de Datos** | `baifa-api-db` | 3306 | - *(red interna)* | - |

> [!IMPORTANT]
> **Reglas de Inbound en AWS Security Group:**
> En la consola de AWS EC2 -> **Security Groups** (grupo launch-wizard-1 o el asignado), agrega dos reglas de entrada:
> - **Tipo:** Custom TCP, **Puerto:** `12001`, **Origen:** `0.0.0.0/0` (Frontend)
> - **Tipo:** Custom TCP, **Puerto:** `11001`, **Origen:** `0.0.0.0/0` (Backend API)

---

## 2. Construcción y Publicación de la Imagen Docker (Local)

Ejecutar desde el directorio raíz de `baifa-ui`:

```bash
# 1. Compilar el proyecto Nuxt para producción
npm run build

# 2. Iniciar sesión en Docker Hub (si no se ha hecho previamente)
docker login

# 3. Construir la imagen etiquetada con la versión de release
docker build -t juaneliezer13/baifa-ui:release-1.0.4 -t juaneliezer13/baifa-ui:latest .

# 4. Subir la imagen a Docker Hub
docker push juaneliezer13/baifa-ui:release-1.0.4
docker push juaneliezer13/baifa-ui:latest
```

---

## 3. Despliegue en el Servidor AWS EC2

### Compose Unificado (Recomendado)
En el servidor EC2, unificamos los servicios en `~/baifa-base/docker-compose.yml`:

```yaml
services:
  front:
    image: juaneliezer13/baifa-ui:release-1.0.4
    container_name: baifa-ui
    restart: unless-stopped
    ports:
      - "12001:3000"
    environment:
      - NODE_ENV=production
      - HOST=0.0.0.0
      - PORT=3000
      - NUXT_PUBLIC_API_BASE=${NUXT_PUBLIC_API_BASE:-http://baifa-api.batdev.lat:11001/api}
    env_file:
      - .env

  api:
    image: juaneliezer13/baifa-api:release-1.0.5
    container_name: baifa-api
    restart: unless-stopped
    ports:
      - "11001:80"
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
    volumes:
      - storage_public:/var/www/storage/app/public

  db:
    image: mysql:8.0
    container_name: baifa-api-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-baifa_api}
      MYSQL_USER: ${DB_USERNAME:-baifa_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_PASSWORD:-secret}"]
      interval: 5s
      timeout: 5s
      retries: 10

volumes:
  storage_public:
  db_data:
```

### Comandos de actualización en el servidor:
```bash
cd ~/baifa-base
docker compose pull
docker compose down
docker compose up -d
```

---

## 4. Verificación de Funcionamiento

1. **Frontend:** Visitar en el navegador `http://baifa.batdev.lat:12001`
2. **API Health:** `curl -i http://baifa-api.batdev.lat:11001/api/v1/health`
3. **Login desde el navegador:** Ingresar con `admin@baifa.com.ve` y contraseña correspondiente.
