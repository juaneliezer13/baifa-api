# 🚀 Guía de Configuración de Puertos y Dominios en AWS EC2 (baifa-base)

Esta guía documenta los cambios necesarios para operar la aplicación en producción en la instancia de **AWS EC2** con los nuevos puertos y dominios:
- **Frontend (baifa-ui):** Dominio `baifa.batdev.lat` en el puerto **`12001`** (mapeado internamente a 3000).
- **Backend API (baifa-api):** Dominio `baifa-api.batdev.lat` en el puerto **`11001`** (mapeado internamente a 80).

---

## 🌐 1. Esquema de Red y Puertos

| Servicio | Contenedor | Puerto Interno | Puerto Host (EC2) | Dominio Asignado | URL Completa |
| :--- | :--- | :---: | :---: | :--- | :--- |
| **Frontend UI** | `baifa-ui` | `3000` | **`12001`** | `baifa.batdev.lat` | `http://baifa.batdev.lat:12001` |
| **Backend API** | `baifa-api` | `80` | **`11001`** | `baifa-api.batdev.lat` | `http://baifa-api.batdev.lat:11001/api` |
| **Base de Datos** | `baifa-api-db` | `3306` | *(Red interna)* | *(No expuesto)* | Red Docker interna |

> [!IMPORTANT]
> Los registros DNS tipo **A** apuntan exclusivamente a la dirección IP pública del servidor (`3.147.220.136`). Dado que los contenedores escuchan en puertos no estándar (`12001` y `11001`), el acceso directo desde el navegador requiere especificar el puerto en la URL:
> - Frontend: `http://baifa.batdev.lat:12001`
> - API Backend: `http://baifa-api.batdev.lat:11001/api`

---

## 🔒 2. Paso 1 en AWS: Abrir Puertos en el Security Group de EC2

Por seguridad, AWS bloquea por defecto todos los puertos excepto los autorizados en las reglas de entrada (*Inbound Rules*). Para habilitar el tráfico hacia los puertos 11001 y 12001:

1. Ingresa a la **Consola de Administración de AWS** (AWS Management Console).
2. Ve al servicio **EC2** -> **Instancias** (*Instances*).
3. Selecciona tu instancia (`3.147.220.136`).
4. En el panel inferior, haz clic en la pestaña **Seguridad** (*Security*).
5. Haz clic sobre el enlace del **Security Group** asignado (por ejemplo, `launch-wizard-1` o similar).
6. Ve a la pestaña **Reglas de entrada** (*Inbound rules*) y presiona **Editar reglas de entrada** (*Edit inbound rules*).
7. Agrega las dos siguientes reglas:
   - **Regla 1 (API):**
     - **Tipo:** *Custom TCP* (TCP personalizado)
     - **Intervalo de puertos:** `11001`
     - **Origen:** *Anywhere-IPv4* (`0.0.0.0/0`)
     - **Descripción:** `BaiFa API Backend`
   - **Regla 2 (Frontend):**
     - **Tipo:** *Custom TCP* (TCP personalizado)
     - **Intervalo de puertos:** `12001`
     - **Origen:** *Anywhere-IPv4* (`0.0.0.0/0`)
     - **Descripción:** `BaiFa Frontend Nuxt`
8. Presiona **Guardar reglas** (*Save rules*).

---

## 💻 3. Paso 2 en el Servidor EC2: Actualizar `docker-compose.yml`

Conéctate por SSH a tu instancia:

```bash
# Desde la carpeta donde tengas la clave access_baifa_dev.pem:
ssh -i access_baifa_dev.pem ubuntu@3.147.220.136
```

Dirígete al directorio de despliegue:

```bash
cd ~/baifa-base
```

Edita el archivo `docker-compose.yml`:

```bash
nano docker-compose.yml
```

Reemplaza su contenido con la siguiente configuración actualizada (con las nuevas imágenes y mapeos de puertos):

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

Guarda con `Ctrl + O`, presiona `Enter` y sal con `Ctrl + X`.

---

## 🔑 4. Paso 3 en el Servidor EC2: Actualizar el archivo `.env`

Edita el archivo `.env` en `~/baifa-base`:

```bash
nano .env
```

Asegúrate de que las variables de URLs apunten a los nuevos dominios y puertos:

```env
APP_NAME=baifa-api
APP_ENV=production
APP_KEY=base64:tdjPKww/y1VnEgw6gl7B7oMEmG4QrQPnZFn5Bjwducc=
APP_DEBUG=false
APP_URL=http://baifa-api.batdev.lat:11001
FRONTEND_URL=http://baifa.batdev.lat:12001

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stderr
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=baifa_api
DB_USERNAME=baifa_user
DB_PASSWORD=secret

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=baifa.com.ve
MAIL_PORT=465
MAIL_USERNAME=no-reply@baifa.com.ve
MAIL_PASSWORD=y+A#SnZFn7Lpk3H6
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="no-reply@baifa.com.ve"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# URL base pública que consumirá el frontend desde el navegador
NUXT_PUBLIC_API_BASE=http://baifa-api.batdev.lat:11001/api
```

---

## 🔄 5. Paso 4: Descargar y Levantar los Nuevos Contenedores

En el servidor EC2, dentro de `~/baifa-base`:

```bash
# 1. Descargar las nuevas versiones desde Docker Hub
docker compose pull

# 2. Detener y remover los contenedores anteriores
docker compose down

# 3. Levantar los nuevos servicios en segundo plano
docker compose up -d

# 4. Limpiar y refrescar la caché de configuración en Laravel
docker exec -it baifa-api php artisan optimize:clear
docker exec -it baifa-api php artisan config:cache

# 5. Ejecutar migraciones por seguridad
docker exec -it baifa-api php artisan migrate --force
```

---

## ✅ 6. Paso 5: Verificación del Despliegue

### Diagnóstico desde la terminal del servidor:
```bash
# Verificar que los contenedores estén levantados y escuchando en los puertos correspondientes:
docker ps

# Probar la API localmente en el puerto 11001:
curl -i http://localhost:11001/api/v1/health

# Probar el Frontend localmente en el puerto 12001:
curl -i http://localhost:12001
```

### Diagnóstico desde tu navegador / máquina local:
1. **API Health:** Abre en tu navegador `http://baifa-api.batdev.lat:11001/api/v1/health`
   - Debe responder JSON con estado `"status": "ok"`, `"database": "connected"`.
2. **Frontend UI:** Abre en tu navegador `http://baifa.batdev.lat:12001`
   - Debe cargar la interfaz web de BaiFa conectada correctamente a `http://baifa-api.batdev.lat:11001/api`.

---

## 🔀 7. (Opcional) Proxy Inverso Nginx para Acceder Sin Escribir Puertos

Si en algún momento deseas que tus usuarios ingresen a `http://baifa.batdev.lat` y `http://baifa-api.batdev.lat` por el puerto estándar **80** (o con certificado SSL en el puerto **443**) sin escribir `:12001` ni `:11001`, puedes instalar un Nginx en el sistema anfitrión de EC2:

```nginx
# /etc/nginx/sites-available/baifa.conf

server {
    listen 80;
    server_name baifa.batdev.lat;

    location / {
        proxy_pass http://127.0.0.1:12001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name baifa-api.batdev.lat;

    location / {
        proxy_pass http://127.0.0.1:11001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```
*(Para este modo bastaría ejecutar `sudo certbot --nginx` para activar HTTPS gratuito con Let's Encrypt).*
