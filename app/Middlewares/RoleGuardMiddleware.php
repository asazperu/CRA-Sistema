<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Auth;

final class RoleGuardMiddleware
{
    public function handle(string $role): void
    {
        $user = Auth::user();
        if (!$user || ($user['role'] ?? null) !== $role) {
            http_response_code(403);
            exit('403 - No autorizado');
        }
    }
}
