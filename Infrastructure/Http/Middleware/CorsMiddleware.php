<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware;

use Infrastructure\Config\Config;
use Infrastructure\Http\Middleware\Contracts\MiddlewareInterface;
use Infrastructure\Http\Middleware\Contracts\ResponseMiddlewareInterface;
use Infrastructure\Http\Response\NoContentResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Http\Response\ResponseWithHeaders;
use Infrastructure\Kernel\Enums\HttpHeaders;
use Infrastructure\Kernel\Enums\HttpMethods;
use Infrastructure\Kernel\Request;
use RuntimeException;

final readonly class CorsMiddleware implements MiddlewareInterface, ResponseMiddlewareInterface
{
    private const string APP_URL_CONFIG_KEY = 'APP_URL';

    private string $allowedOrigin;

    public function __construct(Config $config)
    {
        $allowedOrigin = $config->get(self::APP_URL_CONFIG_KEY);

        if (!is_string($allowedOrigin) || trim($allowedOrigin) === '') {
            throw new RuntimeException(self::APP_URL_CONFIG_KEY . ' config value must be a non-empty string.');
        }

        $this->allowedOrigin = $allowedOrigin;
    }

    /**
     * @param callable(Request): Response $next
     */
    public function handle(Request $request, callable $next): Response
    {
        if ($request->method === HttpMethods::OPTIONS->value) {
            return new NoContentResponse();
        }

        return $next($request);
    }

    public function processResponse(Request $request, Response $response): Response
    {
        return new ResponseWithHeaders($response, [
            'Access-Control-Allow-Origin' => $this->allowedOrigin,
            'Access-Control-Allow-Methods' => $this->allowedMethods(),
            'Access-Control-Allow-Headers' => $this->allowedHeaders(),
        ]);
    }

    private function allowedMethods(): string
    {
        return implode(', ', [
            HttpMethods::GET->value,
            HttpMethods::POST->value,
            HttpMethods::PATCH->value,
            HttpMethods::DELETE->value,
            HttpMethods::OPTIONS->value,
        ]);
    }

    private function allowedHeaders(): string
    {
        return implode(', ', [
            HttpHeaders::CONTENT_TYPE->value,
            HttpHeaders::AUTHORIZATION->value,
            HttpHeaders::IDEMPOTENCY_KEY->value,
        ]);
    }
}
