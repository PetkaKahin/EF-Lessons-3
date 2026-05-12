<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\Database\MigrationRunner;
use Infrastructure\Database\PdoConnection;
use Infrastructure\Persistence\Task\SQLiteTaskRepository;
use Infrastructure\Persistence\Task\TaskMapper;

final class Application
{
    public function run(): void
    {
        $pdoConnection = new PdoConnection();
        $migrationRunner = new MigrationRunner(dirname(__DIR__) . '/Database/migrations');
        $taskRepository = new SQLiteTaskRepository($pdoConnection, new TaskMapper());

        $router = new Router();
        $registerRoutes = require_once __DIR__ . '/../Http/Routes/routes.php';
        $registerRoutes($router, $taskRepository, $migrationRunner, $pdoConnection);

        $request = Request::fromGlobals();
        $response = $router->dispatch($request);

        $response->send();
    }
}
