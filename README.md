# Castro Romero Abogados (Base MVC PHP 8+)

Base funcional lista para subir a HostGator/cPanel.

## Incluye
- `public/index.php` como front controller.
- Rewrite con `.htaccess` para rutas limpias (`/login`, `/chat`, `/install`, `/documentos`).
- Router liviano.
- PDO + prepared statements.
- Sesiones seguras + `password_hash()/password_verify()`.
- CSRF + validación server-side + sanitización XSS.
- Middlewares `Auth` y `RoleGuard`.
- Login / logout / cambio de contraseña.
- Roles: `ADMIN` y `USER`.
- Chat con OpenRouter (modo normal + streaming opcional).
- Historial de chats (crear/renombrar/borrar).
- Mensajes persistidos en MySQL y logs de uso en `api_usage_logs`.
- Panel ADMIN con marca, IA, CRUD de usuarios, `audit_logs` y consumo.
- Módulo Documentos con:
  - carpeta física fuera de `public/` (`storage/documentos`),
  - upload/list/download/delete/reprocess,
  - validación MIME real y tamaño,
  - renombrado UUID,
  - permisos por usuario,
  - auditoría de acciones.
  - parseo de contenido (PDF/DOCX) y chunking a `document_texts` (800–1500 chars),
  - detección de PDF escaneado (texto vacío) con advertencia de OCR si no hay binarios.

## Configuración OpenRouter
Agrega en `.env` después de instalar:

```env
OPENROUTER_API_KEY=tu_api_key
OPENROUTER_MODEL=openai/gpt-4o-mini
```

## Instalación en HostGator (cPanel)
1. En **cPanel > MySQL Databases**, crea una **base de datos** y un **usuario** MySQL.
2. Asigna el usuario a la base con permisos completos.
3. Sube el proyecto al hosting (dominio o subdominio).
4. Asegura que el sitio apunte a `public/` (o usa el `.htaccess` raíz incluido).
5. Abre `https://tu-dominio.com/install`.
6. En `/install`:
   - ingresa `host`, `usuario`, `password` y `nombre de BD` (ya creada),
   - usa **Probar conexión MySQL**,
   - ejecuta **Instalar ahora**.

## Qué hace `/install`
- Prueba la conexión MySQL.
- Ejecuta `database.sql` automáticamente.
- Crea el usuario `ADMIN` inicial.
- Inicializa settings de marca e IA (incluye system prompt legal por defecto).
- Guarda configuración en `.env` (fuera de `public/`).
- Crea `install.lock` y bloquea re-ejecución.

## Importante
La app **no crea físicamente la base de datos** en hosting; usa la que tú creas en cPanel/phpMyAdmin.
