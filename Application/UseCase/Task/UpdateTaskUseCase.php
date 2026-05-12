<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\DTO\Task\UpdateTaskInput;
use Domain\Task\Contracts\TaskRepositoryInterface;
use Domain\Task\Task;
use Domain\Task\TaskId;

final readonly class UpdateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    /**
     */
    public function execute(TaskId $id, UpdateTaskInput $input): ?Task
    {
        $task = $this->tasks->findById($id);

        if ($task === null) {
            return null;
        }

        $updatedTask = new Task(
            id: $task->id,
            title: $input->hasTitle() ? $input->title : $task->title,
            description: $input->hasDescription() ? $input->description : $task->description,
            status: $input->hasStatus() ? $input->status : $task->status,
            createdAt: $task->createdAt,
        );

        $this->tasks->save($updatedTask);

        return $updatedTask;
    }
}
