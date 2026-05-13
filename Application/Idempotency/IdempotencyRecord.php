<?php

declare(strict_types=1);

namespace Application\Idempotency;

/**
 * Запись из таблицы idempotency_keys
 */
final readonly class IdempotencyRecord
{
    public function __construct(
        public string $key,
        public string $requestHash,
        public ?IdempotentResponse $response,
    ) {
    }

    public function isCompleted(): bool
    {
        // Пока response равен null, ключ занят, но ответ еще не сохранен
        return $this->response !== null;
    }
}
