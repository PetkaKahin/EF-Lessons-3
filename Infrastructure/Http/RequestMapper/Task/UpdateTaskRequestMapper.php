<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Application\DTO\Task\UpdateTaskInput;
use Domain\Task\TaskStatus;
use Infrastructure\Http\RequestMapper\JsonObjectBodyParser;
use Infrastructure\Kernel\Request;
use InvalidArgumentException;
use JsonException;

final readonly class UpdateTaskRequestMapper
{
    public function __construct(
        private JsonObjectBodyParser $bodyParser,
        private TaskStatusParser $statusParser,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function map(Request $request): UpdateTaskInput
    {
        $payload = $this->bodyParser->parse($request);
        $this->assertAllowedFields($payload, ['title', 'description', 'status']);

        return new UpdateTaskInput(
            titleProvided: array_key_exists('title', $payload),
            title: $this->title($payload),
            descriptionProvided: array_key_exists('description', $payload),
            description: $this->description($payload),
            statusProvided: array_key_exists('status', $payload),
            status: $this->status($payload),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function title(array $payload): ?string
    {
        if (!array_key_exists('title', $payload)) {
            return null;
        }

        if (!is_string($payload['title'])) {
            throw new InvalidArgumentException('Title is required.');
        }

        return $payload['title'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function description(array $payload): ?string
    {
        if (!array_key_exists('description', $payload)) {
            return null;
        }

        if ($payload['description'] !== null && !is_string($payload['description'])) {
            throw new InvalidArgumentException('Description must be a string or null.');
        }

        return $payload['description'];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function status(array $payload): ?TaskStatus
    {
        if (!array_key_exists('status', $payload)) {
            return null;
        }

        if (!is_string($payload['status'])) {
            throw new InvalidArgumentException('Status must be a string.');
        }

        return $this->statusParser->parse($payload['status']);
    }

    /**
     * @param array<string, mixed> $payload
     * @param string[] $allowedFields
     */
    private function assertAllowedFields(array $payload, array $allowedFields): void
    {
        $unknownFields = array_diff(array_keys($payload), $allowedFields);

        if ($unknownFields !== []) {
            throw new InvalidArgumentException('Unknown fields: ' . implode(', ', $unknownFields) . '.');
        }
    }
}
