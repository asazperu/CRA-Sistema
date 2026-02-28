<?php

return [
    'auth' => ['/login', '/logout', '/password/change'],
    'chat' => ['/chat', '/chat/new', '/chat/message', '/chat/rename', '/chat/delete', '/chat/event/create'],
    'documentos' => ['/documentos', '/documentos/upload', '/documentos/download', '/documentos/delete', '/documentos/reprocess', '/documentos/process-now'],
    'eventos' => ['/eventos', '/eventos/create', '/eventos/ics'],
    'admin' => [
        '/admin', '/admin/brand', '/admin/ai', '/admin/kb/create',
        '/admin/users/create', '/admin/users/reset-password', '/admin/users/toggle-status',
    ],
    'install' => ['/install', '/install/test-connection'],
];
