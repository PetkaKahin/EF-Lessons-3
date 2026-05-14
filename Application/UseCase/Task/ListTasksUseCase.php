<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\Contracts\TaskRepositoryInterface;
use Application\DTO\Task\ListTasksInput;
use Application\DTO\Task\TaskPage;

final readonly class ListTasksUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    public function execute(ListTasksInput $input): TaskPage
    {
        return $this->tasks->findPage($input->status, $input->limit, $input->cursor);
    }
}
