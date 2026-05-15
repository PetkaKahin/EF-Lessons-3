<?php

declare(strict_types=1);

namespace Application\DTO\Task;

use Domain\Task\TaskStatus;

final readonly class CreateTaskInput
{
    public function __construct(
        public string $title,
        public ?string $description,
        public TaskStatus $status,
    ) {
    }
}
