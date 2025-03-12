<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RahimiAli\Hermes\Container\ServiceContainer;

class MiddlewareDispatcher implements RequestHandlerInterface
{
    /**
     * @var callable(): ResponseInterface
     */
    private $handler;

    /**
     * @param class-string<MiddlewareInterface>[] $middlewareQueue
     * @param callable(): ResponseInterface $handler
     */
    public function __construct(
        private array $middlewareQueue,
        callable $handler,
        private readonly ServiceContainer $container,
    ) {
        $this->handler = $handler;
    }

    /**
     * Handles the request by processing the middleware stack and eventually calling the final handler.
     *
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewareQueue)) {
            return $this->container->call($this->handler, [
                ServerRequestInterface::class => $request,
                'request' => $request,
            ]);
        }

        $middlewareClass = array_shift($this->middlewareQueue);

        $middleware = $this->container->make($middlewareClass);

        assert($middleware instanceof MiddlewareInterface, 'middleware is instance of MiddlewareInterface');

        return $middleware->process($request, $this);
    }
}
