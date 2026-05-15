<?php

declare(strict_types=1);

namespace Application\Contracts;

use Application\Webhook\WebhookDelivery;
use Domain\Shared\Time\DateTimeValue;

interface WebhookDeliveryRepositoryInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function enqueue(array $payload, DateTimeValue $nextAttemptAt): int;

    public function findById(int $id): ?WebhookDelivery;

    /**
     * @return WebhookDelivery[]
     */
    public function findDue(DateTimeValue $now, int $limit): array;

    public function markSuccessfulAttempt(int $id): void;

    public function markFailedAttempt(int $id, string $error, DateTimeValue $nextAttemptAt): void;
}
