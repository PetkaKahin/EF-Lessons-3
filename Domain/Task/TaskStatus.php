<?php

declare(strict_types=1);

namespace Domain\Task;

enum TaskStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';
}
