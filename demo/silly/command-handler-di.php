<?php
require_once 'vendor/autoload.php';

use Pluf\Di\Container;
$container = new Container();
$container['the-service-id'] = Container::value(function ($name, $yell) {
    if ($name) {
        $text = 'Hello, ' . $name;
    } else {
        $text = 'Hello';
    }
    
    if ($yell) {
        $text = strtoupper($text);
    }
    echo $text;
    echo "\n";
});

$app = new Silly\Application();
$app->useContainer($container);
$app->command('greet [name] [--yell]', 'the-service-id');
$app->run();

