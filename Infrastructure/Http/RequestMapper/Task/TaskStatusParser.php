<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Domain\Task\TaskStatus;
use InvalidArgumentException;

final class TaskStatusParser
{
    public function parse(?string $value): ?TaskStatus
    {
        if ($value === null) {
            return null;
        }

        $status = TaskStatus::tryFrom($value);

        if ($status === null) {
            throw new InvalidArgumentException('Status must be one of: new, in_progress, done.');
        }

        return $status;
    }
}
