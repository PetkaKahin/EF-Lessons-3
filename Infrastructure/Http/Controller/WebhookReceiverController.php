<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controller;

use Infrastructure\Http\Response\NoContentResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;
use Infrastructure\Webhook\WebhookLogWriter;

final readonly class WebhookReceiverController
{
    public function __construct(
        private WebhookLogWriter $webhookLogWriter,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->webhookLogWriter->append($request->body);

        return new NoContentResponse();
    }
}
