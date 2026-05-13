<?php

declare(strict_types=1);

namespace Application\UseCase\Idempotency;

use Application\Contracts\IdempotencyKeyRepositoryInterface;
use Application\Contracts\TransactionManagerInterface;
use Application\Idempotency\IdempotentResponse;
use RuntimeException;

final readonly class RunIdempotentOperationUseCase
{
    public function __construct(
        private IdempotencyKeyRepositoryInterface $idempotencyKeys,
        private TransactionManagerInterface $transactions,
    ) {
    }

    /**
     * @param callable(): IdempotentResponse $operation
     */
    public function execute(string $key, string $requestHash, callable $operation): IdempotentResponse
    {
        // Вся проверка ключа, создание задачи и сохранение ответа идут в одной транзакции
        return $this->transactions->transactional(function () use ($key, $requestHash, $operation): IdempotentResponse {
            // Если ключ удалось зарезервировать, этот запрос считается первым
            if ($this->idempotencyKeys->tryReserve($key, $requestHash)) {
                $response = $operation();
                $this->idempotencyKeys->complete($key, $response);

                return $response;
            }

            // Если ключ уже был, читаем сохраненную запись и решаем, что вернуть клиенту
            $record = $this->idempotencyKeys->findByKey($key);

            if ($record === null) {
                throw new RuntimeException('Idempotency key was not found after reservation failed.');
            }

            // Тот же ключ с другим запросом запрещен
            if (!hash_equals($record->requestHash, $requestHash)) {
                throw IdempotencyConflictException::forDifferentRequest();
            }

            // На случай когда ключ есть, но первый запрос еще не успел сохранить ответ
            if (!$record->isCompleted()) {
                throw new RuntimeException('Idempotent operation is still processing.');
            }

            // Повтор такого же запроса получает ровно тот ответ, который сохранил первый запрос
            return $record->response;
        });
    }
}
