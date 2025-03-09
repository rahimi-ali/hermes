<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    /**
     * @param callable(ServerRequestInterface $request, ResponseInterface $response): void $callback
     * @return $this
     */
    public function afterResponse(callable $callback): static;
}
