<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use Psr\Http\Server\MiddlewareInterface;

class RouteGroup implements ConfigurableRouteGroup, RouteRegistrar
{
    private string $prefix = '';

    /**
     * @var class-string<MiddlewareInterface>[]
     */
    private array $middleware = [];

    /**
     * @var class-string<MiddlewareInterface>[]
     */
    private array $withoutMiddleware = [];

    public function __construct(
        private readonly RouteRegistrar $baseRegistrar,
    ) {
    }

    public function withPrefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function withMiddleware(array|string $middleware): static
    {
        $this->middleware = [
            ...$this->middleware,
            ...(is_array($middleware) ? $middleware : [$middleware]),
        ];
        return $this;
    }

    public function withoutMiddleware(array|string $withoutMiddleware): static
    {
        $this->withoutMiddleware = [
            ...$this->withoutMiddleware,
            ...(is_array($withoutMiddleware) ? $withoutMiddleware : [$withoutMiddleware]),
        ];
        return $this;
    }

    public function withRoutes(callable $callback): void
    {
        $callback($this);
    }

    public function route(
        string $method,
        string $path,
        callable|array $handler,
        array $middleware = [],
        array $withoutMiddleware = [],
    ): ConfigurableRoute {
        $middleware = array_diff([...$this->middleware, ...$middleware], [...$this->withoutMiddleware, ...$withoutMiddleware]);
        return $this->baseRegistrar->route(
            $method,
            rtrim($this->prefix, '/') . '/' . ltrim($path, '/'),
            $handler,
            $middleware,
            $withoutMiddleware,
        );
    }

    public function group(string $prefix = '', array $middleware = []): ConfigurableRouteGroup
    {
        return new self($this)->withPrefix($prefix)->withMiddleware($middleware);
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
}
