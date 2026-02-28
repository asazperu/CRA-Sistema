# Castro Romero Abogados (Base MVC PHP 8+)

Base funcional lista para HostGator/cPanel con:
- Front controller (`public/index.php`) + rewrite (`.htaccess`)
- Router liviano
- PDO + prepared statements
- Sesiones seguras + `password_hash()`
- CSRF + validación server-side + sanitización XSS
- Middleware `Auth` y `RoleGuard`
- Login / logout / cambio de contraseña
- Roles: `ADMIN` y `USER`
- Instalador web (`install.php`) que importa `database.sql`, crea admin, escribe `.env` y genera `install.lock`

## Instalación
1. Crear BD y usuario en cPanel/phpMyAdmin.
2. Subir archivos al hosting.
3. Apuntar dominio a `public/` (o dejar `.htaccess` raíz).
4. Abrir `/install.php` y completar datos.
5. Ingresar por `/login` con el admin creado.

## Estructura pedida
- Árbol MVC: `documentos/01-arbol-mvc.md`
- Mapa endpoints: `documentos/02-mapa-rutas.md`
- Esquema MySQL: `database.sql` y `documentos/03-esquema-mysql.md`
