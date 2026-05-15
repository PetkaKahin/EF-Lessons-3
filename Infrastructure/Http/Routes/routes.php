<?php

declare(strict_types=1);

use Infrastructure\Http\Controller\EchoController;
use Infrastructure\Http\Controller\HeadersController;
use Infrastructure\Http\Controller\HealthController;
use Infrastructure\Http\Controller\TaskController;
use Infrastructure\Http\Controller\WebhookReceiverController;
use Infrastructure\Http\Middleware\BearerTokenMiddleware;
use Infrastructure\Http\Middleware\CorsMiddleware;
use Infrastructure\Http\Middleware\DebugHeadersMiddleware;
use Infrastructure\DI\Container;
use Infrastructure\Kernel\Router;

return static function (
    Router $router,
    Container $container,
): void {
    $router->middleware($container->get(DebugHeadersMiddleware::class));
    $router->middleware($container->get(CorsMiddleware::class), '/tasks');

    $taskController = $container->get(TaskController::class);
    $auth = $container->get(BearerTokenMiddleware::class);

    $router->get('/health', $container->get(HealthController::class));
    $router->post('/echo', $container->get(EchoController::class));
    $router->get('/headers', $container->get(HeadersController::class));
    $router->post('/webhook-receiver', $container->get(WebhookReceiverController::class));

    $router->post('/tasks', [$taskController, 'create'], $auth);
    $router->get('/tasks', [$taskController, 'list']);
    $router->get('/tasks/{id}', [$taskController, 'get']);
    $router->patch('/tasks/{id}', [$taskController, 'patch'], $auth);
    $router->delete('/tasks/{id}', [$taskController, 'delete'], $auth);
};
