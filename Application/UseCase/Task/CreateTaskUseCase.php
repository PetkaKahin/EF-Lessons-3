<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\Contracts\ClockInterface;
use Application\Contracts\TaskRepositoryInterface;
use Application\Contracts\TaskIdGeneratorInterface;
use Application\DTO\Task\CreateTaskInput;
use Domain\Task\Task;

final readonly class CreateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
        private TaskIdGeneratorInterface $taskIds,
        private ClockInterface $clock,
    ) {
    }

    public function execute(CreateTaskInput $input): Task
    {
        $task = Task::create(
            id: $this->taskIds->next(),
            title: $input->title,
            createdAt: $this->clock->now(),
            description: $input->description,
            status: $input->status,
        );

        $this->tasks->add($task);

        return $task;
    }
}
