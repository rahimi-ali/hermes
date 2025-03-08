<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Container;

use Psr\Container\ContainerInterface;

interface ServiceContainer extends ContainerInterface
{
    public const string BINDING_TYPE_SIMPLE = 'simple';

    public const string BINDING_TYPE_SINGLETON = 'singleton';

    public const string BINDING_TYPE_SCOPED_SINGLETON = 'scoped-singleton';

    /**
     * Calls __construct() of the $class and uses Type Based and Name Based Reflection to resolve arguments(in that order)
     *
     * @template C of object
     * @param class-string<C> $class
     * @param array<string, mixed> $parameters
     * @return C
     */
    public function make(string $class, array $parameters = []): object;

    /**
     * If a function or object method or static class method, It will call the function and resolve arguments using Type Based then Name Based Reflection
     * If an instance class method, It will construct the class using Type Based then Name Based Reflection then call the method with same argument resolving strategy
     *
     * @template R
     * @param callable(): R $callable
     * @param array<string, mixed> $parameters
     * @return R
     */
    public function call(callable $callable, array $parameters = []): mixed;

    /**
     * Get binding definition if any
     *
     * @return array{callback: callable(ServiceContainer $container): mixed, bindingType: self::BINDING_TYPE_*}|null
     */
    public function getBinding(string $id): array|null;

    /**
     * Returns a scoped instance of the container that has its own state to manage SCOPED bindings and forwards the rest to parent(this instance)
     */
    public function newScopedInstance(): static;
}
