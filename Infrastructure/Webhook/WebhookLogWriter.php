<?php

declare(strict_types=1);

namespace Infrastructure\Webhook;

use Infrastructure\Config\Config;
use RuntimeException;

final readonly class WebhookLogWriter
{
    private string $logPath;

    public function __construct(Config $config)
    {
        $this->logPath = (string) $config->get('WEBHOOK_LOG_PATH');
    }

    public function append(string $payload): void
    {
        $directory = dirname($this->logPath);

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create webhook log directory.');
        }

        $bytesWritten = file_put_contents($this->logPath, trim($payload) . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($bytesWritten === false) {
            throw new RuntimeException('Cannot write webhook log.');
        }
    }
}
