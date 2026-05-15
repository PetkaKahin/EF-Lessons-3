<?php

declare(strict_types=1);

namespace Infrastructure\Http\RequestMapper\Task;

use Application\DTO\Task\CreateTaskInput;
use Domain\Task\TaskStatus;
use Infrastructure\Http\RequestMapper\JsonObjectFieldsValidator;
use Infrastructure\Http\RequestMapper\JsonObjectBodyParser;
use Infrastructure\Kernel\Request;
use InvalidArgumentException;
use JsonException;

final readonly class CreateTaskRequestMapper
{
    public function __construct(
        private JsonObjectBodyParser $bodyParser,
        private JsonObjectFieldsValidator $fieldsValidator,
        private TaskStatusParser $statusParser,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function map(Request $request): CreateTaskInput
    {
        $payload = $this->bodyParser->parse($request);
        $this->fieldsValidator->assertAllowedFields($payload, ['title', 'description', 'status']);

        return new CreateTaskInput(
            title: $this->title($payload),
            description: $this->description($payload),
            status: $this->status($payload),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function title(array $payload): string
    {
        $title = $payload['title'] ?? null;

        if (!is_string($title)) {
            throw new InvalidArgumentException('Title is required.');
        }

        return $title;
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
    private function status(array $payload): TaskStatus
    {
        if (!array_key_exists('status', $payload)) {
            return TaskStatus::New;
        }

        if (!is_string($payload['status'])) {
            throw new InvalidArgumentException('Status must be a string.');
        }

        return $this->statusParser->parse($payload['status']);
    }

}
