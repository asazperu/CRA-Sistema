<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = App\Core\App::basePath();
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return App\Core\Config::get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        App\Core\View::render($view, $data, $layout);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return rtrim(config('app.url', ''), '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(): void
    {
        $token = $_POST['_token'] ?? '';

        if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('Token CSRF inválido.');
        }
    }
}


if (!function_exists('sanitize_input')) {
    function sanitize_input(string $value): string
    {
        return trim(strip_tags($value));
    }
}


if (!function_exists('safe_markdown')) {
    function safe_markdown(string $markdown): string
    {
        $escaped = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');

        $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $escaped) ?? $escaped;

        $lines = preg_split('/
||
/', $escaped) ?: [];
        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            $trim = trim($line);
            if (preg_match('/^[-*]\s+(.+)/', $trim, $m) === 1) {
                if (!$inList) {
                    $html .= '<ul>';
                    $inList = true;
                }
                $html .= '<li>' . $m[1] . '</li>';
                continue;
            }

            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }

            if ($trim === '') {
                continue;
            }

            $html .= '<p>' . $trim . '</p>';
        }

        if ($inList) {
            $html .= '</ul>';
        }

        return $html;
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
