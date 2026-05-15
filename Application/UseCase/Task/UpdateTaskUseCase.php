<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\Contracts\TaskRepositoryInterface;
use Application\DTO\Task\UpdateTaskInput;
use Application\UseCase\Webhook\SendTaskDoneWebhookUseCase;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

final readonly class UpdateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
        private SendTaskDoneWebhookUseCase $sendTaskDoneWebhook,
    ) {
    }

    public function execute(TaskId $id, UpdateTaskInput $input): ?Task
    {
        $task = $this->tasks->findById($id);

        if ($task === null) {
            return null;
        }

        $wasDone = $task->status === TaskStatus::Done;

        if (!$this->hasChanges($task, $input)) {
            return $task;
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

        if (!$this->tasks->update($task)) {
            return null;
        }

        if (!$wasDone && $task->status === TaskStatus::Done) {
            $this->sendTaskDoneWebhook->execute($task);
        }

        return $task;
    }

    private function hasChanges(Task $task, UpdateTaskInput $input): bool
    {
        if ($input->hasTitle() && $input->title !== $task->title) {
            return true;
        }

        if ($input->hasDescription() && $input->description !== $task->description) {
            return true;
        }

        if ($input->hasStatus() && $input->status !== $task->status) {
            return true;
        }

        return false;
    }
}
