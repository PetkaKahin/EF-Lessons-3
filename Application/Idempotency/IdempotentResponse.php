<?php

declare(strict_types=1);

namespace Application\Idempotency;

/**
 * Сохраненный ответ первого запроса
 */
final readonly class IdempotentResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data,
    ) {
    }
}
