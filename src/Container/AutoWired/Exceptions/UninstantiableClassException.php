<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Container\AutoWired\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class UninstantiableClassException extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct("$class is neither instantiable nor bound.");
    }
}
