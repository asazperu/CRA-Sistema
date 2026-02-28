<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/chat');
        }

        view('auth/login', ['title' => 'Ingresar']);
    }

    public function login(): void
    {
        verify_csrf();

        $email = sanitize_input((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $_SESSION['_old'] = ['email' => $email];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash('error', 'Datos inválidos.');
            redirect('/login');
        }

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

    public function showChangePassword(): void
    {
        view('auth/change_password', ['title' => 'Cambiar contraseña']);
    }

    public function changePassword(): void
    {
        verify_csrf();

        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($new !== $confirm || strlen($new) < 8) {
            flash('error', 'La nueva contraseña debe coincidir y tener al menos 8 caracteres.');
            redirect('/password/change');
        }

        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

        $model = new User();
        if (!$model->verifyPassword((int) $user['id'], $current)) {
            flash('error', 'Contraseña actual incorrecta.');
            redirect('/password/change');
        }

        $model->updatePassword((int) $user['id'], $new);
        flash('success', 'Contraseña actualizada.');
        redirect('/chat');
    }
}
