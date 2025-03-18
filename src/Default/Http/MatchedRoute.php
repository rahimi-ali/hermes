<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\Http;

use Hermes\Hermes\Core\Http\MatchedHttpRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

readonly class MatchedRoute implements MatchedHttpRoute
{
    /**
     * @var array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     */
    private mixed $handler;

    /**
     * @param "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT" $method
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     * @param class-string<MiddlewareInterface>[] $applicableMiddleware
     * @param array<string, string> $parameters
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $method,
        private string $path,
        private string $name,
        callable|array $handler,
        private array $applicableMiddleware,
        private array $parameters,
        private array $attributes,
    ) {
        $this->handler = $handler;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->applicableMiddleware;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $name): string|null
    {
        return $this->parameters[$name] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): string|null
    {
        return $this->attributes[$name] ?? null;
    }
}
