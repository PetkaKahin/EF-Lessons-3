<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\Contracts\TaskRepositoryInterface;
use Domain\Task\TaskId;

final readonly class DeleteTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    public function execute(TaskId $id): bool
    {
        return $this->tasks->delete($id);
    }
}
