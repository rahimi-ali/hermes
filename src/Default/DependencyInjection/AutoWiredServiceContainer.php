<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RahimiAli\Hermes\Core\DependencyInjection\ConfigurableServiceContainer;
use RahimiAli\Hermes\Core\DependencyInjection\ServiceContainer;
use RahimiAli\Hermes\Default\DependencyInjection\Exceptions\CircularDependencyException;
use RahimiAli\Hermes\Default\DependencyInjection\Exceptions\FailedToResolveParameterException;
use RahimiAli\Hermes\Default\DependencyInjection\Exceptions\NotFoundException;
use RahimiAli\Hermes\Default\DependencyInjection\Exceptions\UninstantiableClassException;
use RahimiAli\Hermes\Default\DependencyInjection\Utils\InteractsWithReflection;
use RahimiAli\Hermes\Default\DependencyInjection\Utils\NotResolved;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

class AutoWiredServiceContainer implements ConfigurableServiceContainer
{
    use InteractsWithReflection;

    /**
     * @var array<string, array{
     *     bindingType: ServiceContainer::BINDING_TYPE_*,
     *     callback: (callable(ServiceContainer $container): mixed)|class-string|mixed
     * }>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $resolved = [];

    /**
     * @var array<string, bool>
     */
    private array $resolutionStack = [];

    final public function __construct(
        private readonly ConfigurableServiceContainer|null $rootServiceContainer = null,
    ) {
    }

    public function bind(string $id, mixed $callback): static
    {
        return $this->bindOncePerScope($id, $callback);
    }

    public function bindTransient(string $id, callable|string $callback): static
    {
        $this->bindings[$id] = [
            'bindingType' => ServiceContainer::BINDING_TYPE_TRANSIENT,
            'callback' => $callback,
        ];

        return $this;
    }

    public function bindOncePerScope(string $id, mixed $callback): static
    {
        unset($this->resolved[$id]);

        $callback = (is_string($callback) && class_exists($callback)) ?
            fn (ServiceContainer $container) => $container->make($callback) :
            $callback;

        $this->bindings[$id] = [
            'bindingType' => ServiceContainer::BINDING_TYPE_SCOPED_SINGLETON,
            'callback' => $callback,
        ];

        return $this;
    }

    public function bindOnce(string $id, mixed $callback): static
    {
        if ($this->rootServiceContainer !== null) {
            $this->rootServiceContainer->bindOnce($id, $callback);
            return $this;
        }

        unset($this->resolved[$id]);

        $callback = (is_string($callback) && class_exists($callback)) ?
            fn (ServiceContainer $container) => $container->make($callback) :
            $callback;

        $this->bindings[$id] = [
            'bindingType' => ServiceContainer::BINDING_TYPE_SINGLETON,
            'callback' => $callback,
        ];

        return $this;
    }

