<?php

declare(strict_types=1);

namespace Application\Contracts;

use Application\Idempotency\IdempotencyRecord;
use Application\Idempotency\IdempotentResponse;

interface IdempotencyKeyRepositoryInterface
{
    /**
     * Пытается занять ключ под текущий запрос
     * true означает, что ключ новый и операцию можно выполнять
     */
    public function tryReserve(string $key, string $requestHash): bool;

    public function findByKey(string $key): ?IdempotencyRecord;

    /**
     * Сохраняет финальный ответ после успешного выполнения операции
     */
    public function complete(string $key, IdempotentResponse $response): void;
}
