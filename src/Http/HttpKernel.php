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
        private readonly bool $debug = false,
    ) {
        if ($this->debug) {
            $this->httpServer->onWorkerStart(
                function ($server, $workerId): void {
                    // TODO: nice formatting
                    $this->workerId = (string)$workerId;
                    echo "Worker #$workerId started.\n";
                }
            );
            $this->httpServer->onWorkerShutdown(
                function ($server, $workerId): void {
                    // TODO: nice formatting
                    echo "Worker #$workerId shutdown.\n";
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
        // TODO: nice formatting
        if ($this->debug) {
            $requestId = uniqid();

            echo "$requestId\trequest_received\t[{$request->getMethod()}]{$request->getUri()->getPath()}\tworker:$this->workerId\n";

            $route = $this->httpRouter->match($request);

            echo "$requestId\trequest_matched\t[{$request->getMethod()}]{$request->getUri()->getPath()}\troute:{$route->name()}\n";

            $dispatcher = new MiddlewareDispatcher(
                $route->middleware(),
                $route->handler(),
                $this->serviceContainer->newScopedInstance(),
            );

            $response = $dispatcher->handle($request);

            echo "$requestId\tresponse_sent\t[{$request->getMethod()}]{$request->getUri()->getPath()}\tstatus:{$response->getStatusCode()}\n";

            return $response;
        } else {
            $route = $this->httpRouter->match($request);

            return new MiddlewareDispatcher(
                $route->middleware(),
                $route->handler(),
                $this->serviceContainer->newScopedInstance(),
            )->handle($request);
        }
    }
}
