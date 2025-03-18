<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use HermesFramework\Hermes\Core\Http\HttpRouter;
use HermesFramework\Hermes\Core\Http\MatchedHttpRoute;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class FastRouteHttpRouter implements HttpRouter
{
    private readonly FastRouteRegistrar $registrar;

    private GroupCountBasedDispatcher $dispatcher;

    public function __construct()
    {
        $this->registrar = new FastRouteRegistrar();
    }

    public function match(ServerRequestInterface $request): MatchedHttpRoute
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new GroupCountBasedDispatcher($this->registrar->getData());
        }

        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                /** @var Route $route */
                $route = $routeInfo[1];

                return new MatchedRoute(
                    $route->getMethod(),
                    $route->getPath(),
                    $route->getName(),
                    $route->getHandler(),
                    $route->getMiddleware(),
                    $routeInfo[2] ?? [],
                    $route->getAttributes(),
                );
            case Dispatcher::NOT_FOUND:
                throw new NotFoundException();
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException();
            default:
                throw new RuntimeException('unhandled route result: ' . $routeInfo[0]);
        }
    }

    public function getRegistrar(): RouteRegistrar
    {
        return $this->registrar;
    }
}
