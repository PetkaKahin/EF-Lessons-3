<?php

declare(strict_types=1);

namespace Domain\Task\DTO;

use Domain\Task\Task;
use Domain\Task\TaskId;

final readonly class TaskPage
{
    /**
     * @param Task[] $items
     */
    public function __construct(
        public array $items,
        public ?TaskId $nextCursor,
    ) {
    }
}
