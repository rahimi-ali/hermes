<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use Psr\Http\Message\ServerRequestInterface;

interface HttpRouter
{
    public function match(ServerRequestInterface $request): HttpRoute;
}
