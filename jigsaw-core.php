<?php

require __DIR__ . '/vendor/autoload.php';

// TODO use __DIR__??
$app = new \TightenCo\Jigsaw\Container(getcwd());
$app->bootstrap([]);
