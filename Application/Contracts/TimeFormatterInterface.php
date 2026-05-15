<?php

declare(strict_types=1);

namespace Application\Contracts;

use Domain\Shared\Time\DateTimeValue;
use Domain\Shared\Time\Duration;

interface TimeFormatterInterface
{
    public function formatForDatabase(DateTimeValue $dateTime): string;

    public function parseFromDatabase(string $value): DateTimeValue;

    public function formatForApi(DateTimeValue $dateTime): string;

    public function parseFromApi(string $value): DateTimeValue;

    public function formatForLog(DateTimeValue $dateTime): string;

    public function formatDuration(Duration $duration): string;
}
