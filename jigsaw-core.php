<?php

require __DIR__ . '/vendor/autoload.php';

// TODO use __DIR__??
$container = new \TightenCo\Jigsaw\Container(getcwd());

$container->bootstrap([]);

if (file_exists($bootstrapFile = $container->basePath('bootstrap.php'))) {
    $events = $container->events;
    include $bootstrapFile;
}
