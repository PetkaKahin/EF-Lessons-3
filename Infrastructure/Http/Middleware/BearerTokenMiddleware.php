<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware;

use Infrastructure\Config\Config;
use Infrastructure\Http\Middleware\Contracts\MiddlewareInterface;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class BearerTokenMiddleware implements MiddlewareInterface
{
    private const string API_TOKEN_CONFIG_KEY = 'API_TOKEN';

    public function __construct(
        private Config $config,
    ) {
    }

    /**
     * @param callable(Request): Response $next
     */
    public function handle(Request $request, callable $next): Response
    {
        $authorization = $request->header('Authorization');

        if ($authorization === null || $authorization === '') {
            return new JsonResponse(
                ['error' => 'Authorization header is required'],
                401,
                ['WWW-Authenticate' => 'Bearer'],
            );
        }

        if ($authorization !== 'Bearer ' . $this->config->get(self::API_TOKEN_CONFIG_KEY)) {
            return new JsonResponse(['error' => 'Invalid bearer token'], 403);
        }

        return $next($request);
    }
}
