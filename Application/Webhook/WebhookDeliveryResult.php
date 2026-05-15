<?php

declare(strict_types=1);

namespace Application\Webhook;

final readonly class WebhookDeliveryResult
{
    private function __construct(
        public bool $successful,
        public ?int $statusCode,
        public ?string $error,
    ) {
    }

    public static function success(int $statusCode): self
    {
        return new self(true, $statusCode, null);
    }

    public static function failure(?int $statusCode, string $error): self
    {
        return new self(false, $statusCode, $error);
    }
}
