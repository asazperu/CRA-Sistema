<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        view('dashboard/index', ['title' => 'Panel', 'user' => $user]);
    }

    public function admin(): void
    {
        $user = Auth::user();
        view('dashboard/admin', ['title' => 'Panel Admin', 'user' => $user]);
    }
}
