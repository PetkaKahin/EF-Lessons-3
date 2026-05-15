<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Task;

use Application\Contracts\TimeFormatterInterface;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;

final readonly class TaskMapper
{
    public function __construct(
        private TimeFormatterInterface $timeFormatter,
    ) {
    }

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
            createdAt: $this->timeFormatter->parseFromDatabase($row['created_at']),
        );
    }

}
