<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

use Application\Contracts\ClockInterface;
use Application\Contracts\TimeFormatterInterface;
use Domain\Shared\Time\DateTimeValue;

final class ResponseWithAppTimeHeader extends Response
{
    public function __construct(
        private readonly Response $response,
        private readonly ClockInterface $clock,
        private readonly TimeFormatterInterface $timeFormatter,
        private readonly DateTimeValue $startedAt,
    ) {
    }

    public function send(): void
    {
        header('X-App-Time: ' . $this->appTime());

        $this->response->send();
    }

    private function appTime(): string
    {
        return $this->timeFormatter->formatDuration(
            $this->startedAt->durationUntil($this->clock->now()),
        );
    }
}
