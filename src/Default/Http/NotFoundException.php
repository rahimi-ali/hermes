<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use Exception;
use HermesFramework\Hermes\Core\Http\Exceptions\NotFoundException as NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Not Found', 404);
    }
}
