<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Container;

interface ConfigurableServiceContainer extends ServiceContainer
{
    /**
     * The callback is evaluated every time the entry is requested
     *
     * @template T
     * @param class-string<T>|string $id
     * @param callable(ServiceContainer $container): T $callback
     */
    public function bind(string $id, callable $callback): static;

    /**
     * The callback is run once lazily(on request) per each scope(for example a http request)
     *
     * @template T
     * @param class-string<T>|string $id
     * @param callable(ServiceContainer $container): T $callback
     */
    public function bindOnceForScope(string $id, callable $callback): static;

    /**
     * The callback is run once lazily(on request) and all later requests for the entry use that instance
     *
     * @template T
     * @param class-string<T>|string $id
     * @param callable(ServiceContainer $container): T $callback
     */
    public function bindOnce(string $id, callable $callback): static;

    /**
     * Tells the container to cache the created instance if ever created and not instantiate it in subsequent calls to make.
     *
     * @param class-string $className
     */
    public function makeOnce(string $className): static;

    /**
     * Tells the container to cache the created instance if ever created fot the scope and not instantiate it in subsequent calls to make in that scope.
     *
     * @param class-string $className
     */
    public function makeOncePerScope(string $className): static;
}
