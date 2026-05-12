<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

final readonly class Request
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $body,
        private array $headers,
        private array $queryParams = [],
        private array $pathParams = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $body = file_get_contents('php://input') ?: '';

        return new self(
            method: $method,
            path: $path,
            body: $body,
            headers: self::headersFromServer($_SERVER),
            queryParams: $_GET,
        );
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function query(string $name): ?string
    {
        $value = $this->queryParams[$name] ?? null;

        return is_string($value) ? $value : null;
    }

    public function pathParam(string $name): ?string
    {
        return $this->pathParams[$name] ?? null;
    }

    /**
     * @param array<string, string> $pathParams
     */
    public function withPathParams(array $pathParams): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            body: $this->body,
            headers: $this->headers,
            queryParams: $this->queryParams,
            pathParams: $pathParams,
        );
    }

    /**
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
