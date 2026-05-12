<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Domain\Task\TaskId;
use Infrastructure\Kernel\Request;

final class TaskIdPathMapper
{
    public function map(Request $request): TaskId
    {
        return TaskId::fromData($request->pathParam('id') ?? '');
    }
}
