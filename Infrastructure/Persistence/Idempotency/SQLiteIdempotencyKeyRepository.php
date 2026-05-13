<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Idempotency;

use Application\Contracts\IdempotencyKeyRepositoryInterface;
use Application\Idempotency\IdempotencyRecord;
use Application\Idempotency\IdempotentResponse;
use Infrastructure\Database\PdoConnection;
use JsonException;
use PDO;
use RuntimeException;

final readonly class SQLiteIdempotencyKeyRepository implements IdempotencyKeyRepositoryInterface
{
    public function __construct(
        private PdoConnection $pdoConnection,
    ) {
    }

    public function tryReserve(string $key, string $requestHash): bool
    {
        // PRIMARY KEY по idempotency_key не даст двум запросам занять один ключ
        $statement = $this->pdo()->prepare(<<<SQL
            INSERT OR IGNORE INTO idempotency_keys (idempotency_key, request_hash)
            VALUES (:idempotency_key, :request_hash)
            SQL);

        $statement->execute([
            'idempotency_key' => $key,
            'request_hash' => $requestHash,
        ]);

        return $statement->rowCount() === 1;
    }

    public function findByKey(string $key): ?IdempotencyRecord
    {
        $statement = $this->pdo()->prepare(<<<SQL
            SELECT idempotency_key, request_hash, response_body
            FROM idempotency_keys
            WHERE idempotency_key = :idempotency_key
            SQL);

        $statement->execute(['idempotency_key' => $key]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return new IdempotencyRecord(
            key: $row['idempotency_key'],
            requestHash: $row['request_hash'],
            response: $this->responseFromRow($row),
        );
    }

    public function complete(string $key, IdempotentResponse $response): void
    {
        $statement = $this->pdo()->prepare(<<<SQL
            UPDATE idempotency_keys
            SET response_body = :response_body
            WHERE idempotency_key = :idempotency_key
            SQL);

        $statement->execute([
            'idempotency_key' => $key,
            'response_body' => $this->encodeJson($response->data),
        ]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Unable to complete idempotency key.');
        }
    }

    /**
     * @param array{response_body: string|null} $row
     */
    private function responseFromRow(array $row): ?IdempotentResponse
    {
        // null - ключ уже занят, но первый запрос еще не сохранил ответ
        if ($row['response_body'] === null) {
            return null;
        }

        return new IdempotentResponse(
            data: $this->decodeJsonObject($row['response_body']),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function encodeJson(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode idempotent response.', previous: $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $json): array
    {
        try {
            $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to decode idempotent response.', previous: $exception);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Stored idempotent response must be a JSON object.');
        }

        return $data;
    }

    private function pdo(): PDO
    {
        return $this->pdoConnection->get();
    }
}
