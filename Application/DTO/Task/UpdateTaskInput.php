<?php

declare(strict_types=1);

namespace Application\DTO\Task;

use Domain\Task\TaskStatus;
use InvalidArgumentException;

final readonly class UpdateTaskInput
{
    public function __construct(
        private bool $titleProvided,
        public ?string $title,
        private bool $descriptionProvided,
        public ?string $description,
        private bool $statusProvided,
        public ?TaskStatus $status,
    ) {
        if (!$titleProvided && !$descriptionProvided && !$statusProvided) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        if ($statusProvided && $status === null) {
            throw new InvalidArgumentException('Status must be one of: new, in_progress, done.');
        }
    }

    public function hasTitle(): bool
    {
        return $this->titleProvided;
    }

    public function hasDescription(): bool
    {
        return $this->descriptionProvided;
    }

    public function hasStatus(): bool
    {
        return $this->statusProvided;
    }
}
