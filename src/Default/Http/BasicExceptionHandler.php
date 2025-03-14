<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\Http;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RahimiAli\Hermes\Core\Http\ExceptionHandler;
use RahimiAli\Hermes\Core\Http\Exceptions\MethodNotAllowedException;
use RahimiAli\Hermes\Core\Http\Exceptions\NotFoundException;
use Throwable;

class BasicExceptionHandler implements ExceptionHandler
{
    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        if ($exception instanceof NotFoundException) {
            return new JsonResponse(['message' => 'Not Found'], 404);
        }

        if ($exception instanceof MethodNotAllowedException) {
            return new JsonResponse(['message' => 'Method Not Allowed'], 405);
        }

        return new JsonResponse(['message' => 'Server Error'], 500);
    }
}
