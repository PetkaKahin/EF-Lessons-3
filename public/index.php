<?php

declare(strict_types=1);

use Infrastructure\Kernel\Application;

$GLOBALS['startTime'] = microtime(true);

require_once dirname(__DIR__) . '/vendor/autoload.php';

new Application()->run();
