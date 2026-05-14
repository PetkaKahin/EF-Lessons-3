<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Application\DTO\Task\ListTasksInput;
use Infrastructure\Kernel\Request;
use InvalidArgumentException;

final readonly class ListTasksRequestMapper
{
    public function __construct(
        private TaskStatusParser $statusParser,
    ) {
    }

    public function map(Request $request): ListTasksInput
    {
        return new ListTasksInput(
            status: $this->statusParser->parse($request->query('status')),
            limit: $this->limit($request->query('limit')),
            cursor: $this->cursor($request->query('cursor')),
        );
    }

    private function limit(?string $value): int
    {
        if ($value === null) {
            return 20;
        }

        if (!ctype_digit($value)) {
            throw new InvalidArgumentException('Limit must be an integer from 1 to 100.');
        }

        return (int) $value;
    }

    private function cursor(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!ctype_digit($value) || (int) $value < 1) {
            throw new InvalidArgumentException('Cursor must be a positive integer string.');
        }

        return $value;
    }
}
