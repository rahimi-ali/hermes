<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\DependencyInjection\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class FailedToResolveParameterException extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $when, string $parameterName, string|null $parameterType = null)
    {
        parent::__construct(
            $parameterType ?
                "Could not resolve a value for the parameter '$parameterName' either by name or its type($parameterType) when $when." :
                "Could not resolve a value for the parameter '$parameterName' by its name when $when.",
        );
    }
}
