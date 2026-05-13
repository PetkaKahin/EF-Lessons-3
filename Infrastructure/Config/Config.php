<?php

declare(strict_types=1);

namespace Infrastructure\Config;

use RuntimeException;

final readonly class Config
{
    /**
     * @var array<mixed>
     */
    private array $values;

    public function __construct(string $configPath)
    {
        if (!is_file($configPath)) {
            throw new RuntimeException('config.php file is required.');
        }

        $values = require $configPath;

        if (!is_array($values)) {
            throw new RuntimeException('config.php must return an array.');
        }

        $this->values = $values;
    }

    public function get(string $key): mixed
    {
        // делаю так специально, потому, что есть плохой опыт с laravel,
        // где отсутствие ключей замалчивалось, хотя они были важны
        // Если надо будет без проверки, то просто tryGet сделать, но сейчас он не нужен
        if (!array_key_exists($key, $this->values)) {
            throw new RuntimeException('Config key is required: ' . $key);
        }

        return $this->values[$key];
    }
}
