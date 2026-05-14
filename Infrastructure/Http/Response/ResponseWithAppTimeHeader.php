<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

final class ResponseWithAppTimeHeader extends Response
{
    public function __construct(
        private readonly Response $response,
    ) {
    }

    public function send(): void
    {
        header('X-App-Time: ' . $this->appTime());

        $this->response->send();
    }

    private function appTime(): string
    {
        $startTime = $GLOBALS['startTime'] ?? microtime(true);

        return round((microtime(true) - $startTime) * 1000, 1) . 'ms';
    }
}
