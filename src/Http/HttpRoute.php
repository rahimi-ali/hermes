<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

interface HttpRoute
{
    public function getName(): string;

    public function getPath(): string;

    /**
     * @return "DELETE"|"GET"|"HEAD"|"OPTIONS"|"PATCH"|"POST"|"PUT"
     */
    public function getMethod(): string;

    /**
     * @return callable(mixed ...$args): ResponseInterface
     */
    public function getHandler(): callable;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddleware(): array;
}
