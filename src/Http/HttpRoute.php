<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

interface HttpRoute
{
    public function name(): string;

    public function path(): string;

    /**
     * @return "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT"
     */
    public function method(): string;

    /**
     * @return callable(mixed ...$args): ResponseInterface
     */
    public function handler(): callable;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function middleware(): array;
}
