<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Core\Http;

use Psr\Http\Message\ServerRequestInterface;
use RahimiAli\Hermes\Core\Http\Exceptions\RouterException;

interface HttpRouter
{
    /**
     * @throws RouterException
     */
    public function match(ServerRequestInterface $request): MatchedHttpRoute;
}
