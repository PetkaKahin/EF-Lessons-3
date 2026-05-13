<?php

declare(strict_types=1);

namespace Application\Contracts;

use Domain\Task\DTO\TaskPage;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

interface TaskRepositoryInterface
{
    public function create(string $title, ?string $description, TaskStatus $status): Task;

    public function findById(TaskId $id): ?Task;

    public function findPage(?TaskStatus $status, int $limit, ?TaskId $cursor): TaskPage;

    public function save(Task $task): void;

    public function delete(TaskId $id): bool;
}
