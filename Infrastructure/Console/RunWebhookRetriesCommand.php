<?php

declare(strict_types=1);

namespace Infrastructure\Console;

use Application\UseCase\Webhook\RetryDueWebhooksUseCase;

final readonly class RunWebhookRetriesCommand
{
    public function __construct(
        private RetryDueWebhooksUseCase $retryDueWebhooks,
    ) {
    }

    public function execute(int $limit = 20): int
    {
        return $this->retryDueWebhooks->execute($limit);
    }
}
