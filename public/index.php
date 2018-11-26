<?php

$container = require __DIR__ . '/../bootstrap/app.php';
$app = new \Slim\App($container);

require __DIR__ . '/../bootstrap/middlewares.php';
$routes = scandir(__DIR__ . '/../app/Routes/');

//group and inclide routes
$app->group('/api/v1', function () use ($app,$container) {
    foreach ($routes as $route) {
        //include routes
        if (is_file(__DIR__ . '/../app/Routes/' . $route) && mb_substr($route, -4, 4) === '.php') {
            require __DIR__ . '/../app/Routes/' . $route;
        }
    }
})->add($customMiddleWares['jwt']);

$app->add($customMiddleWares['cors']);

$app->run();
