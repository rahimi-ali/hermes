<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Core\Http;

use HermesFramework\Hermes\Core\Http\Exceptions\RouterException;
use Psr\Http\Message\ServerRequestInterface;

interface HttpRouter
{
    /**
     * @throws RouterException
     */
    public function match(ServerRequestInterface $request): MatchedHttpRoute;
}
