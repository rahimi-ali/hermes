<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\Http;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdParser;

class FastRouteRegistrar implements RouteRegistrar
{
    private readonly RouteCollector $collector;

    public function __construct()
    {
        $this->collector = new RouteCollector(new StdParser(), new GroupCountBasedDataGenerator());
    }

    public function route(
        string $method,
        string $path,
        callable|array $handler,
        array $middleware = [],
        array $withoutMiddleware = [],
    ): ConfigurableRoute {
        $path = '/' . trim($path, '/');

        $route = new Route(
            $method,
            $path,
            $handler,
        )
            ->withMiddleware(array_diff($middleware, $withoutMiddleware))
            ->withoutMiddleware($withoutMiddleware);

        $this->collector->addRoute($method, $path, $route);

        return $route;
    }

    public function group(string $prefix = '', array $middleware = []): ConfigurableRouteGroup
    {
        return new RouteGroup($this)->withPrefix($prefix)->withMiddleware($middleware);
    }

    public function get(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('GET', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('POST', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('PUT', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('PATCH', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('DELETE', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function options(
        string $path,
        callable|array $handler,
        array $middleware = [],
        array $withoutMiddleware = [],
    ): ConfigurableRoute {
        return $this->route('OPTIONS', $path, $handler, $middleware, $withoutMiddleware);
    }

    public function head(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute
    {
        return $this->route('HEAD', $path, $handler, $middleware, $withoutMiddleware);
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->collector->getData();
    }
}
