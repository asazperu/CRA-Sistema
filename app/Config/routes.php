<?php

return [
    'auth' => ['/login', '/logout', '/password/change'],
    'chat' => ['/chat', '/chat/new', '/chat/message', '/chat/rename', '/chat/delete'],
    'admin' => [
        '/admin', '/admin/brand', '/admin/ai',
        '/admin/users/create', '/admin/users/reset-password', '/admin/users/toggle-status',
    ],
    'install' => ['/install', '/install/test-connection'],
];
