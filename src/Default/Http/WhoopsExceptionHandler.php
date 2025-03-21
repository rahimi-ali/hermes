<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use HermesFramework\Hermes\Core\Http\ExceptionHandler;
use HermesFramework\Hermes\Core\Http\Exceptions\MethodNotAllowedException;
use HermesFramework\Hermes\Core\Http\Exceptions\NotFoundException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class WhoopsExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private readonly bool $debug = true,
        private readonly bool $json = false,
    ) {
    }

    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        if ($this->debug) {
            $whoops = new Run();
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);

            if ($this->json) {
                $whoops->pushHandler(new JsonResponseHandler());
            } else {
                $prettyPageHandler = new PrettyPageHandler();
                $prettyPageHandler->handleUnconditionally(true);
                $whoops->pushHandler($prettyPageHandler);
            }

            $code = match (true) {
                $exception instanceof NotFoundException => 404,
                $exception instanceof MethodNotAllowedException => 405,
                default => 500,
            };

            return $this->json ?
                new JsonResponse($whoops->handleException($exception), $code) :
                new Response\HtmlResponse($whoops->handleException($exception), $code);
        }

        switch (true) {
            case $exception instanceof NotFoundException:
                $message = 'Not Found';
                $code = 404;
                break;
            case $exception instanceof MethodNotAllowedException:
                $message = 'Method Not Allowed';
                $code = 405;
                break;
            default:
                $message = 'Internal Server Error';
                $code = 500;
        }

        return $this->json ? new JsonResponse(compact('message'), $code) : new Response($message, $code);
    }
}
