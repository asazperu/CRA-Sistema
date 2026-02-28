<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Auth;

final class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }
    }
}
