<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http\Swoole;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class SwooleResponseEmitter
{
    private const array BODY_PROHIBITED_STATUSES = [204, 205, 304];

    public function emit(ResponseInterface $psrResponse, Response $swooleResponse, bool $withoutBody = false): bool
    {
        $swooleResponse->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $swooleResponse->header($name, $values, true);
        }

        if (
            $withoutBody ||
            $psrResponse->getStatusCode() < 200 ||
            in_array($psrResponse->getStatusCode(), self::BODY_PROHIBITED_STATUSES, true)
        ) {
            return $swooleResponse->end();
        }

        return $swooleResponse->end($psrResponse->getBody());
    }
}
