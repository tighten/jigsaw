<?php

require __DIR__ . '/vendor/autoload.php';

// TODO use __DIR__??
$app = new \TightenCo\Jigsaw\Container(getcwd());
$app->bootstrap([]);

if (file_exists($bootstrapFile = $app->basePath('bootstrap.php'))) {
    $events = $app->events;
    $container = $app;
    include $bootstrapFile;
}
