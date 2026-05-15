<?php

declare(strict_types=1);

namespace Infrastructure\Time;

use Application\Contracts\TimeFormatterInterface;
use DateTimeImmutable;
use DateTimeZone;
use Domain\Shared\Time\DateTimeValue;
use Domain\Shared\Time\Duration;
use Infrastructure\Config\Config;
use RuntimeException;

final readonly class ConfigurableTimeFormatter implements TimeFormatterInterface
{
    private DateTimeZone $timezone;
    private string $databaseFormat;
    private string $apiFormat;
    private string $logFormat;
    private int $durationPrecision;

    public function __construct(Config $config)
    {
        $this->timezone = new DateTimeZone($this->stringValue($config, 'TIMEZONE'));
        $this->databaseFormat = $this->stringValue($config, 'TIME_DATABASE_FORMAT');
        $this->apiFormat = $this->stringValue($config, 'TIME_API_FORMAT');
        $this->logFormat = $this->stringValue($config, 'TIME_LOG_FORMAT');
        $this->durationPrecision = $this->intValue($config, 'TIME_DURATION_PRECISION');
    }

    public function formatForDatabase(DateTimeValue $dateTime): string
    {
        return $dateTime->format($this->databaseFormat, $this->timezone);
    }

    public function parseFromDatabase(string $value): DateTimeValue
    {
        return $this->parse($value, $this->databaseFormat);
    }

    public function formatForApi(DateTimeValue $dateTime): string
    {
        return $dateTime->format($this->apiFormat, $this->timezone);
    }

    public function parseFromApi(string $value): DateTimeValue
    {
        return $this->parse($value, $this->apiFormat);
    }

    public function formatForLog(DateTimeValue $dateTime): string
    {
        return $dateTime->format($this->logFormat, $this->timezone);
    }

    public function formatDuration(Duration $duration): string
    {
        return $duration->formatMilliseconds($this->durationPrecision);
    }

    private function parse(string $value, string $format): DateTimeValue
    {
        $dateTime = DateTimeImmutable::createFromFormat('!' . $format, $value, $this->timezone);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $dateTime === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            throw new RuntimeException('Invalid date time value: ' . $value);
        }

        return DateTimeValue::fromDateTime($dateTime);
    }

    private function stringValue(Config $config, string $key): string
    {
        $value = $config->get($key);

        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException($key . ' config value must be a non-empty string.');
        }

        return $value;
    }

    private function intValue(Config $config, string $key): int
    {
        $value = $config->get($key);

        if (!is_int($value) || $value < 0) {
            throw new RuntimeException($key . ' config value must be a non-negative integer.');
        }

        return $value;
    }
}
