<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

final class NoContentResponse extends Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private int $statusCode = 204,
        private array $headers = [],
    ) {
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        ini_set('default_mimetype', '');
        header_remove('Content-Type');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        header('X-App-Time: ' . $this->profilerTime());
    }

    private function profilerTime(): string
    {
        return round(
            (microtime(true) - $GLOBALS['startTime']) * 1000,
            1,
        ) . 'ms';
    }
}
