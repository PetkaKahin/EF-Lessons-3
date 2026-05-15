<?php

declare(strict_types=1);

namespace Domain\Shared\Time;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;

final readonly class DateTimeValue
{
    private DateTimeImmutable $value;

    private function __construct(DateTimeInterface $value)
    {
        $this->value = DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new DateTimeZone('UTC'));
    }

    public static function fromDateTime(DateTimeInterface $value): self
    {
        return new self($value);
    }

    public static function fromTimestamp(float $timestamp): self
    {
        $dateTime = DateTimeImmutable::createFromFormat(
            'U.u',
            sprintf('%.6F', $timestamp),
            new DateTimeZone('UTC'),
        );

        if ($dateTime === false) {
            throw new RuntimeException('Unable to create date time from timestamp.');
        }

        return new self($dateTime);
    }

    public function plusSeconds(int $seconds): self
    {
        $modifier = ($seconds >= 0 ? '+' : '') . $seconds . ' seconds';
        $dateTime = $this->value->modify($modifier);

        if ($dateTime === false) {
            throw new RuntimeException('Unable to modify date time.');
        }

        return new self($dateTime);
    }

    public function durationUntil(self $end): Duration
    {
        return Duration::between($this, $end);
    }

    public function format(string $format, DateTimeZone $timezone): string
    {
        return $this->value
            ->setTimezone($timezone)
            ->format($format);
    }

    public function timestamp(): float
    {
        return (float) $this->value->format('U.u');
    }
}
