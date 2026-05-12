<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Domain\Task\Contracts\TaskRepositoryInterface;
use Domain\Task\Task;
use Domain\Task\TaskId;

final readonly class GetTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    public function execute(TaskId $id): ?Task
    {
        return $this->tasks->findById($id);
    }
}
