<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\DependencyInjection\AppContainerFactory;

final class Application
{
    public function run(): void
    {
        $container = AppContainerFactory::create();
        /** @var Router $router */
        $router = $container->get(Router::class);
        $registerRoutes = require_once __DIR__ . '/../Http/Routes/routes.php';

        $registerRoutes($router, $container);

        $request = Request::fromGlobals();
        $response = $router->dispatch($request);

        $response->send();
    }
}
