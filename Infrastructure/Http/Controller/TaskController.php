<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Application\UseCase\Task\CreateTaskIdempotentlyUseCase;
use Application\UseCase\Task\DeleteTaskUseCase;
use Application\UseCase\Task\GetTaskUseCase;
use Application\UseCase\Task\ListTasksUseCase;
use Application\UseCase\Task\UpdateTaskUseCase;
use Domain\Task\Task;
use Infrastructure\Http\Presenter\TaskPresenter;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestHasher;
use Infrastructure\Http\RequestMapper\Task\CreateTaskRequestMapper;
use Infrastructure\Http\RequestMapper\Task\ListTasksRequestMapper;
use Infrastructure\Http\RequestMapper\Task\TaskIdPathMapper;
use Infrastructure\Http\RequestMapper\Task\UpdateTaskRequestMapper;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\NoContentResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class TaskController
{
    public function __construct(
        private CreateTaskIdempotentlyUseCase $createTask,
        private ListTasksUseCase $listTasks,
        private GetTaskUseCase $getTask,
        private UpdateTaskUseCase $updateTask,
        private DeleteTaskUseCase $deleteTask,
        private CreateTaskRequestMapper $createTaskRequestMapper,
        private CreateTaskRequestHasher $createTaskRequestHasher,
        private ListTasksRequestMapper $listTasksRequestMapper,
        private UpdateTaskRequestMapper $updateTaskRequestMapper,
        private TaskIdPathMapper $taskIdPathMapper,
        private TaskPresenter $taskPresenter,
    ) {
    }

    public function create(Request $request): Response
    {
        $input = $this->createTaskRequestMapper->map($request);
        $idempotencyKey = $this->idempotencyKey($request);

        $task = $this->createTask->execute(
            input: $input,
            idempotencyKey: $idempotencyKey,
            requestHash: $idempotencyKey === null
                ? null
                : $this->createTaskRequestHasher->hash($request, $input),
        );

        return $this->createdTaskResponse($task);
    }

    public function list(Request $request): Response
    {
        $page = $this->listTasks->execute(
            $this->listTasksRequestMapper->map($request),
        );

        return new JsonResponse($this->taskPresenter->presentPage($page));
    }

    public function get(Request $request): Response
    {
        $task = $this->getTask->execute($this->taskIdPathMapper->map($request));

        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new JsonResponse($this->taskPresenter->present($task));
    }

    public function patch(Request $request): Response
    {
        $task = $this->updateTask->execute(
            id: $this->taskIdPathMapper->map($request),
            input: $this->updateTaskRequestMapper->map($request),
        );

        if ($task === null) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new JsonResponse($this->taskPresenter->present($task));
    }

    public function delete(Request $request): Response
    {
        $deleted = $this->deleteTask->execute($this->taskIdPathMapper->map($request));

        if (!$deleted) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new NoContentResponse();
    }

    private function idempotencyKey(Request $request): ?string
    {
        $key = $request->header('Idempotency-Key');

        if ($key === null) {
            return null;
        }

        $key = trim($key);

        return $key === '' ? null : $key;
    }

    private function createdTaskResponse(Task $task): JsonResponse
    {
        $data = $this->taskPresenter->present($task);

        return new JsonResponse(
            data: $data,
            statusCode: 201,
            headers: ['Location' => '/tasks/' . $data['id']],
        );
    }
}
