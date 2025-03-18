<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Route implements ConfigurableRoute
{
    /**
     * @var array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface)
     */
    private mixed $handler;

    private string $name;

    /**
     * @var class-string<MiddlewareInterface>[]
     */
    private array $middleware = [];

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * @param "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT" $method
     * @param array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface) $handler
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        callable|array $handler,
    ) {
        assert(is_callable($handler) || method_exists($handler[0] ?? '', $handler[1] ?? ''));

        $this->handler = $handler;
        $this->name = "[$this->method]$this->path";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT"
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface)
     */
    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttribute(string $attribute): mixed
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function withName(string $name): static
    {
        $this->name = $name;
        if ($this->name === '') {
            $this->name = "[$this->method]$this->path";
        }
        return $this;
    }

    public function withMiddleware(array|string $middleware): static
    {
        $this->middleware = [
            ...$this->middleware,
            ...(is_string($middleware) ? [$middleware] : $middleware),
        ];
        return $this;
    }

    public function withoutMiddleware(array|string $withoutMiddleware): static
    {
        $this->middleware = array_diff($this->middleware, is_array($withoutMiddleware) ? $withoutMiddleware : [$withoutMiddleware]);
        return $this;
    }

    public function withAttributes(array $attributes): static
    {
        $this->attributes = [
            ...$this->attributes,
            ...$attributes,
        ];
        return $this;
    }

    public function withAttribute(string $attribute, mixed $value): static
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }
}
