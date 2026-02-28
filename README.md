# Castro Romero Abogados (PHP MVC + MySQL)

Sistema web estilo ChatGPT/Gemini orientado a abogados peruanos.

## Stack
- PHP 8+ con MVC real
- MySQL/MariaDB (gestionado en phpMyAdmin)
- HTML/CSS/JS
- Compatible con HostGator/cPanel

## Entregables solicitados
- Árbol MVC: `documentos/01-arbol-mvc.md`
- Mapa de rutas/endpoints: `documentos/02-mapa-rutas.md`
- Esquema DB completo: `database.sql` + `documentos/03-esquema-mysql.md`

## Instalación en HostGator/cPanel
1. Crear base de datos y usuario desde **cPanel > MySQL Databases**.
2. Subir el proyecto al hosting.
3. Apuntar el dominio a `public/` (o usar `.htaccess` raíz).
4. Abrir `https://tu-dominio.com/install.php`.
5. Completar credenciales DB, URL y admin inicial.

El instalador:
- Importa `database.sql` sobre una base ya creada por el usuario.
- Crea usuario administrador inicial.
- Escribe `.env`.
- Genera `install.lock` para bloquear reinstalación.

## CLI útil
- `php cli/install_check.php` -> valida presencia de `.env` e `install.lock`.
