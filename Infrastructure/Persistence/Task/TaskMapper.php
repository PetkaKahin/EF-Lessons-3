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
     *     id: int|string,
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
            id: TaskId::fromData($row['id']),
            title: $row['title'],
            description: $row['description'],
            status: TaskStatus::from($row['status']),
            createdAt: $row['created_at'],
        );
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string|null,
     *     status: string
     * }
     */
    public function toArray(Task $task): array
    {
        return [
            'id'          => $task->id->value,
            'title'       => $task->title,
            'description' => $task->description,
            'status'      => $task->status->value,
        ];
    }
}
