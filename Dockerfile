# ==============================================================================
# Dockerfile Multietapa para Producción (AWS EC2 / ECR / Docker Hub)
# Proyecto: Sistema Web de Tracking Logístico BaiFa - Backend API (Laravel 11)
# ==============================================================================

# ------------------------------------------------------------------------------
# Etapa 1: Construcción e instalación de dependencias de Composer (Optimizado)
# ------------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Copiar manifiestos de dependencias primero para aprovechar cache de capas Docker
COPY composer.json composer.lock ./

# Instalar dependencias de producción omitiendo paquetes de desarrollo
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-scripts

# Copiar el código fuente de la aplicación
COPY . .

# Generar autoloader optimizado final
RUN composer dump-autoload --optimize --no-dev

# ------------------------------------------------------------------------------
# Etapa 2: Imagen final de producción (PHP 8.4-FPM + Nginx + Supervisor)
# ------------------------------------------------------------------------------
FROM php:8.4-fpm

LABEL maintainer="BaiFa Development Team"
LABEL description="Imagen todo-en-uno de producción para el backend baifa-api con Nginx y PHP-FPM"

# Instalar dependencias del sistema operativo, Nginx y Supervisor
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar y habilitar extensiones de PHP requeridas por Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Copiar Composer oficial para tareas de mantenimiento en el servidor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuración personalizada de PHP para producción
COPY docker/production/php.ini $PHP_INI_DIR/conf.d/99-baifa-production.ini

# Configuración de Nginx
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf
COPY docker/production/nginx.conf /etc/nginx/conf.d/default.conf

# Configuración de Supervisor para ejecutar Nginx y PHP-FPM conjuntamente
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Directorio de trabajo
WORKDIR /var/www

# Copiar código fuente y dependencias optimizadas de la etapa vendor
COPY . /var/www
COPY --from=vendor /app/vendor /var/www/vendor

# Copiar y dar permisos al script de arranque
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Crear directorios críticos de almacenamiento y ajustar permisos a www-data
RUN mkdir -p /var/www/storage/framework/cache/data \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Puerto HTTP público expuesto
EXPOSE 80

# Script de entrada para preparación de entorno
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Comando por defecto: Supervisor gestionando Nginx y PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
