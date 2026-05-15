<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

use JsonException;

final class JsonResponse extends Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private mixed $data,
        private readonly int $statusCode = 200,
        private readonly array $headers = [],
    ) {
    }

    public function send(): void
    {
        try {
            $body = json_encode($this->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            $body = '{"error":"Unable to encode JSON response"}';
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo $body;
            return;
        }

        http_response_code($this->statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $body;
    }
}
