<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

interface HttpKernelDebugLogger
{
    public function workerStarted(string $workerId): void;

    public function workerShutdown(string $workerId): void;

    public function requestReceived(string $workerId, string $requestId, string $method, string $path): void;

    public function requestMatched(string $workerId, string $requestId, string $method, string $path, string $routeName): void;

    public function responseSent(
        string $workerId,
        string $requestId,
        string $method,
        string $path,
        string $routeName,
        int $statusCode,
    ): void;
}
