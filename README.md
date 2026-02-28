# Castro Romero Abogados (Base MVC PHP 8+)

Base funcional lista para despliegue en HostGator/cPanel.

## Seguridad y operación (resumen)
- Rate limit en login (5 intentos / 5 min por email+IP).
- Rate limit en chat (8 mensajes / 60s por usuario).
- Manejo de errores centralizado (handler global para web/CLI).
- Secretos fuera del código: `.env` (`DB_*`, `OPENROUTER_API_KEY`, `APP_KEY`).
- Instalador bloqueado después de completar (`install.lock`).

## Paso a paso de instalación (cPanel)

### 1) Crear base de datos y usuario en cPanel
1. Ir a **cPanel > MySQL Databases**.
2. Crear una base de datos (ejemplo: `cpaneluser_cra`).
3. Crear un usuario MySQL (ejemplo: `cpaneluser_crausr`).
4. Asignar el usuario a la base con **ALL PRIVILEGES**.
5. Guardar host (normalmente `localhost`), puerto (`3306`), nombre BD, usuario y contraseña.

### 2) Subir archivos del proyecto
1. Subir todo el proyecto al hosting (File Manager o FTP).
2. Asegurar que el dominio/subdominio apunte a `public/`.
3. Verificar permisos de escritura para raíz del proyecto (para crear `.env` e `install.lock`).

### 3) Configurar `.env`
Puedes dejar que el instalador lo genere automáticamente o crearlo manualmente con:

```env
APP_NAME=Castro Romero Abogados
APP_URL=https://tu-dominio.com
APP_ENV=production
APP_DEBUG=0
APP_KEY=generar_un_valor_aleatorio_largo
DB_HOST=localhost
DB_PORT=3306
DB_NAME=tu_bd
DB_USER=tu_usuario
DB_PASS=tu_password
OPENROUTER_API_KEY=
OPENROUTER_MODEL=openai/gpt-4o-mini
```

> Nunca subas `.env` al repositorio.

### 4) Ejecutar `/install`
1. Abrir `https://tu-dominio.com/install`.
2. Completar formulario DB + admin inicial.
3. Usar **Probar conexión MySQL**.
4. Ejecutar **Instalar ahora**.
5. El instalador importa `database.sql`, crea admin, guarda `.env`, y crea `install.lock`.

### 5) Entrar como admin
1. Ir a `https://tu-dominio.com/login`.
2. Iniciar sesión con el admin creado.

### 6) Configurar OpenRouter y marca
1. En `.env`, establecer `OPENROUTER_API_KEY`.
2. En panel admin, ajustar modelo/temperatura/tokens.
3. Configurar marca: nombre, logo y colores.

## Worker de documentos (cron HostGator)
- Script: `php cli/worker.php --limit=10`
- Procesa documentos `pending` (extraer texto, resumir, indexar chunks).
- Cron sugerido cada 5 minutos:

```cron
*/5 * * * * /usr/local/bin/php /home/USUARIO/public_html/tu-app/cli/worker.php --limit=10 >> /home/USUARIO/worker_docs.log 2>&1
```

También existe alternativa manual en `/documentos` con **Procesar ahora** (rate limit por usuario).
