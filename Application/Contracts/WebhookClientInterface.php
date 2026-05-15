<?php

declare(strict_types=1);

namespace Application\Contracts;

use Application\Webhook\WebhookDeliveryResult;

interface WebhookClientInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function post(array $payload): WebhookDeliveryResult;
}
