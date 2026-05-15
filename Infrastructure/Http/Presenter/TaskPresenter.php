<?php

declare(strict_types=1);

namespace Infrastructure\Http\Presenter;

use Application\DTO\Task\TaskPage;
use Application\Mapper\TaskSnapshotMapper;
use Domain\Task\Task;

final readonly class TaskPresenter
{
    public function __construct(
        private TaskSnapshotMapper $taskSnapshotMapper,
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     description: string|null,
     *     status: string,
     *     createdAt: string
     * }
     */
    public function present(Task $task): array
    {
        return $this->taskSnapshotMapper->toArray($task);
    }

    /**
     * @return array{
     *     items: array<int, array<string, string|null>>,
     *     nextCursor: string|null
     * }
     */
    public function presentPage(TaskPage $page): array
    {
        return [
            'items'      => array_map(
                fn(Task $task): array => $this->present($task),
                $page->items,
            ),
            'nextCursor' => $page->nextCursor,
        ];
    }
}
