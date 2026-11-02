# 🚀 Guía de Despliegue en AWS EC2 con Docker — baifa-api

Esta guía detalla los pasos exactos para compilar la imagen de producción de **baifa-api**, subirla a un registro de contenedores (Docker Hub o AWS ECR) y desplegarla en tu instancia de **Amazon Web Services (AWS EC2)** utilizando la clave SSH provista.

---

## 📋 Requisitos Previos
1. **Docker** instalado en tu máquina local.
2. **Instancia EC2** en AWS activa (Ubuntu 22.04 / 24.04 o Amazon Linux 2023).
3. **Clave privada SSH:** Ubicada en `access_baifa_dev.pem`.
4. **Security Group de AWS EC2** configurado con las siguientes reglas de entrada (*Inbound Rules*):
   - **SSH (Puerto 22):** Tu IP o `0.0.0.0/0` (para acceso remoto).
   - **Custom TCP (Puerto 11001):** `0.0.0.0/0` (para el tráfico de la API pública).
   - **Custom TCP (Puerto 12001):** `0.0.0.0/0` (para el tráfico del Frontend).
   - **HTTP (Puerto 80) / HTTPS (Puerto 443):** `0.0.0.0/0` (opcional si se utiliza proxy inverso).

---

## 🛠️ Paso 1: Construir la Imagen Docker en Local

Desde la raíz del proyecto `baifa-api`:

```bash
# Compilar la imagen de producción usando el Dockerfile multietapa
docker build -t juaneliezer13/baifa-api:release-1.0.5 -t juaneliezer13/baifa-api:latest .
```

*Nota: La imagen compila Nginx + PHP 8.4-FPM + Supervisor en un único contenedor auto-contenido listo para producción.*

---

## 📤 Paso 2: Subir la Imagen a Docker Hub

```bash
# Iniciar sesión en Docker Hub
docker login

# Subir la imagen
docker push juaneliezer13/baifa-api:release-1.0.5
docker push juaneliezer13/baifa-api:latest
```

---

## 🔑 Paso 3: Conectarse a la Instancia EC2 mediante SSH

Ubícate en la carpeta donde se encuentra la clave `access_baifa_dev.pem`:

```bash
# En Linux / macOS / WSL:
chmod 400 access_baifa_dev.pem

# Conectarse a la IP pública de tu instancia EC2:
ssh -i access_baifa_dev.pem ubuntu@3.147.220.136
```

---

## 🚀 Paso 4: Desplegar el Contenedor en EC2

1. **Directorio de despliegue `~/baifa-base`:**
```bash
cd ~/baifa-base
```

2. **Asegurar variables en `.env`:**
```env
APP_NAME=baifa-api
APP_ENV=production
APP_KEY=base64:tdjPKww/y1VnEgw6gl7B7oMEmG4QrQPnZFn5Bjwducc=
APP_DEBUG=false
APP_URL=http://baifa-api.batdev.lat:11001
FRONTEND_URL=http://baifa.batdev.lat:12001

LOG_CHANNEL=stderr
LOG_LEVEL=error

# Configuración de Base de Datos
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=baifa_api
DB_USERNAME=baifa_user
DB_PASSWORD=secret

# URL del Frontend Nuxt
NUXT_PUBLIC_API_BASE=http://baifa-api.batdev.lat:11001/api
```

3. **Actualizar y levantar contenedores:**
```bash
docker compose pull
docker compose down
docker compose up -d
```

---

## 🗄️ Paso 5: Ejecutar Migraciones y Limpiar Caché

Una vez levantado el contenedor:

```bash
# Limpiar caché de Laravel
docker exec -it baifa-api php artisan optimize:clear
docker exec -it baifa-api php artisan config:cache

# Ejecutar migraciones en producción
docker exec -it baifa-api php artisan migrate --force

# Verificar estado de salud de la API
curl http://localhost:11001/api/v1/health
```

---

## 🔍 Comandos Útiles de Mantenimiento en EC2
- **Ver logs en tiempo real:** `docker logs -f baifa-api`
- **Reiniciar el servicio:** `docker restart baifa-api`
- **Entrar a la consola del contenedor:** `docker exec -it baifa-api bash`
- **Limpiar o refrescar caches:** `docker exec -it baifa-api php artisan optimize:clear`
