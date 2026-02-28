<?php

return [
    'auth' => ['/login', '/logout', '/register', '/forgot-password', '/reset-password'],
    'chat' => ['/chat', '/chat/new', '/chat/message', '/chat/{id}', '/chat/{id}/rename', '/chat/{id}/delete'],
    'admin' => ['/admin', '/admin/users', '/admin/settings', '/admin/audit-logs'],
    'kb' => ['/kb', '/kb/categories', '/kb/articles', '/kb/search'],
    'documentos' => ['/documentos', '/documentos/upload', '/documentos/{id}', '/documentos/{id}/download'],
    'export' => ['/export/chat/{id}.pdf', '/export/chat/{id}.docx', '/export/reportes'],
    'events' => ['/events/stream', '/events/webhook/audit', '/events/webhook/import'],
    'install' => ['/install', '/install/requirements', '/install/run', '/install/finalize'],
];
