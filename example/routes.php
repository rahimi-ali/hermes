<?php

declare(strict_types=1);

require_once __DIR__ . '/app/PingHandler.php';

use App\PingHandler;
use HermesFramework\Hermes\Default\Http\RouteRegistrar;

return function (RouteRegistrar $router): void {
    $router->get('ping', [PingHandler::class, 'ping'])
        ->withName('ping');
};
