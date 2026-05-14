<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\Contracts\TaskRepositoryInterface;
use Application\DTO\Task\UpdateTaskInput;
use Domain\Task\Task;
use Domain\Task\TaskId;

final readonly class UpdateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    public function execute(TaskId $id, UpdateTaskInput $input): ?Task
    {
        $task = $this->tasks->findById($id);

        if ($task === null) {
            return null;
        }

        if ($input->hasTitle()) {
            $task->rename($input->title);
        }

        if ($input->hasDescription()) {
            $task->changeDescription($input->description);
        }

        if ($input->hasStatus()) {
            $task->changeStatus($input->status);
        }

        return $this->tasks->update($task, $input);
    }
}
