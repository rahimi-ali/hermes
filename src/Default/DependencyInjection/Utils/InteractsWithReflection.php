<?php

declare(strict_types=1);

namespace Hermes\Hermes\Default\DependencyInjection\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

trait InteractsWithReflection
{
    /**
     * @var array<class-string, ReflectionClass<object>>
     */
    private array $reflectionCache = [];

    /**
     * @param class-string<object> $class
     * @return ReflectionClass<object>
     */
    protected function getReflectionClass(string $class): ReflectionClass
    {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }
        return $this->reflectionCache[$class];
    }

    /**
     * @return string[]
     */
    protected static function flattenReflectionUnionType(ReflectionUnionType $reflectionType): array
    {
        $types = [];
        foreach ($reflectionType->getTypes() as $type) {
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $types[] = $type->getName();
            } else {
                if ($type instanceof ReflectionUnionType) {
                    $types = [...$types, ...self::flattenReflectionUnionType($type)];
                }
            }
        }
        return $types;
    }

    /**
     * The callable types and normalizations are given in the table below:
     *
     *  Callable                        | Normalization                   | Type
     * ---------------------------------+---------------------------------+--------------
     *  function (...) use (...) {...}  | function (...) use (...) {...}  | 'closure'
     *  $object                         | $object                         | 'invocable'
     *  "function"                      | "function"                      | 'function'
     *  "class::method"                 | ["class", "method"]             | 'static'
     *  ["class", "parent::method"]     | ["parent of class", "method"]   | 'static'
     *  ["class", "self::method"]       | ["class", "method"]             | 'static'
     *  ["class", "method"]             | ["class", "method"]             | 'static'
     *  [$object, "parent::method"]     | [$object, "parent::method"]     | 'object'
     *  [$object, "self::method"]       | [$object, "method"]             | 'object'
     *  [$object, "method"]             | [$object, "method"]             | 'object'
     * ---------------------------------+---------------------------------+--------------
     *  other callable                  | idem                            | 'unknown'
     *
     * @return 'closure'|'function'|'invocable'|'object'|'static'|'unknown'
     * @throws ReflectionException
     * @see https://www.php.net/manual/en/language.types.callable.php#118032 Original Implementation
     */
    protected static function callableType(callable $callable, mixed &$normalized): string
    {
        switch (true) {
            case is_object($callable):
                $normalized = $callable;
                return $callable::class === 'Closure' ? 'closure' : 'invocable';
            case is_string($callable):
                if (preg_match('~^(?<class>[a-z_][a-z0-9_]*)::(?<method>[a-z_][a-z0-9_]*)$~i', $callable, $matches)) {
                    if (new ReflectionMethod($matches['class'], $matches['method'])->isStatic()) {
                        $normalized = [$matches['class'], $matches['method']];
                        return 'static';
                    }
                } else {
                    $normalized = $callable;
                    return 'function';
                }
                break;
            case is_array($callable):
                if (preg_match('~^(:?(?<reference>self|parent)::)?(?<method>[a-z_][a-z0-9_]*)$~i', $callable[1], $matches)) {
                    if (is_string($callable[0])) {
                        if (strtolower($matches['reference']) === 'parent') {
                            [$reference, $method] = [get_parent_class($callable[0]), $matches['method']];
                        } else {
                            [$reference, $method] = [$callable[0], $matches['method']];
                        }
                        assert(is_string($reference));
                        if (new ReflectionMethod($reference, $method)->isStatic()) {
                            $normalized = [$reference, $method];
                            return 'static';
                        }
                    } else {
                        if ('self' === strtolower($matches['reference'])) {
                            [$reference, $method] = [$callable[0], $matches['method']];
                        } else {
                            [$reference, $method] = $callable;
                        }
                        if (!new ReflectionMethod($reference, $method)->isStatic()) {
                            $normalized = [$reference, $method];
                            return 'object';
                        }
                    }
                }
                break;
        }
        $normalized = $callable;
        return 'unknown';
    }
}
