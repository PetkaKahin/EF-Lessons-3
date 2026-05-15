<?php

declare(strict_types=1);

namespace Application\Contracts;

use Domain\Shared\Time\DateTimeValue;

interface ClockInterface
{
    public function now(): DateTimeValue;
}
