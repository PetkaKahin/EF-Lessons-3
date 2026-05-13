<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware;

use Infrastructure\Config\Config;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

final readonly class BearerTokenMiddleware
{
    public function __construct(
        private Config $config,
    ) {
    }

    /**
     * @param callable(Request): Response $next
     * @return callable(Request): Response
     */
    public function protect(callable $next): callable
    {
        return function (Request $request) use ($next): Response {
            $authorization = $request->header('Authorization');

            if ($authorization === null || $authorization === '') {
                return new JsonResponse(
                    ['error' => 'Authorization header is required'],
                    401,
                    ['WWW-Authenticate' => 'Bearer'],
                );
            }

            if ($authorization !== 'Bearer ' . $this->config->get('API_TOKEN')) {
                return new JsonResponse(['error' => 'Invalid bearer token'], 403);
            }

            return $next($request);
        };
    }
}
