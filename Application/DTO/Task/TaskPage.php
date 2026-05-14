<?php

declare(strict_types=1);

namespace Application\DTO\Task;

use Domain\Task\Task;

final readonly class TaskPage
{
    /**
     * @param Task[] $items
     */
    public function __construct(
        public array $items,
        public ?string $nextCursor,
    ) {
    }
}
