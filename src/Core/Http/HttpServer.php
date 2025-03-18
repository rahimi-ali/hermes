<?php

declare(strict_types=1);

namespace Hermes\Hermes\Core\Http;

use Psr\Http\Server\RequestHandlerInterface;

interface HttpServer
{
    public function start(): void;

    /**
     * @param callable(mixed $server, int $workerId): void $callback
     */
    public function onWorkerStart(callable $callback): static;

    /**
     * @param callable(mixed $server, int $workerId): void $callback
     */
    public function onWorkerShutdown(callable $callback): static;

    public function onRequest(RequestHandlerInterface $handler): static;
}
