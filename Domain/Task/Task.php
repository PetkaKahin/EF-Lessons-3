<?php

declare(strict_types=1);

namespace Domain\Task;

use InvalidArgumentException;

final class Task
{
    public function __construct(
        public readonly TaskId $id,
        public private(set) string $title,
        public private(set) ?string $description,
        public private(set) TaskStatus $status,
        public readonly string $createdAt,
    ) {
        self::assertTitle($title);
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

    public function rename(string $title): void
    {
        self::assertTitle($title);

        $this->title = $title;
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function changeStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    private static function assertTitle(string $title): void
    {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Task title is required.');
        }
    }
}
