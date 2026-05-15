<?php

declare(strict_types=1);

namespace Application\UseCase\Webhook;

use Application\Contracts\ClockInterface;
use Application\Contracts\TimeFormatterInterface;
use Application\Contracts\WebhookDeliveryRepositoryInterface;
use Domain\Task\Task;

final readonly class SendTaskDoneWebhookUseCase
{
    public function __construct(
        private WebhookDeliveryRepositoryInterface $webhookDeliveries,
        private DeliverWebhookUseCase $deliverWebhook,
        private ClockInterface $clock,
        private TimeFormatterInterface $timeFormatter,
    ) {
    }

    public function execute(Task $task): void
    {
        $occurredAt = $this->clock->now();
        $deliveryId = $this->webhookDeliveries->enqueue(
            payload: [
                'taskId' => $task->id->value,
                'status' => $task->status->value,
                'occurredAt' => $this->timeFormatter->formatForApi($occurredAt),
            ],
            nextAttemptAt: $occurredAt,
        );

        $delivery = $this->webhookDeliveries->findById($deliveryId);

        if ($delivery !== null) {
            $this->deliverWebhook->execute($delivery);
        }
    }
}
