<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\DependencyInjection\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct("$id could not be resolved by the service container.");
    }
}
