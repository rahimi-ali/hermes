<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Runtimes\Swoole;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;

interface SwooleRequestTransformer
{
    public function transform(Request $swooleRequest): ServerRequestInterface;
}
