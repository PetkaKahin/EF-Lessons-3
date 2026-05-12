<?php

declare(strict_types=1);

namespace Domain\Task;

use InvalidArgumentException;

final readonly class Task
{
    public function __construct(
        private(set) public TaskId $id,
        private(set) public string $title,
        private(set) public ?string $description,
        private(set) public TaskStatus $status,
        private(set) public string $createdAt,
    ) {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Task title is required.');
        }
    }

    public static function create(
        TaskId $id,
        string $title,
        ?string $description = null,
        TaskStatus $status = TaskStatus::New,
        ?string $createdAt = null,
    ): self {
        return new self(
            id: $id,
            title: $title,
            description: $description,
            status: $status,
            createdAt: $createdAt ?? date(DATE_ATOM),
        );
    }

}
