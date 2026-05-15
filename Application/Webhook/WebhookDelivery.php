<?php

declare(strict_types=1);

namespace Application\Webhook;

final readonly class WebhookDelivery
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public int $id,
        public array $payload,
        public int $attempts,
        public int $maxAttempts,
        public string $status,
    ) {
    }
}
