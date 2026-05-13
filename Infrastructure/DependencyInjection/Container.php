<?php

declare(strict_types=1);

namespace Infrastructure\DependencyInjection;

use Closure;
use RuntimeException;

final class Container
{
    /**
     * @var array<string, Closure(self): object>
     */
    private array $factories = [];

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * @param callable(self): object $factory
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->factories[$id] = Closure::fromCallable($factory);
        unset($this->instances[$id]);
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new RuntimeException('Service is not registered: ' . $id);
        }

        return $this->instances[$id] = $this->factories[$id]($this);
    }
}

