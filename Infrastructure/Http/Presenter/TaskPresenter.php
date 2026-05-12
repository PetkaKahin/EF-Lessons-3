<?php

declare(strict_types=1);

namespace Infrastructure\Http\Presenter;

use Domain\Task\DTO\TaskPage;
use Domain\Task\Task;

final class TaskPresenter
{
    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string|null,
     *     status: string,
     *     createdAt: string
     * }
     */
    public function present(Task $task): array
    {
        return [
            'id'          => $task->id->value,
            'title'       => $task->title,
            'description' => $task->description,
            'status'      => $task->status->value,
            'createdAt'   => $task->createdAt,
        ];
    }

    /**
     * @return array{
     *     items: array<int, array<string, string|int|null>>,
     *     nextCursor: int|null
     * }
     */
    public function presentPage(TaskPage $page): array
    {
        return [
            'items'      => array_map(
                fn(Task $task): array => $this->present($task),
                $page->items,
            ),
            'nextCursor' => $page->nextCursor?->value,
        ];
    }
}
