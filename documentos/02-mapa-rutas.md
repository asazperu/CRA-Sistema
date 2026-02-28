# Mapa de rutas / endpoints

## Auth
- `GET /login` -> formulario de ingreso.
- `POST /login` -> autenticación.
- `POST /logout` -> cierre de sesión.
- `GET /register` *(planeado)*.
- `POST /register` *(planeado)*.
- `POST /forgot-password` *(planeado)*.
- `POST /reset-password` *(planeado)*.

## Chat
- `GET /chat` -> listado de conversaciones + chat activo.
- `POST /chat/new` -> crear conversación.
- `POST /chat/message` -> enviar mensaje y recibir respuesta IA.
- `GET /chat/view?id={id}` -> visualizar conversación.
- `PATCH /chat/{id}` *(planeado, rename)*.
- `DELETE /chat/{id}` *(planeado)*.

## Admin
- `GET /admin` *(planeado dashboard admin)*.
- `GET /admin/users` *(planeado)*.
- `PATCH /admin/users/{id}/status` *(planeado)*.
- `GET /admin/settings` *(planeado)*.
- `GET /admin/audit-logs` *(planeado)*.

## KB (Knowledge Base)
- `GET /kb` *(planeado)*.
- `GET /kb/search?q=` *(planeado)*.
- `POST /kb/categories` *(planeado)*.
- `POST /kb/articles` *(planeado)*.
- `PATCH /kb/articles/{id}` *(planeado)*.
- `DELETE /kb/articles/{id}` *(planeado)*.

## Documentos
- `GET /documentos` *(planeado)*.
- `POST /documentos/upload` *(planeado)*.
- `GET /documentos/{id}` *(planeado)*.
- `GET /documentos/{id}/download` *(planeado)*.
- `DELETE /documentos/{id}` *(planeado)*.
- `POST /documentos/process-now` -> procesamiento manual de `pending` con rate limit.

## Export
- `POST /export/chat/{id}.pdf` *(planeado)*.
- `POST /export/chat/{id}.docx` *(planeado)*.
- `GET /export/reportes` *(planeado)*.

## Events
- `GET /eventos` -> lista/calendario de eventos por usuario.
- `POST /eventos/create` -> crear evento manual.
- `GET /eventos/ics?id={id}` -> exportar evento a `.ics`.
- `POST /chat/event/create` -> crear evento desde modal en chat.
- `GET /events/stream` *(planeado - SSE)*.
- `POST /events/webhook/audit` *(planeado)*.
- `POST /events/webhook/import` *(planeado)*.

## Install
- `GET /install` -> vista del instalador (vía `install.php`).
- `POST /install` -> ejecuta importación SQL + admin + `.env` + `install.lock`.
- `GET /install/requirements` *(planeado chequeos)*.
- `POST /install/finalize` *(planeado)*.
