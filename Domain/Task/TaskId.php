<?php

declare(strict_types=1);

namespace Domain\Task;

use InvalidArgumentException;

final readonly class TaskId
{
    private function __construct(
        private(set) public string $value,
    ) {
    }

    public static function fromData(string $value): self
    {
        $value = strtolower(trim($value));

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('Task id must be a UUID.');
        }

        return new self($value);
    }
}
