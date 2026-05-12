<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\Task\CreateTaskUseCase;
use Application\UseCase\Task\DeleteTaskUseCase;
use Application\UseCase\Task\GetTaskUseCase;
use Application\UseCase\Task\ListTasksUseCase;
use Application\UseCase\Task\UpdateTaskUseCase;
use DomainException;
use Infrastructure\Http\Presenter\TaskPresenter;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestMapper;
use Infrastructure\Http\RequestMapper\Task\ListTasksRequestMapper;
use Infrastructure\Http\RequestMapper\Task\TaskIdPathMapper;
use Infrastructure\Http\RequestMapper\Task\UpdateTaskRequestMapper;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\NoContentResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;
use InvalidArgumentException;
use JsonException;
use Throwable;

final readonly class TaskController
{
    public function __construct(
        private CreateTaskUseCase $createTask,
        private ListTasksUseCase $listTasks,
        private GetTaskUseCase $getTask,
        private UpdateTaskUseCase $updateTask,
        private DeleteTaskUseCase $deleteTask,
        private CreateTaskRequestMapper $createTaskRequestMapper,
        private ListTasksRequestMapper $listTasksRequestMapper,
        private UpdateTaskRequestMapper $updateTaskRequestMapper,
        private TaskIdPathMapper $taskIdPathMapper,
        private TaskPresenter $taskPresenter,
    ) {
    }

    public function create(Request $request): Response
    {
        try {
            $task = $this->createTask->execute(
                $this->createTaskRequestMapper->map($request),
            );

            return new JsonResponse(
                data: $this->taskPresenter->present($task),
                statusCode: 201,
                headers: ['Location' => '/tasks/' . $task->id->value],
            );
        } catch (JsonException) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }
    }

    public function list(Request $request): Response
    {
        try {
            $page = $this->listTasks->execute(
                $this->listTasksRequestMapper->map($request),
            );

            return new JsonResponse($this->taskPresenter->presentPage($page));
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }
    }

    public function get(Request $request): Response
    {
        try {
            $task = $this->getTask->execute($this->taskIdPathMapper->map($request));

            if ($task === null) {
                return new JsonResponse(['error' => 'Task not found'], 404);
            }

            return new JsonResponse($this->taskPresenter->present($task));
        } catch (Throwable $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }
    }

    public function patch(Request $request): Response
    {
        try {
            $task = $this->updateTask->execute(
                id: $this->taskIdPathMapper->map($request),
                input: $this->updateTaskRequestMapper->map($request),
            );

            if ($task === null) {
                return new JsonResponse(['error' => 'Task not found'], 404);
            }

            return new JsonResponse($this->taskPresenter->present($task));
        } catch (JsonException) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }
    }

    public function delete(Request $request): Response
    {
        $deleted = $this->deleteTask->execute($this->taskIdPathMapper->map($request));

        if (!$deleted) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new NoContentResponse();
    }
}
