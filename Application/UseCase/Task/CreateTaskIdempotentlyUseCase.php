<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\DTO\Task\CreateTaskInput;
use Application\Idempotency\IdempotentResponse;
use Application\Mapper\TaskSnapshotMapper;
use Application\UseCase\Idempotency\RunIdempotentOperationUseCase;
use Domain\Task\Task;
use InvalidArgumentException;

final readonly class CreateTaskIdempotentlyUseCase
{
    public function __construct(
        private CreateTaskUseCase $createTask,
        private RunIdempotentOperationUseCase $runIdempotentOperation,
        private TaskSnapshotMapper $taskSnapshotMapper,
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
        return new IdempotentResponse($this->taskSnapshotMapper->toArray($task));
    }

    private function taskFromResponse(IdempotentResponse $response): Task
    {
        return $this->taskSnapshotMapper->fromArray($response->data);
    }
}
