<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Core\DependencyInjection;

interface ConfigurableServiceContainer extends ServiceContainer
{
    /**
     * Alias for bindOncePerScope
     *
     * @param class-string|string $id
     * @param (callable(ServiceContainer $container): mixed)|class-string|mixed $callback
     */
    public function bind(string $id, mixed $callback): static;

    /**
     * The callback is evaluated every time the entry is requested
     *
     * @param class-string|string $id
     * @param (callable(ServiceContainer $container): mixed)|class-string $callback
     */
    public function bindTransient(string $id, callable|string $callback): static;

    /**
     * The callback is run once lazily(on request) per each scope(for example a http request)
     *
     * @param class-string|string $id
     * @param (callable(ServiceContainer $container): mixed)|class-string|mixed $callback
     */
    public function bindOncePerScope(string $id, mixed $callback): static;

    /**
     * The callback is run once lazily(on request) and all later requests for the entry use that instance
     *
     * @param class-string|string $id
     * @param (callable(ServiceContainer $container): mixed)|class-string|mixed $callback
     */
    public function bindOnce(string $id, mixed $callback): static;
}
