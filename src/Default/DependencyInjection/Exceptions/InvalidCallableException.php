<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\DependencyInjection\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class InvalidCallableException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param callable|mixed[] $callable
     */
    public function __construct(callable|array $callable)
    {
        parent::__construct(
            sprintf(
                'callable must be either of callable type or an array in the shape of {0: className, 1: methodName}, %s is neither.',
                print_r($callable, true),
            )
        );
    }
}
