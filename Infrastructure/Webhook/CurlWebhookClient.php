<?php

declare(strict_types=1);

namespace Infrastructure\Webhook;

use Application\Contracts\WebhookClientInterface;
use Application\Webhook\WebhookDeliveryResult;
use Infrastructure\Config\Config;
use JsonException;

final readonly class CurlWebhookClient implements WebhookClientInterface
{
    private string $url;

    public function __construct(Config $config)
    {
        $this->url = trim((string) $config->get('WEBHOOK_URL'));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws JsonException
     */
    public function post(array $payload): WebhookDeliveryResult
    {
        if ($this->url === '') {
            return WebhookDeliveryResult::failure(null, 'WEBHOOK_URL is empty.');
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $curl = curl_init($this->url);

        if ($curl === false) {
            return WebhookDeliveryResult::failure(null, 'Cannot initialize webhook request.');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
        ]);

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            return WebhookDeliveryResult::failure($statusCode === 0 ? null : $statusCode, $error);
        }

        curl_close($curl);

        if ($statusCode >= 200 && $statusCode < 300) {
            return WebhookDeliveryResult::success($statusCode);
        }

        return WebhookDeliveryResult::failure($statusCode, 'Unexpected webhook status code: ' . $statusCode);
    }
}
