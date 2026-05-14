<?php

declare(strict_types=1);

namespace Application\Contracts;

use Application\DTO\Task\TaskPage;
use Application\DTO\Task\UpdateTaskInput;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

interface TaskRepositoryInterface
{
    public function create(string $title, ?string $description, TaskStatus $status): Task;

    public function findById(TaskId $id): ?Task;

    public function findPage(?TaskStatus $status, int $limit, ?string $cursor): TaskPage;

    public function update(Task $task, UpdateTaskInput $input): ?Task;

    public function delete(TaskId $id): bool;
}
