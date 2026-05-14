<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Task;

use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

final class TaskMapper
{
    /**
     * @param array{
     *     id: string,
     *     title: string,
     *     description:
     *     string|null,
     *     status: string,
     *     created_at: string
     * } $row
     */
    public function fromArray(array $row): Task
    {
        return Task::create(
            id: TaskId::fromData((string) $row['id']),
            title: $row['title'],
            description: $row['description'],
            status: TaskStatus::from($row['status']),
            createdAt: $row['created_at'],
        );
    }

}
