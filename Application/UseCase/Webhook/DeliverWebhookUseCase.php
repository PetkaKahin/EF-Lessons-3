<?php

declare(strict_types=1);

namespace Application\UseCase\Webhook;

use Application\Contracts\ClockInterface;
use Application\Contracts\WebhookClientInterface;
use Application\Contracts\WebhookDeliveryRepositoryInterface;
use Application\Webhook\WebhookDelivery;
use Domain\Shared\Time\DateTimeValue;

final readonly class DeliverWebhookUseCase
{
    private const int RETRY_DELAY_SECONDS = 5;

    public function __construct(
        private WebhookClientInterface $webhookClient,
        private WebhookDeliveryRepositoryInterface $webhookDeliveries,
        private ClockInterface $clock,
    ) {
    }

    public function execute(WebhookDelivery $delivery): void
    {
        if ($delivery->status !== 'pending' || $delivery->attempts >= $delivery->maxAttempts) {
            return;
        }

        $result = $this->webhookClient->post($delivery->payload);

        if ($result->successful) {
            $this->webhookDeliveries->markSuccessfulAttempt($delivery->id);
            return;
        }

        $this->webhookDeliveries->markFailedAttempt(
            id: $delivery->id,
            error: $result->error ?? 'Webhook request failed.',
            nextAttemptAt: $this->retryAt(),
        );
    }

    private function retryAt(): DateTimeValue
    {
        return $this->clock->now()->plusSeconds(self::RETRY_DELAY_SECONDS);
    }
}
