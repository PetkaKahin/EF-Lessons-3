<?php

declare(strict_types=1);

use Infrastructure\Http\Controller\EchoController;
use Infrastructure\Http\Controller\HeadersController;
use Infrastructure\Http\Controller\HealthController;
use Infrastructure\Http\Controller\TaskController;
use Infrastructure\Http\Middleware\BearerTokenMiddleware;
use Infrastructure\DI\Container;
use Infrastructure\Kernel\Router;

return static function (
    Router $router,
    Container $container,
): void {
    $taskController = $container->get(TaskController::class);
    $auth = $container->get(BearerTokenMiddleware::class);

    $router->get('/health', $container->get(HealthController::class));
    $router->post('/echo', $container->get(EchoController::class));
    $router->get('/headers', $container->get(HeadersController::class));

    $router->post('/tasks', $auth->protect([$taskController, 'create']));
    $router->get('/tasks', [$taskController, 'list']);
    $router->get('/tasks/{id}', [$taskController, 'get']);
    $router->patch('/tasks/{id}', $auth->protect([$taskController, 'patch']));
    $router->delete('/tasks/{id}', $auth->protect([$taskController, 'delete']));
};
