<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteRegistrar
{
    /**
     * @param "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT" $method
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function route(
        string $method,
        string $path,
        callable|array $handler,
        array $middleware = [],
        array $withoutMiddleware = [],
    ): ConfigurableRoute;

    /**
     * @param class-string<MiddlewareInterface>[] $middleware
     */
    public function group(string $prefix = '', array $middleware = []): ConfigurableRouteGroup;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function get(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function post(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function put(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function patch(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function delete(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function options(
        string $path,
        callable|array $handler,
        array $middleware = [],
        array $withoutMiddleware = [],
    ): ConfigurableRoute;

    /**
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $middleware
     * @param class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function head(string $path, callable|array $handler, array $middleware = [], array $withoutMiddleware = []): ConfigurableRoute;
}
