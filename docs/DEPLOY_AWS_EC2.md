# 🚀 Guía de Despliegue en AWS EC2 con Docker — baifa-api

Esta guía detalla los pasos exactos para compilar la imagen de producción de **baifa-api**, subirla a un registro de contenedores (Docker Hub o AWS ECR) y desplegarla en tu instancia de **Amazon Web Services (AWS EC2)** utilizando la clave SSH provista.

---

## 📋 Requisitos Previos
1. **Docker** instalado en tu máquina local.
2. **Instancia EC2** en AWS activa (Ubuntu 22.04 / 24.04 o Amazon Linux 2023).
3. **Clave privada SSH:** Ubicada en `baifa-base/access_baifa_dev.pem`.
4. **Security Group de AWS EC2** configurado con las siguientes reglas de entrada (*Inbound Rules*):
   - **SSH (Puerto 22):** Tu IP o `0.0.0.0/0` (para acceso remoto).
   - **HTTP (Puerto 80):** `0.0.0.0/0` (para el tráfico de la API pública).
   - **HTTPS (Puerto 443):** `0.0.0.0/0` (opcional con certificado SSL).

---

## 🛠️ Paso 1: Construir la Imagen Docker en Local

Desde la raíz del proyecto `baifa-api`:

```bash
# Compilar la imagen de producción usando el Dockerfile multietapa
docker build -t baifa-api:latest .
```

*Nota: La imagen compila Nginx + PHP 8.4-FPM + Supervisor en un único contenedor auto-contenido listo para producción.*

---

## 📤 Paso 2: Subir la Imagen a un Registro

### Opción A: Usando Docker Hub (Recomendada por simplicidad)
```bash
# Iniciar sesión en Docker Hub
docker login

# Etiquetar con tu usuario de Docker Hub
docker tag baifa-api:latest <tu-usuario-dockerhub>/baifa-api:latest

# Subir la imagen
docker push <tu-usuario-dockerhub>/baifa-api:latest
```

### Opción B: Usando AWS Elastic Container Registry (ECR)
```bash
# Iniciar sesión en ECR con AWS CLI
aws ecr get-login-password --region <tu-region> | docker login --username AWS --password-stdin <tu-account-id>.dkr.ecr.<tu-region>.amazonaws.com

# Etiquetar y subir
docker tag baifa-api:latest <tu-account-id>.dkr.ecr.<tu-region>.amazonaws.com/baifa-api:latest
docker push <tu-account-id>.dkr.ecr.<tu-region>.amazonaws.com/baifa-api:latest
```

---

## 🔑 Paso 3: Conectarse a la Instancia EC2 mediante SSH

Ubícate en la carpeta `baifa-base` donde se encuentra la clave `access_baifa_dev.pem`:

```bash
# En Linux / macOS / WSL:
chmod 400 access_baifa_dev.pem

# Conectarse a la IP pública de tu instancia EC2 (usuario 'ubuntu' o 'ec2-user'):
ssh -i access_baifa_dev.pem ubuntu@<IP-PUBLICA-DE-TU-EC2>
```

---

## ⚙️ Paso 4: Preparar Docker en la Instancia EC2

Si la instancia no tiene Docker instalado, ejecútalo en la terminal de EC2:

```bash
# En Ubuntu:
sudo apt-get update
sudo apt-get install -y docker.io docker-compose-plugin
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
newgrp docker
```

---

## 🚀 Paso 5: Desplegar el Contenedor en EC2

1. **Crear directorio de despliegue y archivo `.env`:**
```bash
mkdir -p ~/baifa-api && cd ~/baifa-api
nano .env
```
Pega la configuración de producción ajustando los datos de base de datos (RDS o MySQL local):

```env
APP_NAME=Baifa
APP_ENV=production
APP_KEY=base64:... # Generar o usar el de tu proyecto
APP_DEBUG=false
APP_URL=http://<IP-PUBLICA-DE-TU-EC2>

LOG_CHANNEL=stack
LOG_LEVEL=info

# Configuración de Base de Datos (MySQL local o AWS RDS)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1 # O el endpoint de tu RDS
DB_PORT=3306
DB_DATABASE=baifa_api
DB_USERNAME=baifa_user
DB_PASSWORD=secret

# URL del Frontend Nuxt
FRONTEND_URL=http://<IP-O-DOMINIO-FRONTEND>:3000
```

2. **Descargar y ejecutar el contenedor:**
```bash
# Descargar la imagen
docker pull <tu-usuario-dockerhub>/baifa-api:latest

# Detener contenedor anterior si existe
docker stop baifa-api 2>/dev/null || true
docker rm baifa-api 2>/dev/null || true

# Ejecutar en segundo plano mapeando el puerto 80
docker run -d \
  --name baifa-api \
  --restart unless-stopped \
  -p 80:80 \
  --env-file .env \
  <tu-usuario-dockerhub>/baifa-api:latest
```

---

## 🗄️ Paso 6: Ejecutar Migraciones y Datos Iniciales

Una vez levantado el contenedor:

```bash
# Ejecutar migraciones en producción
docker exec -it baifa-api php artisan migrate --force

# Opcional: Ejecutar seeders iniciales
docker exec -it baifa-api php artisan db:seed --force

# Verificar estado de salud de la API
curl http://localhost/api/v1/health
```

---

## 🔍 Comandos Útiles de Mantenimiento en EC2
- **Ver logs en tiempo real:** `docker logs -f baifa-api`
- **Reiniciar el servicio:** `docker restart baifa-api`
- **Entrar a la consola del contenedor:** `docker exec -it baifa-api bash`
- **Limpiar o refrescar caches:** `docker exec -it baifa-api php artisan optimize:clear`
