<?php

declare(strict_types=1);

use Playground\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$bootstrap = new Bootstrap(dirname(__DIR__));
$app = $bootstrap->app();
$app->run();
