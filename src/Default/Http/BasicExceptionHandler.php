<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\Http;

use Hermes\Hermes\Core\Http\ExceptionHandler;
use Hermes\Hermes\Core\Http\Exceptions\MethodNotAllowedException;
use Hermes\Hermes\Core\Http\Exceptions\NotFoundException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class BasicExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private readonly bool $debug = false,
    ) {
    }

    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        if ($exception instanceof NotFoundException) {
            return new JsonResponse(['message' => 'Not Found'], 404);
        }

        if ($exception instanceof MethodNotAllowedException) {
            return new JsonResponse(['message' => 'Method Not Allowed'], 405);
        }

        if ($this->debug) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage(),
                ],
                500,
            );
        }
        return new JsonResponse(['message' => 'Server Error'], 500);
    }
}
