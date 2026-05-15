<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Webhook;

use Application\Contracts\ClockInterface;
use Application\Contracts\TimeFormatterInterface;
use Application\Contracts\WebhookDeliveryRepositoryInterface;
use Application\Webhook\WebhookDelivery;
use Domain\Shared\Time\DateTimeValue;
use Infrastructure\Database\PdoConnection;
use JsonException;
use PDO;
use RuntimeException;

final readonly class SQLiteWebhookDeliveryRepository implements WebhookDeliveryRepositoryInterface
{
    public function __construct(
        private PdoConnection $pdoConnection,
        private ClockInterface $clock,
        private TimeFormatterInterface $timeFormatter,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws JsonException
     */
    public function enqueue(array $payload, DateTimeValue $nextAttemptAt): int
    {
        $now = $this->timeFormatter->formatForDatabase($this->clock->now());

        $sql = <<<SQL
            INSERT INTO webhook_deliveries (
                payload,
                attempts,
                max_attempts,
                status,
                next_attempt_at,
                created_at,
                updated_at
            )
            VALUES (
                :payload,
                0,
                3,
                'pending',
                :next_attempt_at,
                :created_at,
                :updated_at
            )
            SQL;

        $statement = $this->pdo()->prepare($sql);
        $statement->execute([
            'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'next_attempt_at' => $this->timeFormatter->formatForDatabase($nextAttemptAt),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function findById(int $id): ?WebhookDelivery
    {
        $statement = $this->pdo()->prepare(<<<SQL
            SELECT id, payload, attempts, max_attempts, status
            FROM webhook_deliveries
            WHERE id = :id
            SQL);
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : $this->map($row);
    }

    public function findDue(DateTimeValue $now, int $limit): array
    {
        $statement = $this->pdo()->prepare(<<<SQL
            SELECT id, payload, attempts, max_attempts, status
            FROM webhook_deliveries
            WHERE status = 'pending'
                AND attempts < max_attempts
                AND next_attempt_at <= :now
            ORDER BY id ASC
            LIMIT :limit
            SQL);
        $statement->bindValue(':now', $this->timeFormatter->formatForDatabase($now));
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(
            fn(array $row): WebhookDelivery => $this->map($row),
            $statement->fetchAll(),
        );
    }

    public function markSuccessfulAttempt(int $id): void
    {
        $statement = $this->pdo()->prepare(<<<SQL
            UPDATE webhook_deliveries
            SET attempts = attempts + 1,
                status = 'sent',
                last_error = NULL,
                updated_at = :updated_at
            WHERE id = :id
                AND status = 'pending'
            SQL);
        $statement->execute([
            'id' => $id,
            'updated_at' => $this->timeFormatter->formatForDatabase($this->clock->now()),
        ]);
    }

    public function markFailedAttempt(int $id, string $error, DateTimeValue $nextAttemptAt): void
    {
        $statement = $this->pdo()->prepare(<<<SQL
            UPDATE webhook_deliveries
            SET attempts = attempts + 1,
                status = CASE
                    WHEN attempts + 1 >= max_attempts THEN 'failed'
                    ELSE 'pending'
                END,
                last_error = :last_error,
                next_attempt_at = :next_attempt_at,
                updated_at = :updated_at
            WHERE id = :id
                AND status = 'pending'
            SQL);
        $statement->execute([
            'id' => $id,
            'last_error' => substr($error, 0, 1000),
            'next_attempt_at' => $this->timeFormatter->formatForDatabase($nextAttemptAt),
            'updated_at' => $this->timeFormatter->formatForDatabase($this->clock->now()),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function map(array $row): WebhookDelivery
    {
        $payload = json_decode((string) $row['payload'], true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($payload)) {
            throw new RuntimeException('Webhook payload must be a JSON object.');
        }

        return new WebhookDelivery(
            id: (int) $row['id'],
            payload: $payload,
            attempts: (int) $row['attempts'],
            maxAttempts: (int) $row['max_attempts'],
            status: (string) $row['status'],
        );
    }

    private function pdo(): PDO
    {
        return $this->pdoConnection->get();
    }
}
