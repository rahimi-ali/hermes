<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RahimiAli\Hermes\Container\ServiceContainer;

class HttpKernel implements RequestHandlerInterface
{
    private string $workerId;

    public function __construct(
        private readonly HttpServer $httpServer,
        private readonly HttpRouter $httpRouter,
        private readonly ServiceContainer $serviceContainer,
        private readonly HttpKernelDebugLogger|null $kernelDebugLogger = null,
    ) {
        if ($this->kernelDebugLogger) {
            $this->httpServer->onWorkerStart(
                function ($server, $workerId): void {
                    $this->workerId = (string)$workerId;
                    $this->kernelDebugLogger?->workerStarted($this->workerId);
                }
            );

            $this->httpServer->onWorkerShutdown(
                function ($server, $workerId): void {
                    $this->workerId = (string)$workerId;
                    $this->kernelDebugLogger?->workerShutdown($this->workerId);
                }
            );

            $this->httpServer->afterResponse(function (ServerRequestInterface $request, ResponseInterface $response): void {
                $this->kernelDebugLogger?->responseSent(
                    $this->workerId,
                    $request->getAttribute('unique_request_id', '-'),
                    $request->getMethod(),
                    $request->getUri()->getPath(),
                    $request->getAttribute('matched_route', null)->getName() ?? '-',
                    $response->getStatusCode(),
                );
            });
        }
    }

    public function start(): void
    {
        $this->httpServer->onRequest($this);
        $this->httpServer->start();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestId = uniqid();
        $request = $request->withAttribute('unique_request_id', $requestId);

        if ($this->kernelDebugLogger) {
            $this->kernelDebugLogger->requestReceived(
                $this->workerId,
                $requestId,
                $request->getMethod(),
                $request->getUri()->getPath(),
            );

            $route = $this->httpRouter->match($request);

            $request = $request->withAttribute('matched_route', $route);

            $this->kernelDebugLogger->requestMatched(
                $this->workerId,
                $requestId,
                $request->getMethod(),
                $request->getUri()->getPath(),
                $route->getName(),
            );

            $dispatcher = new MiddlewareDispatcher(
                $route->getMiddleware(),
                $route->getHandler(),
                $this->serviceContainer->newScopedInstance(),
            );

            return $dispatcher->handle($request);
        } else {
            $route = $this->httpRouter->match($request);

            return new MiddlewareDispatcher(
                $route->getMiddleware(),
                $route->getHandler(),
                $this->serviceContainer->newScopedInstance(),
            )->handle($request);
        }
    }
}
