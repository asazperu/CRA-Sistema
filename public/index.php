<?php

declare(strict_types=1);

use App\Core\App;

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/Autoloader.php';

$autoloader = new App\Core\Autoloader(__DIR__ . '/../app');
$autoloader->register();

App::boot(__DIR__ . '/..');
App::run();
