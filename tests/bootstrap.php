<?php
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'SplClassLoader.php');

    $app = new SplClassLoader('Ratchet', __DIR__);
    $app->register();

    $app = new SplClassLoader('Ratchet', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib');
    $app->register();

    $app = new SplClassLoader('Guzzle', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'guzzle' . DIRECTORY_SEPARATOR . 'src');