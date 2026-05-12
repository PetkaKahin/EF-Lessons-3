<?php

declare(strict_types=1);

namespace Application\UseCase\Task;

use Application\DTO\Task\CreateTaskInput;
use Domain\Task\Contracts\TaskRepositoryInterface;
use Domain\Task\Task;

final readonly class CreateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {
    }

    public function execute(CreateTaskInput $input): Task
    {
        return $this->tasks->create($input->title, $input->description, $input->status);
    }
}
