<?php

declare(strict_types=1);

namespace Hermes\Hermes\Core\Http;

use Hermes\Hermes\Core\DependencyInjection\ServiceContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class HttpKernel implements RequestHandlerInterface
{
    private string $workerId;

    public function __construct(
        private readonly HttpServer $httpServer,
        private readonly HttpRouter $httpRouter,
        private readonly ServiceContainer $serviceContainer,
        private readonly ExceptionHandler $exceptionHandler,
        private readonly HttpKernelDebugger|null $kernelDebugger = null,
    ) {
        if ($this->kernelDebugger) {
            $this->httpServer->onWorkerStart(
                function ($server, $workerId): void {
                    $this->workerId = (string)$workerId;
                    $this->kernelDebugger?->workerStarted($this->workerId);
                }
            );

            $this->httpServer->onWorkerShutdown(
                function ($server, $workerId): void {
                    $this->workerId = (string)$workerId;
                    $this->kernelDebugger?->workerShutdown($this->workerId);
                }
            );
        }
    }

    public function start(): void
    {
        $this->httpServer->onRequest($this);
        $this->httpServer->start();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $request = $request->withAttribute('request_received_time', self::timeNano());

            $requestId = uniqid();
            $request = $request->withAttribute('unique_request_id', $requestId);

            $this->kernelDebugger?->requestReceived(
                $this->workerId,
                $requestId,
                $request,
            );

            $route = $this->httpRouter->match($request);

            $request = $request->withAttribute('matched_route', $route);
            foreach ($route->getAttributes() as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            $this->kernelDebugger?->requestMatched(
                $this->workerId,
                $requestId,
                $request,
                $route,
                (int)round((self::timeNano() - $request->getAttribute('request_received_time')) / 1000),
            );

            $dispatcher = new MiddlewareDispatcher(
                $route->getMiddleware(),
                $route->getHandler(),
                $this->serviceContainer->newScopedInstance(),
            );

            $response = $dispatcher->handle($request);
        } catch (Throwable $exception) {
            $response = $this->exceptionHandler->handle($request, $exception);
        }

        $this->kernelDebugger?->responseSent(
            $this->workerId,
            $request->getAttribute('unique_request_id', '-'),
            $request,
            $request->getAttribute('matched_route'),
            $response,
            (int)round((self::timeNano() - $request->getAttribute('request_received_time')) / 1000),
        );

        return $response;
    }

    private static function timeNano(): int
    {
        $time = hrtime();
        return $time[0] * 1_000_000_000 + $time[1];
    }
}
