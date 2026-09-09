# ADR 0001: Adopción de Stack Docker Compose Personalizado (Nginx + PHP 8.4-FPM + MySQL 8.0)

* **Estado:** Aceptado
* **Fecha:** 2026-09-09
* **Autor:** Juan Chirinos

## Contexto
El proyecto requiere un entorno estandarizado y reproducible que funcione fluidamente en WSL 2 / Windows sin requerir instalación manual de PHP, extensiones, Composer ni MySQL en la máquina anfitriona.

## Decisión
Se implementó un stack personalizado de Docker Compose compuesto por:
1. `webserver`: Contenedor Nginx Alpine.
2. `app`: Contenedor PHP 8.4-FPM con extensiones compiladas (`pdo_mysql`, `mbstring`, `gd`, `zip`, etc.) y Composer.
3. `db`: Contenedor MySQL 8.0 con health check y volumen persistente (`baifa_api_dbdata`).

## Consecuencias
* **Positivas:**
  - Paridad total entre desarrollo y producción.
  - Aislamiento completo de dependencias y versiones de PHP.
  - Arranque coordinado mediante `depends_on` con condición `service_healthy`.
* **Consideraciones:**
  - Los comandos `artisan` y `composer` deben ejecutarse vía `docker compose exec app ...`.