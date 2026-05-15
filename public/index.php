<?php

declare(strict_types=1);

use Infrastructure\Kernel\Application;
use Domain\Shared\Time\DateTimeValue;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$GLOBALS['requestStartedAt'] = DateTimeValue::fromTimestamp(
    (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
);

new Application()->run();
