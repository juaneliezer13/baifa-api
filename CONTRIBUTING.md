# Guía de Contribución - baifa-api

¡Gracias por tu interés en contribuir a **baifa-api**! Para mantener la calidad del código, un flujo de trabajo ordenado y consistencia en el repositorio, te pedimos seguir las siguientes directrices.

---

## 🌿 Flujo de Ramas (Git Workflow)

1. La rama principal y estable es `main`.
2. Para cada nueva funcionalidad o corrección, crea una rama derivada con una nomenclatura descriptiva:
   - `feat/nombre-de-la-funcionalidad`
   - `fix/descripcion-del-bug`
   - `docs/nombre-de-la-documentacion`
   - `refactor/componente-a-refactorizar`

Ejemplo:
```bash
git checkout -b feat/gestion-usuarios
```

---

## 📝 Convención de Commits (Conventional Commits)

Utilizamos el estándar [Conventional Commits](https://www.conventionalcommits.org/):

* `feat:` Nueva funcionalidad para el usuario/API.
* `fix:` Corrección de un error/bug.
* `docs:` Cambios únicamente en documentación (`README.md`, `openapi.yaml`, etc.).
* `style:` Cambios de formato de código (espacios, indentación) sin afectar lógica.
* `refactor:` Refactorización de código que no añade funcionalidades ni corrige bugs.
* `test:` Añadir o modificar pruebas unitarias o de integración.
* `chore:` Tareas de mantenimiento, actualización de Docker, paquetes o configuración.

**Ejemplos válidos:**
```bash
git commit -m "feat(auth): implementar endpoint de login con tokens JWT"
git commit -m "fix(db): corregir mapeo de tipos en migracion de usuarios"
git commit -m "docs(api): documentar endpoint /v1/auth en openapi.yaml"
```

---

## 🎨 Estándares de Código y Pruebas

Antes de enviar cualquier cambio:

1. **Formateo de código:** Ejecuta Laravel Pint para asegurar cumplimiento con PSR-12:
   ```bash
   docker compose exec app ./vendor/bin/pint
   ```
2. **Pruebas automatizadas:** Asegura que todos los tests pasen exitosamente:
   ```bash
   docker compose exec app php artisan test
   ```
3. **Documentación OpenAPI:** Si agregas o modificas un endpoint de la API, actualiza `docs/openapi.yaml` y `CHANGELOG.md`.

---

## 🚀 Proceso de Pull Requests

1. Sube tu rama a tu repositorio remoto:
   ```bash
   git push origin feat/nombre-funcionalidad
   ```
2. Abre un Pull Request apuntando a la rama `main`.
3. Completa la plantilla de Pull Request describiendo los cambios y cómo probarlos.
4. Espera la revisión y aprobación antes de fusionar.