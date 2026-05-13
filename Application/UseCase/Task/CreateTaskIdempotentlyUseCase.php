<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\DTO\Task\CreateTaskInput;
use Application\Idempotency\IdempotentResponse;
use Application\UseCase\Idempotency\RunIdempotentOperationUseCase;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;
use InvalidArgumentException;
use RuntimeException;

final readonly class CreateTaskIdempotentlyUseCase
{
    public function __construct(
        private CreateTaskUseCase $createTask,
        private RunIdempotentOperationUseCase $runIdempotentOperation,
    ) {
    }

    public function execute(
        CreateTaskInput $input,
        ?string $idempotencyKey,
        ?string $requestHash,
    ): Task {
        if ($idempotencyKey === null) {
            return $this->createTask->execute($input);
        }

        if ($requestHash === null || trim($requestHash) === '') {
            throw new InvalidArgumentException('Request hash is required for idempotent operation.');
        }

        $response = $this->runIdempotentOperation->execute(
            key: $idempotencyKey,
            requestHash: $requestHash,
            operation: fn(): IdempotentResponse => $this->responseFromTask(
                $this->createTask->execute($input),
            ),
        );

        return $this->taskFromResponse($response);
    }

    private function responseFromTask(Task $task): IdempotentResponse
    {
        return new IdempotentResponse([
            'id' => $task->id->value,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'created_at' => $task->createdAt,
        ]);
    }

    private function taskFromResponse(IdempotentResponse $response): Task
    {
        $data = $response->data;

        return Task::create(
            id: TaskId::fromData($this->id($data)),
            title: $this->string($data, 'title'),
            description: $this->nullableString($data, 'description'),
            status: TaskStatus::from($this->string($data, 'status')),
            createdAt: $this->createdAt($data),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function id(array $data): int|string
    {
        $value = $data['id'] ?? null;

        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Stored idempotent task id is invalid.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function string(array $data, string $field): string
    {
        $value = $data[$field] ?? null;

        if (!is_string($value)) {
            throw new RuntimeException('Stored idempotent task field is invalid: ' . $field . '.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $field): ?string
    {
        if (!array_key_exists($field, $data)) {
            throw new RuntimeException('Stored idempotent task field is missing: ' . $field . '.');
        }

        $value = $data[$field];

        if ($value !== null && !is_string($value)) {
            throw new RuntimeException('Stored idempotent task field is invalid: ' . $field . '.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createdAt(array $data): string
    {
        if (array_key_exists('created_at', $data)) {
            return $this->string($data, 'created_at');
        }

        if (array_key_exists('createdAt', $data)) {
            return $this->string($data, 'createdAt');
        }

        throw new RuntimeException('Stored idempotent task field is missing: created_at.');
    }
}
