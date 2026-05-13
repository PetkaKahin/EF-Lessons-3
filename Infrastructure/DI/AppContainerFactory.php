<?php

declare(strict_types=1);

namespace Infrastructure\DI;

use Application\Contracts\IdempotencyKeyRepositoryInterface;
use Application\Contracts\TaskRepositoryInterface;
use Application\Contracts\TransactionManagerInterface;
use Application\UseCase\EchoJsonUseCase;
use Application\UseCase\GetHealthStatusUseCase;
use Application\UseCase\GetImportantHeadersUseCase;
use Application\UseCase\Idempotency\RunIdempotentOperationUseCase;
use Application\UseCase\Task\CreateTaskIdempotentlyUseCase;
use Application\UseCase\Task\CreateTaskUseCase;
use Application\UseCase\Task\DeleteTaskUseCase;
use Application\UseCase\Task\GetTaskUseCase;
use Application\UseCase\Task\ListTasksUseCase;
use Application\UseCase\Task\UpdateTaskUseCase;
use Infrastructure\Database\PdoConnection;
use Infrastructure\Database\PdoTransactionManager;
use Infrastructure\Http\Controller\EchoController;
use Infrastructure\Http\Controller\HeadersController;
use Infrastructure\Http\Controller\HealthController;
use Infrastructure\Http\Controller\TaskController;
use Infrastructure\Http\Presenter\TaskPresenter;
use Infrastructure\Http\RequestMapper\JsonObjectBodyParser;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestHasher;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestMapper;
use Infrastructure\Http\RequestMapper\Task\ListTasksRequestMapper;
use Infrastructure\Http\RequestMapper\Task\TaskIdPathMapper;
use Infrastructure\Http\RequestMapper\Task\TaskStatusParser;
use Infrastructure\Http\RequestMapper\Task\UpdateTaskRequestMapper;
use Infrastructure\Kernel\Router;
use Infrastructure\Persistence\Idempotency\SQLiteIdempotencyKeyRepository;
use Infrastructure\Persistence\Task\SQLiteTaskRepository;
use Infrastructure\Persistence\Task\TaskMapper;

