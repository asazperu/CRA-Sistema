<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Autoloader;

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../app/Core/Autoloader.php';

$autoloader = new Autoloader(__DIR__ . '/../app');
$autoloader->register();

App::boot(__DIR__ . '/..', true);
App::run();