    public function get(string $id): mixed
    {
        $resolved = $this->resolved[$id] ?? $this->resolve($id);
        if ($resolved instanceof NotResolved) {
            throw new NotFoundException($id);
        }
        return $resolved;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->resolved) ||
            array_key_exists($id, $this->bindings) ||
            ($this->rootServiceContainer?->has($id) ?? false);
    }

    /**
     * @throws ReflectionException
     * @throws FailedToResolveParameterException
     */
    public function make(string $class, array $overrideParameters = []): object
    {
        assert((class_exists($class) || interface_exists($class)));

        // Check for circular dependencies
        if (isset($this->resolutionStack[$class])) {
            $dependencyChain = array_keys($this->resolutionStack);
            $dependencyChain[] = $class;
            throw new CircularDependencyException($class, $dependencyChain);
        }

        // If already resolved, use it
        if ($resolved = $this->resolved[$class] ?? false) {
            return $resolved;
        }

        $this->resolutionStack[$class] = true;
        try {
            // If its bound use the binding
            if ($bound = $this->resolve($class)) {
                return $bound;
            }

            $reflection = $this->getReflectionClass($class);

            if (!$reflection->isInstantiable()) {
                throw new UninstantiableClassException($class);
            }

            $constructor = $reflection->getConstructor();
            if ($constructor === null || empty($constructor->getParameters())) {
                return new $class();
            }

            $arguments = [];
            foreach ($constructor->getParameters() as $parameter) {
                $arguments[$parameter->getName()] = $this->makeParameter($parameter, $overrideParameters, "making $class");
            }

            return new $class(...$arguments);
        } finally {
            unset($this->resolutionStack[$class]);
        }
    }

    public function call(callable|array $callable, array $overrideParameters = []): mixed
    {
        /** @phpstan-ignore-next-line treating doc types as certain */
        assert(is_callable($callable) || (count($callable) === 2 && is_string($callable[0]) && is_string($callable[1])));

        if (!is_callable($callable)) {
            /** @phpstan-ignore-next-line treating doc types as certain */
            if (is_string($callable[0]) && class_exists($callable[0])) {
                /** @phpstan-ignore-next-line treating doc types as certain */
                return $this->call([$this->make($callable[0]), $callable[1]], $overrideParameters);
            } else {
                throw new InvalidArgumentException('callable must either be an actual callable or a [class, method] pair.');
            }
        }

        $normalizedCallable = null;
        $callableType = self::callableType($callable, $normalizedCallable);
        if ($callableType === 'unknown') {
            throw new InvalidArgumentException('invalid callable: ' . serialize($callable));
        }
        /** @var callable $normalizedCallable */

        if ($callableType === 'function' || $callableType === 'closure' || $callableType === 'invocable') {
            /** @phpstan-ignore-next-line treating doc types as certain */
            $reflection = new ReflectionFunction($callable);
        } else {
            /** @phpstan-ignore-next-line treating doc types as certain */
            $reflection = new ReflectionMethod($normalizedCallable[0], $normalizedCallable[1]);
        }

        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $arguments[] = $this->makeParameter($parameter, $overrideParameters, 'calling callable');
        }

        return call_user_func($normalizedCallable, ...$arguments);
    }

    public function getBinding(string $id): array|null
    {
        return $this->rootServiceContainer === null ?
            ($this->bindings[$id] ?? null) :
            $this->rootServiceContainer->getBinding($id);
    }

    public function newScopedInstance(): static
    {
        return new static($this);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(string $id): mixed
    {
        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            $binding = $this->bindings[$id];

            assert(
                $binding['bindingType'] !== self::BINDING_TYPE_SINGLETON && $this->rootServiceContainer === null,
                'singleton binding should not be present in a non-root service container.',
            );

            if (is_callable($binding['callback'])) {
                $instance = $instance = call_user_func($binding['callback'], $this);
            } else {
                $instance = $binding['callback'];
            }

            if ($binding['bindingType'] !== self::BINDING_TYPE_TRANSIENT) {
                $this->resolved[$id] = $instance;
            }

            return $instance;
        }

        if ($this->rootServiceContainer !== null) {
            try {
                $boundOnParent = $this->rootServiceContainer->getBinding($id);
                if ($boundOnParent !== null && $boundOnParent['bindingType'] === self::BINDING_TYPE_SCOPED_SINGLETON) {
                    $this->bindings[$id] = $boundOnParent;
                    return $this->resolve($id);
                }

                return $this->rootServiceContainer->get($id);
            } catch (NotFoundExceptionInterface) {
                return new NotResolved();
            }
        }

        return new NotResolved();
    }

    /**
     * @param array<string, mixed> $parameters
     * @throws ContainerExceptionInterface
     * @throws FailedToResolveParameterException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function makeParameter(ReflectionParameter $parameter, array $parameters, string $context = ''): mixed
    {
        $paramType = $parameter->getType();
        $paramName = $parameter->getName();

        // Respect override
        if (array_key_exists($paramName, $parameters)) {
            return $parameters[$paramName];
        }

        if ($paramType !== null) {
            if ($paramType instanceof ReflectionIntersectionType) {
                $context = $context === '' ? '' : ' ' . $context . ' and';
                trigger_error(
                    "Intersection types are not resolvable by container, only the parameter name will be used. Raised while$context resolving $paramName parameter.",
                    E_USER_WARNING,
                );
            }

            // Try to Auto Wire using Types first
            /** @var class-string[] $types */
            $types = [];
            if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                $types[] = $paramType->getName();
            } else {
                if ($paramType instanceof ReflectionUnionType) {
                    $types = [...$types, ...self::flattenReflectionUnionType($paramType)];
                }
            }
            foreach ($types as $type) {
                $resolved = $this->resolve($type);
                if (!$resolved instanceof NotResolved) {
                    return $resolved;
                }
                try {
                    /** @phpstan-ignore-next-line */
                    return $this->make($type);
                } catch (FailedToResolveParameterException) {
                }
            }
        }

        $resolvedByName = $this->resolve($paramName);
        if (!$resolvedByName instanceof NotResolved) {
            return $resolvedByName;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new FailedToResolveParameterException(
            when: "$context",
            parameterName: $parameter->getName(),
            parameterType: $parameter->hasType() ? (string)$parameter->getType() : 'not specified',
        );
    }
}
