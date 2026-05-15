<?php

declare(strict_types=1);

namespace Application\UseCase\Webhook;

use Application\Contracts\ClockInterface;
use Application\Contracts\WebhookDeliveryRepositoryInterface;

final readonly class RetryDueWebhooksUseCase
{
    public function __construct(
        private WebhookDeliveryRepositoryInterface $webhookDeliveries,
        private DeliverWebhookUseCase $deliverWebhook,
        private ClockInterface $clock,
    ) {
    }

    public function execute(int $limit = 20): int
    {
        $processed = 0;

        foreach ($this->webhookDeliveries->findDue($this->clock->now(), $limit) as $delivery) {
            $this->deliverWebhook->execute($delivery);
            $processed++;
        }

        return $processed;
    }
}
