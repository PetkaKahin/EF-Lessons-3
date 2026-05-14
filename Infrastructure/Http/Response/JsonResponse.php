<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

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
        http_response_code($this->statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($this->data, JSON_UNESCAPED_SLASHES);
    }
}
