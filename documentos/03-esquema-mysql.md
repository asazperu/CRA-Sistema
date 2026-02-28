# Esquema MySQL/MariaDB

Archivo fuente: `database.sql`.

## Módulos cubiertos
- Auth: `users`, `password_resets`, `sessions`
- Chat: `conversations`, `messages`
- Admin: `audit_logs`
- KB: `knowledge_categories`, `knowledge_articles`
- Documentos: `documents`
- Export: `exports`
- Events: `events`, `events_outbox`
- Install: `install_runs`

## Relaciones clave
- `users 1..N conversations`
- `conversations 1..N messages`
- `users 1..N documents`
- `conversations 1..N documents` (nullable)
- `users 1..N exports`
- `users 1..N events`
- `conversations 1..N exports` (nullable)
- `conversations 1..N events` (nullable)
- `knowledge_categories 1..N knowledge_articles`
- `users 1..N knowledge_articles`
- `users 1..N audit_logs`
- `users 1..N password_resets`
- `users 1..N sessions`

## Índices
Todos los índices y FK se encuentran definidos en `database.sql` con nombres explícitos (`idx_*`, `ux_*`, `fk_*`).
