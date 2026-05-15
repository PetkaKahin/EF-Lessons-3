<?php

declare(strict_types=1);

namespace Application\Contracts;

use Domain\Task\TaskId;

interface TaskIdGeneratorInterface
{
    public function next(): TaskId;
}
