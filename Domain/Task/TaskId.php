<?php

declare(strict_types=1);

namespace Domain\Task;

use InvalidArgumentException;

final readonly class TaskId
{
    private function __construct(
        private(set) public int $value,
    ) {
    }

    public static function fromData(int|string $value): self
    {
        $value = (int) $value;

        if ($value < 1) {
            throw new InvalidArgumentException('Task id must be a positive integer.');
        }

        return new self($value);
    }
}
