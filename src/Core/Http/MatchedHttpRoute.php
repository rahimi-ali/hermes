<?php

declare(strict_types=1);

namespace Hermes\Hermes\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

interface MatchedHttpRoute
{
    public function getName(): string;

    public function getPath(): string;

    /**
     * @return "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT"
     */
    public function getMethod(): string;

    /**
     * @return array{0: class-string, 1: non-empty-string}|(callable(): ResponseInterface)
     */
    public function getHandler(): callable|array;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddleware(): array;

    /**
     * @return array<string, string>
     */
    public function getParameters(): array;

    public function getParameter(string $name): string|null;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    public function getAttribute(string $name): string|null;
}
