<?php

declare(strict_types=1);

namespace Infrastructure\Http\Response;

abstract class Response
{
    abstract public function send(): void;
}
