<?php

declare(strict_types=1);

use Application\UseCase\Task\CreateTaskUseCase;
use Application\UseCase\Task\DeleteTaskUseCase;
use Application\UseCase\Task\GetTaskUseCase;
use Application\UseCase\Task\ListTasksUseCase;
use Application\UseCase\Task\UpdateTaskUseCase;
use Application\UseCase\EchoJsonUseCase;
use Application\UseCase\GetHealthStatusUseCase;
use Application\UseCase\GetImportantHeadersUseCase;
use Domain\Task\Contracts\TaskRepositoryInterface;
use Infrastructure\Database\MigrationRunner;
use Infrastructure\Database\PdoConnection;
use Infrastructure\Http\Controller\EchoController;
use Infrastructure\Http\Controller\HeadersController;
use Infrastructure\Http\Controller\HealthController;
use Infrastructure\Http\Controller\MigrationController;
use Infrastructure\Http\Controller\TaskController;
use Infrastructure\Http\Presenter\TaskPresenter;
use Infrastructure\Http\RequestMapper\JsonObjectBodyParser;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestMapper;
use Infrastructure\Http\RequestMapper\Task\ListTasksRequestMapper;
use Infrastructure\Http\RequestMapper\Task\TaskIdPathMapper;
use Infrastructure\Http\RequestMapper\Task\TaskStatusParser;
use Infrastructure\Http\RequestMapper\Task\UpdateTaskRequestMapper;
use Infrastructure\Kernel\Router;

return static function (
    Router $router,
    TaskRepositoryInterface $tasks,
    MigrationRunner $migrationRunner,
    PdoConnection $pdoConnection,
): void {
    $jsonObjectBodyParser = new JsonObjectBodyParser();
    $taskStatusParser = new TaskStatusParser();
    $migrationController = new MigrationController($migrationRunner, $pdoConnection);

    $taskController = new TaskController(
        createTask: new CreateTaskUseCase($tasks),
        listTasks: new ListTasksUseCase($tasks),
        getTask: new GetTaskUseCase($tasks),
        updateTask: new UpdateTaskUseCase($tasks),
        deleteTask: new DeleteTaskUseCase($tasks),
        createTaskRequestMapper: new CreateTaskRequestMapper($jsonObjectBodyParser, $taskStatusParser),
        listTasksRequestMapper: new ListTasksRequestMapper($taskStatusParser),
        updateTaskRequestMapper: new UpdateTaskRequestMapper($jsonObjectBodyParser, $taskStatusParser),
        taskIdPathMapper: new TaskIdPathMapper(),
        taskPresenter: new TaskPresenter(),
    );

    $router->get('/health', new HealthController(new GetHealthStatusUseCase()));
    $router->post('/echo', new EchoController(new EchoJsonUseCase()));
    $router->get('/headers', new HeadersController(new GetImportantHeadersUseCase()));
    $router->post('/migrations/run', [$migrationController, 'run']);

    $router->post('/tasks', [$taskController, 'create']);
    $router->get('/tasks', [$taskController, 'list']);
    $router->get('/tasks/{id}', [$taskController, 'get']);
    $router->patch('/tasks/{id}', [$taskController, 'patch']);
    $router->delete('/tasks/{id}', [$taskController, 'delete']);
};
