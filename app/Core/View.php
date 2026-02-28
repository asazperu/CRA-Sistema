<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);

        ob_start();
        require base_path('app/Views/' . $view . '.php');
        $content = ob_get_clean();

        require base_path('app/Views/' . $layout . '.php');
    }
}
