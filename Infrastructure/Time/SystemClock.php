<?php

declare(strict_types=1);

namespace Infrastructure\Time;

use Application\Contracts\ClockInterface;
use Domain\Shared\Time\DateTimeValue;

final readonly class SystemClock implements ClockInterface
{
    public function now(): DateTimeValue
    {
        return DateTimeValue::fromTimestamp(microtime(true));
    }
}
