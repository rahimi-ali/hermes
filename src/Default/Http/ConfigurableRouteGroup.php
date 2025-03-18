<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use Psr\Http\Server\MiddlewareInterface;

interface ConfigurableRouteGroup
{
    public function withPrefix(string $prefix): static;

    /**
     * @param class-string<MiddlewareInterface>|class-string<MiddlewareInterface>[] $middleware
     */
    public function withMiddleware(array|string $middleware): static;

    /**
     * @param class-string<MiddlewareInterface>|class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function withoutMiddleware(array|string $withoutMiddleware): static;

    /**
     * @param callable(RouteRegistrar $registrar): void $callback
     */
    public function withRoutes(callable $callback): void;
}
