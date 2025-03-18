<?php

declare(strict_types=1);

namespace Hermes\Hermes\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ExceptionHandler
{
    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface;
}
