<?php

declare(strict_types=1);

namespace Application\DTO\Task;

use Domain\Task\TaskStatus;
use InvalidArgumentException;

final readonly class ListTasksInput
{
    public function __construct(
        public ?TaskStatus $status,
        public int $limit,
        public ?string $cursor,
    ) {
        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Limit must be an integer from 1 to 100.');
        }
    }
}
