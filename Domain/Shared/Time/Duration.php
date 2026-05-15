<?php

declare(strict_types=1);

namespace Domain\Shared\Time;

final readonly class Duration
{
    private function __construct(
        private float $seconds,
    ) {
    }

    public static function between(DateTimeValue $start, DateTimeValue $end): self
    {
        return new self(max(0, $end->timestamp() - $start->timestamp()));
    }

    public function milliseconds(int $precision = 1): float
    {
        return round($this->seconds * 1000, $precision);
    }

    public function formatMilliseconds(int $precision = 1): string
    {
        return $this->milliseconds($precision) . 'ms';
    }
}
