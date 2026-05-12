<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\Database\PdoConnection;
use Infrastructure\Persistence\Task\SQLiteTaskRepository;
use Infrastructure\Persistence\Task\TaskMapper;

final class Application
{
    public function run(): void
    {
        $pdoConnection = new PdoConnection();
        $taskRepository = new SQLiteTaskRepository($pdoConnection, new TaskMapper());

        $router = new Router();
        $registerRoutes = require_once __DIR__ . '/../Http/Routes/routes.php';
        $registerRoutes($router, $taskRepository);

        $request = Request::fromGlobals();
        $response = $router->dispatch($request);

        $response->send();
    }
}
