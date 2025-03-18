<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\Http;

use Psr\Http\Server\MiddlewareInterface;

interface ConfigurableRoute
{
    public function withName(string $name): static;

    /**
     * @param class-string<MiddlewareInterface>|class-string<MiddlewareInterface>[] $middleware
     */
    public function withMiddleware(array|string $middleware): static;

    /**
     * @param class-string<MiddlewareInterface>|class-string<MiddlewareInterface>[] $withoutMiddleware
     */
    public function withoutMiddleware(array|string $withoutMiddleware): static;

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): static;

    public function withAttribute(string $attribute, mixed $value): static;
}
