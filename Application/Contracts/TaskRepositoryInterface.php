<?php

declare(strict_types=1);

namespace Application\Contracts;

use Application\DTO\Task\TaskPage;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

interface TaskRepositoryInterface
{
    public function add(Task $task): void;

    public function findById(TaskId $id): ?Task;

    public function findPage(?TaskStatus $status, int $limit, ?string $cursor): TaskPage;

    public function update(Task $task): bool;

    public function delete(TaskId $id): bool;
}
