<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\DI\AppContainerFactory;
use Infrastructure\Http\Response\Response;
use Throwable;

final class Application
{
    public function run(): void
    {
        try {
            $response = $this->handle();
        } catch (Throwable $exception) {
            $response = (new ExceptionHandler())->handle($exception);
        }

        $response->send();
    }

    private function handle(): Response
    {
        $container = AppContainerFactory::create();
        /** @var Router $router */
        $router = $container->get(Router::class);
        $registerRoutes = require_once __DIR__ . '/../Http/Routes/routes.php';

        $registerRoutes($router, $container);

        $request = Request::fromGlobals();

        return $router->dispatch($request);
    }
}
