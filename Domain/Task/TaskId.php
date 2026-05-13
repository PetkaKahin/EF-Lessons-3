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
        // PDO может вернуть id как int, а ctype_digit работает со строкой
        $rawValue = (string) $value;

        if (!ctype_digit($rawValue)) {
            throw new InvalidArgumentException('Task id must be an numeric value.');
        }

        $value = (int) $rawValue;

        if ($value < 1) {
            throw new InvalidArgumentException('Task id must be a positive integer.');
        }

        return new self($value);
    }
}