final class AppContainerFactory
{
    public static function create(): Container
    {
        $container = new Container();

        $container->singleton(Router::class, static fn(Container $container): Router => new Router());

        $container->singleton(PdoConnection::class, static fn(Container $container): PdoConnection => new PdoConnection());
        $container->singleton(TaskMapper::class, static fn(Container $container): TaskMapper => new TaskMapper());

        $container->singleton(
            TaskRepositoryInterface::class,
            static fn(Container $container): TaskRepositoryInterface => new SQLiteTaskRepository(
                $container->get(PdoConnection::class),
                $container->get(TaskMapper::class),
            ),
        );

        $container->singleton(
            IdempotencyKeyRepositoryInterface::class,
            static fn(Container $container): IdempotencyKeyRepositoryInterface => new SQLiteIdempotencyKeyRepository(
                $container->get(PdoConnection::class),
            ),
        );

        $container->singleton(
            TransactionManagerInterface::class,
            static fn(Container $container): TransactionManagerInterface => new PdoTransactionManager(
                $container->get(PdoConnection::class),
            ),
        );

        $container->singleton(JsonObjectBodyParser::class, static fn(Container $container): JsonObjectBodyParser => new JsonObjectBodyParser());
        $container->singleton(TaskStatusParser::class, static fn(Container $container): TaskStatusParser => new TaskStatusParser());
        $container->singleton(CreateTaskRequestHasher::class, static fn(Container $container): CreateTaskRequestHasher => new CreateTaskRequestHasher());
        $container->singleton(TaskIdPathMapper::class, static fn(Container $container): TaskIdPathMapper => new TaskIdPathMapper());
        $container->singleton(TaskPresenter::class, static fn(Container $container): TaskPresenter => new TaskPresenter());

        $container->singleton(
            CreateTaskRequestMapper::class,
            static fn(Container $container): CreateTaskRequestMapper => new CreateTaskRequestMapper(
                $container->get(JsonObjectBodyParser::class),
                $container->get(TaskStatusParser::class),
            ),
        );

        $container->singleton(
            UpdateTaskRequestMapper::class,
            static fn(Container $container): UpdateTaskRequestMapper => new UpdateTaskRequestMapper(
                $container->get(JsonObjectBodyParser::class),
                $container->get(TaskStatusParser::class),
            ),
        );

        $container->singleton(
            ListTasksRequestMapper::class,
            static fn(Container $container): ListTasksRequestMapper => new ListTasksRequestMapper(
                $container->get(TaskStatusParser::class),
            ),
        );

        $container->singleton(
            CreateTaskUseCase::class,
            static fn(Container $container): CreateTaskUseCase => new CreateTaskUseCase(
                $container->get(TaskRepositoryInterface::class),
            ),
        );

        $container->singleton(
            ListTasksUseCase::class,
            static fn(Container $container): ListTasksUseCase => new ListTasksUseCase(
                $container->get(TaskRepositoryInterface::class),
            ),
        );

        $container->singleton(
            GetTaskUseCase::class,
            static fn(Container $container): GetTaskUseCase => new GetTaskUseCase(
                $container->get(TaskRepositoryInterface::class),
            ),
        );

        $container->singleton(
            UpdateTaskUseCase::class,
            static fn(Container $container): UpdateTaskUseCase => new UpdateTaskUseCase(
                $container->get(TaskRepositoryInterface::class),
            ),
        );

        $container->singleton(
            DeleteTaskUseCase::class,
            static fn(Container $container): DeleteTaskUseCase => new DeleteTaskUseCase(
                $container->get(TaskRepositoryInterface::class),
            ),
        );

        $container->singleton(
            RunIdempotentOperationUseCase::class,
            static fn(Container $container): RunIdempotentOperationUseCase => new RunIdempotentOperationUseCase(
                $container->get(IdempotencyKeyRepositoryInterface::class),
                $container->get(TransactionManagerInterface::class),
            ),
        );

        $container->singleton(
            CreateTaskIdempotentlyUseCase::class,
            static fn(Container $container): CreateTaskIdempotentlyUseCase => new CreateTaskIdempotentlyUseCase(
                $container->get(CreateTaskUseCase::class),
                $container->get(RunIdempotentOperationUseCase::class),
            ),
        );

        $container->singleton(GetHealthStatusUseCase::class, static fn(Container $container): GetHealthStatusUseCase => new GetHealthStatusUseCase());
        $container->singleton(EchoJsonUseCase::class, static fn(Container $container): EchoJsonUseCase => new EchoJsonUseCase());
        $container->singleton(GetImportantHeadersUseCase::class, static fn(Container $container): GetImportantHeadersUseCase => new GetImportantHeadersUseCase());

        $container->singleton(
            HealthController::class,
            static fn(Container $container): HealthController => new HealthController(
                $container->get(GetHealthStatusUseCase::class),
            ),
        );

        $container->singleton(
            EchoController::class,
            static fn(Container $container): EchoController => new EchoController(
                $container->get(EchoJsonUseCase::class),
            ),
        );

        $container->singleton(
            HeadersController::class,
            static fn(Container $container): HeadersController => new HeadersController(
                $container->get(GetImportantHeadersUseCase::class),
            ),
        );

        $container->singleton(
            TaskController::class,
            static fn(Container $container): TaskController => new TaskController(
                createTask: $container->get(CreateTaskIdempotentlyUseCase::class),
                listTasks: $container->get(ListTasksUseCase::class),
                getTask: $container->get(GetTaskUseCase::class),
                updateTask: $container->get(UpdateTaskUseCase::class),
                deleteTask: $container->get(DeleteTaskUseCase::class),
                createTaskRequestMapper: $container->get(CreateTaskRequestMapper::class),
                createTaskRequestHasher: $container->get(CreateTaskRequestHasher::class),
                listTasksRequestMapper: $container->get(ListTasksRequestMapper::class),
                updateTaskRequestMapper: $container->get(UpdateTaskRequestMapper::class),
                taskIdPathMapper: $container->get(TaskIdPathMapper::class),
                taskPresenter: $container->get(TaskPresenter::class),
            ),
        );

        return $container;
    }
}
