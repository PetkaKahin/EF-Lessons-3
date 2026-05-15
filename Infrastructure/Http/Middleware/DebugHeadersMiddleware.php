<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware;

use Application\Contracts\ClockInterface;
use Application\Contracts\TimeFormatterInterface;
use Domain\Shared\Time\DateTimeValue;
use Infrastructure\Config\Config;
use Infrastructure\Http\Middleware\Contracts\MiddlewareInterface;
use Infrastructure\Http\Middleware\Contracts\ResponseMiddlewareInterface;
use Infrastructure\Http\Response\Response;
use Infrastructure\Http\Response\ResponseWithAppTimeHeader;
use Infrastructure\Kernel\Request;
use RuntimeException;

final readonly class DebugHeadersMiddleware implements MiddlewareInterface, ResponseMiddlewareInterface
{
    private const string DEBUG_CONFIG_KEY = 'DEBUG';

    private bool $debug;

    public function __construct(
        Config $config,
        private readonly ClockInterface $clock,
        private readonly TimeFormatterInterface $timeFormatter,
    ) {
        $debug = $config->get(self::DEBUG_CONFIG_KEY);

        if (!is_bool($debug)) {
            throw new RuntimeException(self::DEBUG_CONFIG_KEY . ' config value must be boolean.');
        }

        $this->debug = $debug;
    }

    /**
     * @param callable(Request): Response $next
     */
    public function handle(Request $request, callable $next): Response
    {
        return $next($request);
    }

    public function processResponse(Request $request, Response $response): Response
    {
        if (!$this->debug) {
            return $response;
        }

        return new ResponseWithAppTimeHeader(
            response: $response,
            clock: $this->clock,
            timeFormatter: $this->timeFormatter,
            startedAt: $this->requestStartedAt(),
        );
    }

    private function requestStartedAt(): DateTimeValue
    {
        $startedAt = $GLOBALS['requestStartedAt'] ?? null;

        return $startedAt instanceof DateTimeValue ? $startedAt : $this->clock->now();
    }
}
