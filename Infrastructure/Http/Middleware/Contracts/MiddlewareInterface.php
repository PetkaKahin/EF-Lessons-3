<?php

declare(strict_types=1);

namespace Infrastructure\Http\Middleware\Contracts;

use Infrastructure\Http\Response\Response;
use Infrastructure\Kernel\Request;

interface MiddlewareInterface
{
    /**
     * @param callable(Request): Response $next
     */
    public function handle(Request $request, callable $next): Response;
}
