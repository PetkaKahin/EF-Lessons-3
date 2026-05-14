<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\DI\AppContainerFactory;
use Infrastructure\DI\Container;
use Throwable;

final class Application
{
    public function run(): void
    {
        $request = Request::fromGlobals();
        $router = null;

        try {
            $container = AppContainerFactory::create();
            $router = $this->router($container);
            $response = $router->dispatch($request);
        } catch (Throwable $exception) {
            $response = new ExceptionHandler()->handle($exception);
        }

        // делаю тут на случай, если приложение выкинет ошибку, а заголовки отдать всё равно надо
        if ($router !== null) {
            $response = $router->processResponse($request, $response);
        }

        $response->send();
    }

    private function router(Container $container): Router
    {
        /** @var Router $router */
        $router = $container->get(Router::class);
        $registerRoutes = require_once __DIR__ . '/../Http/Routes/routes.php';

        $registerRoutes($router, $container);

        return $router;
    }
}
