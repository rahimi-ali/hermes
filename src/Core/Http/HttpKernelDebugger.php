<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpKernelDebugger
{
    public function workerStarted(string $workerId): void;

    public function workerShutdown(string $workerId): void;

    public function requestReceived(string $workerId, string $requestId, ServerRequestInterface $request): void;

    public function requestMatched(
        string $workerId,
        string $requestId,
        ServerRequestInterface $request,
        MatchedHttpRoute $matchedRoue,
        int $matchTimeMicro,
    ): void;

    public function responseSent(
        string $workerId,
        string $requestId,
        ServerRequestInterface $request,
        MatchedHttpRoute|null $matchedRoue,
        ResponseInterface $response,
        int $responseTimeMicro,
    ): void;
}
