<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/');
        }

        view('auth/login', ['title' => 'Ingresar']);
    }

    public function login(): void
    {
        verify_csrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['_old'] = ['email' => $email];

        if (!Auth::attempt($email, $password)) {
            flash('error', 'Credenciales inválidas.');
            redirect('/login');
        }

        redirect('/chat');
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        redirect('/login');
    }
}
