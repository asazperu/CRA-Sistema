# Castro Romero Abogados (PHP MVC + MySQL)

Sistema web estilo ChatGPT/Gemini orientado a abogados peruanos.

## Stack
- PHP 8+ con MVC real (Front Controller + Router + Controllers + Models + Views)
- MySQL/MariaDB (gestionado en phpMyAdmin)
- HTML/CSS/JS
- Despliegue compatible con HostGator/cPanel

## Instalación en HostGator/cPanel
1. Crea una base de datos y usuario desde **cPanel > MySQL Databases**.
2. Sube el proyecto al hosting (public_html o subcarpeta).
3. Asegura que el dominio apunte a `public/` (o usa `.htaccess` raíz incluido).
4. Abre `https://tu-dominio.com/install.php`.
5. Completa el formulario:
   - Datos DB (la base creada por ti en cPanel/phpMyAdmin)
   - URL pública
   - Usuario administrador inicial
6. El instalador hará:
   - Importación automática de `database.sql`
   - Creación de admin inicial
   - Escritura de `.env`
   - Bloqueo por `install.lock`

## Seguridad de instalación
- Si existe `install.lock`, el instalador se bloquea.
- Para reinstalar: elimina manualmente `install.lock` (solo en entornos controlados).

## Rutas principales
- `/login`
- `/chat`
- `/install` (vía `install.php`)

