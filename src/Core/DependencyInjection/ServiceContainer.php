<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Core\DependencyInjection;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

interface ServiceContainer extends ContainerInterface
{
    public const string BINDING_TYPE_TRANSIENT = 'transient';

    public const string BINDING_TYPE_SINGLETON = 'singleton';

    public const string BINDING_TYPE_SCOPED_SINGLETON = 'scoped-singleton';

    /**
     * Calls __construct() of the $class and uses Type Based and Name Based Reflection to resolve arguments(in that order)
     *
     * @template C of object
     * @param class-string<C> $class
     * @param array<string, mixed> $overrideParameters
     * @return C
     * @throws ContainerExceptionInterface
     */
    public function make(string $class, array $overrideParameters = []): object;

    /**
     * If a function or [object, static method] pair or static class method, It will call the function and resolve arguments
     * using Type Based then Name Based Reflection
     * If an instance [class, not static method] method, It will construct the class using Type Based then Name Based Reflection
     * then call the method with same argument resolving strategy
     *
     * @template R
     * @param array{class-string, string}|(callable(): R) $callable
     * @param array<string, mixed> $overrideParameters
     * @return R
     * @throws ContainerExceptionInterface
     */
    public function call(callable|array $callable, array $overrideParameters = []): mixed;

    /**
     * Get binding definition if any
     *
     * @return array{
     *     callback: (callable(ServiceContainer $container): mixed)|class-string|mixed,
     *     bindingType: self::BINDING_TYPE_*
     * }|null
     */
    public function getBinding(string $id): array|null;

    /**
     * Returns a scoped instance of the container that has its own state to manage SCOPED bindings and forwards the rest to parent(this instance)
     */
    public function newScopedInstance(): static;
}
