<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\Http\Middleware\Contracts\MiddlewareInterface;
use Infrastructure\Http\Middleware\Contracts\ResponseMiddlewareInterface;
use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Enums\HttpMethods;

final class Router
{
    private const string KEY_HANDLER = 'handler';
    private const string KEY_MIDDLEWARE = 'middleware';
    private const string KEY_MIDDLEWARES = 'middlewares';
    private const string KEY_PARAM = 'param';
    private const string KEY_PARAM_NAME = 'paramName';
    private const string KEY_PATH_PARAMS = 'pathParams';
    private const string KEY_PATH_PREFIX = 'pathPrefix';
    private const string KEY_STATIC = 'static';

    /**
     * @var list<array{middleware: MiddlewareInterface, pathPrefix: string|null}>
     */
    private array $middlewares = [];

    /**
     * @var array<string, array{handler: callable(Request): Response, middlewares: list<MiddlewareInterface>}>
     */
    private array $routes = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $routesWithParams = [];

    public function middleware(MiddlewareInterface $middleware, ?string $pathPrefix = null): void
    {
        $this->middlewares[] = [
            self::KEY_MIDDLEWARE => $middleware,
            self::KEY_PATH_PREFIX => $pathPrefix,
        ];
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function get(string $path, callable $handler, MiddlewareInterface ...$middlewares): void
    {
        $this->addRoute(HttpMethods::GET, $path, $handler, ...$middlewares);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function post(string $path, callable $handler, MiddlewareInterface ...$middlewares): void
    {
        $this->addRoute(HttpMethods::POST, $path, $handler, ...$middlewares);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function patch(string $path, callable $handler, MiddlewareInterface ...$middlewares): void
    {
        $this->addRoute(HttpMethods::PATCH, $path, $handler, ...$middlewares);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function delete(string $path, callable $handler, MiddlewareInterface ...$middlewares): void
    {
        $this->addRoute(HttpMethods::DELETE, $path, $handler, ...$middlewares);
    }

    public function dispatch(Request $request): Response
    {
        $handler = fn(Request $request): Response => $this->dispatchRoute($request);

        return $this->runThroughMiddlewares($request, $handler, $this->middlewaresFor($request));
    }

    public function processResponse(Request $request, Response $response): Response
    {
        foreach (array_reverse($this->middlewaresFor($request)) as $middleware) {
            if (!$middleware instanceof ResponseMiddlewareInterface) {
                continue;
            }

            $response = $middleware->processResponse($request, $response);
        }

        return $response;
    }

    private function dispatchRoute(Request $request): Response
    {
        $routeKey = $this->routeKey($request->method, $request->path);

        if (isset($this->routes[$routeKey])) {
            return $this->runRoute($request, $this->routes[$routeKey]);
        }

        $routeWithParams = $this->matchRequest($request);

        if ($routeWithParams !== null) {
            return $this->runRoute(
                $request->withPathParams($routeWithParams[self::KEY_PATH_PARAMS]),
                $routeWithParams,
            );
        }

        return new JsonResponse(['error' => 'Not found'], 404);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function addRoute(
        HttpMethods $method,
        string $path,
        callable $handler,
        MiddlewareInterface ...$middlewares,
    ): void {
        if (str_contains($path, '{')) {
            $route = &$this->routesWithParams[$method->value];

            foreach ($this->pathParts($path) as $pathPart) {
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $pathPart, $matches) === 1) {
                    $route[self::KEY_PARAM_NAME] = $matches[1];
                    $route[self::KEY_PARAM] ??= [];
                    $route = &$route[self::KEY_PARAM];
                    continue;
                }

                $route[self::KEY_STATIC][$pathPart] ??= [];
                $route = &$route[self::KEY_STATIC][$pathPart];
            }

            $route[self::KEY_HANDLER] = $handler;
            $route[self::KEY_MIDDLEWARES] = $middlewares;

            return;
        }

        $this->routes[$this->routeKey($method->value, $path)] = [
            self::KEY_HANDLER => $handler,
            self::KEY_MIDDLEWARES => $middlewares,
        ];
    }

    /**
     * @param array{handler: callable(Request): Response, middlewares: list<MiddlewareInterface>} $route
     */
    private function runRoute(Request $request, array $route): Response
    {
        return $this->runThroughMiddlewares(
            $request,
            $route[self::KEY_HANDLER],
            $route[self::KEY_MIDDLEWARES],
        );
    }

    /**
     * @param callable(Request): Response $handler
     * @param list<MiddlewareInterface> $middlewares
     */
    private function runThroughMiddlewares(Request $request, callable $handler, array $middlewares): Response
    {
        foreach (array_reverse($middlewares) as $middleware) {
            $next = $handler;
            $handler = static fn(Request $request): Response => $middleware->handle($request, $next);
        }

        return $handler($request);
    }

    /**
     * @return list<MiddlewareInterface>
     */
    private function middlewaresFor(Request $request): array
    {
        $middlewares = [];

        foreach ($this->middlewares as $middleware) {
            $pathPrefix = $middleware[self::KEY_PATH_PREFIX];

            if ($pathPrefix !== null && !$this->pathMatchesPrefix($request->path, $pathPrefix)) {
                continue;
            }

            $middlewares[] = $middleware[self::KEY_MIDDLEWARE];
        }

        return $middlewares;
    }

    private function pathMatchesPrefix(string $path, string $prefix): bool
    {
        $prefix = '/' . trim($prefix, '/');

        if ($prefix === '/') {
            return true;
        }

        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }

    private function routeKey(string $method, string $path): string
    {
        return $method . ' ' . $path;
    }

    /**
     * @return array{
     *     handler: callable(Request): Response,
     *     middlewares: list<MiddlewareInterface>,
     *     pathParams: array<string, string>
     * }|null
     */
    private function matchRequest(Request $request): ?array
    {
        $route = $this->routesWithParams[$request->method] ?? null;

        if ($route === null) {
            return null;
        }

        $pathParams = [];

        foreach ($this->pathParts($request->path) as $pathPart) {
            if (isset($route[self::KEY_STATIC][$pathPart])) {
                $route = $route[self::KEY_STATIC][$pathPart];
                continue;
            }

            if (isset($route[self::KEY_PARAM], $route[self::KEY_PARAM_NAME])) {
                $pathParams[$route[self::KEY_PARAM_NAME]] = $pathPart;
                $route = $route[self::KEY_PARAM];
                continue;
            }

            return null;
        }

        if (!isset($route[self::KEY_HANDLER])) {
            return null;
        }

        return [
            self::KEY_HANDLER => $route[self::KEY_HANDLER],
            self::KEY_MIDDLEWARES => $route[self::KEY_MIDDLEWARES] ?? [],
            self::KEY_PATH_PARAMS => $pathParams,
        ];
    }

    /**
     * @return string[]
     */
    private function pathParts(string $path): array
    {
        $path = trim($path, '/');

        return $path === '' ? [] : explode('/', $path);
    }
}
