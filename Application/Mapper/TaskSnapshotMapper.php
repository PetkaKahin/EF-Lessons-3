<?php

declare(strict_types=1);

namespace Application\Mapper;

use Application\Contracts\TimeFormatterInterface;
use Domain\Task\Task;
use Domain\Task\TaskId;
use Domain\Task\TaskStatus;
use RuntimeException;

final class TaskSnapshotMapper
{
    public function __construct(
        private readonly TimeFormatterInterface $timeFormatter,
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
    public function toArray(Task $task): array
    {
        return [
            'id' => $task->id->value,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'createdAt' => $this->timeFormatter->formatForApi($task->createdAt),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): Task
    {
        return Task::create(
            id: TaskId::fromData($this->id($data)),
            title: $this->string($data, 'title'),
            description: $this->nullableString($data, 'description'),
            status: TaskStatus::from($this->string($data, 'status')),
            createdAt: $this->timeFormatter->parseFromApi($this->createdAt($data)),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function id(array $data): string
    {
        $value = $data['id'] ?? null;

        if (!is_string($value)) {
            throw new RuntimeException('Stored idempotent task id is invalid.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function string(array $data, string $field): string
    {
        $value = $data[$field] ?? null;

        if (!is_string($value)) {
            throw new RuntimeException('Stored idempotent task field is invalid: ' . $field . '.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $field): ?string
    {
        if (!array_key_exists($field, $data)) {
            throw new RuntimeException('Stored idempotent task field is missing: ' . $field . '.');
        }

        $value = $data[$field];

        if ($value !== null && !is_string($value)) {
            throw new RuntimeException('Stored idempotent task field is invalid: ' . $field . '.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createdAt(array $data): string
    {
        if (array_key_exists('createdAt', $data)) {
            return $this->string($data, 'createdAt');
        }

        if (array_key_exists('created_at', $data)) {
            return $this->string($data, 'created_at');
        }

        throw new RuntimeException('Stored idempotent task field is missing: createdAt.');
    }
}
