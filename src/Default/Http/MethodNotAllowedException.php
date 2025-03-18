<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\Http;

use Exception;
use Hermes\Hermes\Core\Http\Exceptions\MethodNotAllowedException as MethodNotAllowedExceptionInterface;

class MethodNotAllowedException extends Exception implements MethodNotAllowedExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Method Not Allowed', 405);
    }
}
