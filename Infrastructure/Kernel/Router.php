<?php

declare(strict_types=1);

namespace Infrastructure\Kernel;

use Infrastructure\Http\Response\JsonResponse;
use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Enums\HttpMethods;

/**
 * Здесь хранятся все маршруты, которые зарегистрировали в routes.php
 */
final class Router
{
    /**
     * Обычные маршруты без параметров
     *
     * @var array<string, callable(Request): Response>
     */
    private array $routes = [];

    /**
     * Маршруты с параметрами в пути, например /tasks/{id}
     *
     * @var array<string, array<string, mixed>>
     */
    private array $routesWithParams = [];

    /**
     * @param callable(Request): Response $handler
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute(HttpMethods::GET, $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute(HttpMethods::POST, $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function patch(string $path, callable $handler): void
    {
        $this->addRoute(HttpMethods::PATCH, $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute(HttpMethods::DELETE, $path, $handler);
    }

    /**
     * Ищет подходящий маршрут для текущего запроса и вызывает его handler
     */
    public function dispatch(Request $request): Response
    {
        $routeKey = $this->routeKey($request->method, $request->path);

        if (isset($this->routes[$routeKey])) {
            return $this->routes[$routeKey]($request);
        }

        $routeWithParams = $this->matchRequest($request);

        if ($routeWithParams !== null) {
            return $routeWithParams['handler'](
                $request->withPathParams($routeWithParams['pathParams']),
            );
        }

        return new JsonResponse(['error' => 'Not found'], 404);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function addRoute(HttpMethods $method, string $path, callable $handler): void
    {
        if (str_contains($path, '{')) {
            $route = &$this->routesWithParams[$method->value];

            foreach ($this->pathParts($path) as $pathPart) {
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)}$/', $pathPart, $matches) === 1) {
                    $route['paramName'] = $matches[1];
                    $route['param'] ??= [];
                    $route = &$route['param'];
                    continue;
                }

                $route['static'][$pathPart] ??= [];
                $route = &$route['static'][$pathPart];
            }

            $route['handler'] = $handler;

            return;
        }

        $this->routes[$this->routeKey($method->value, $path)] = $handler;
    }

    /**
     * Делает ключ для быстрого поиска обычного маршрута
     */
    private function routeKey(string $method, string $path): string
    {
        return $method . ' ' . $path;
    }

    /**
     * Проверяет, подходит ли путь запроса под путь маршрута
     *
     * @return array{
     *     handler: callable(Request): Response,
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
            if (isset($route['static'][$pathPart])) {
                $route = $route['static'][$pathPart];
                continue;
            }

            if (isset($route['param'], $route['paramName'])) {
                $pathParams[$route['paramName']] = $pathPart;
                $route = $route['param'];
                continue;
            }

            return null;
        }

        if (!isset($route['handler'])) {
            return null;
        }

        return [
            'handler' => $route['handler'],
            'pathParams' => $pathParams,
        ];
    }

    /**
     * Разделяет роут на части через `/`
     *
     * @return string[]
     */
    private function pathParts(string $path): array
    {
        $path = trim($path, '/');

        return $path === '' ? [] : explode('/', $path);
    }
}
