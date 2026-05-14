<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

final class ResponseWithHeaders extends Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly Response $response,
        private readonly array $headers,
    ) {
    }

    public function send(): void
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        $this->response->send();
    }
}
