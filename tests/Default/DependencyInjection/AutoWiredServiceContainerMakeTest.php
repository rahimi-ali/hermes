<?php

declare(strict_types=1);

namespace Tests\Container\AutoWired;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RahimiAli\Hermes\Core\DependencyInjection\ServiceContainer;
use RahimiAli\Hermes\Default\DependencyInjection\AutoWiredServiceContainer;

#[CoversClass(AutoWiredServiceContainer::class)]
class AutoWiredServiceContainerMakeTest extends TestCase
{
    #[Test]
    public function it_can_make_a_class_that_was_bound_transiently_using_the_binding(): void
    {
        $container = new AutoWiredServiceContainer();

        $container->bindTransient(ServiceA::class, function () {
            $instance = new ServiceA();
            $instance->state = 666;
            return $instance;
        });

        $made = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $made);
        $this->assertEquals(666, $made->state);

        $madeAgain = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgain);
        $this->assertNotSame($made, $madeAgain);
    }

    #[Test]
    public function it_can_make_a_class_that_was_bound_as_singleton_using_the_binding(): void
    {
        $container = new AutoWiredServiceContainer();

        $container->bindOnce(ServiceA::class, function () {
            $instance = new ServiceA();
            $instance->state = 666;
            return $instance;
        });

        $made = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $made);
        $this->assertEquals(666, $made->state);

        $madeAgain = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgain);
        $this->assertSame($made, $madeAgain);
    }

    #[Test]
    public function it_can_make_a_class_that_was_bound_as_singleton_using_the_binding_from_scoped_instances(): void
    {
        $container = new AutoWiredServiceContainer();

        $container->bindOnce(ServiceA::class, function (ServiceContainer $serviceContainer) use ($container) {
            $this->assertSame($container, $serviceContainer);

            $instance = new ServiceA();
            $instance->state = 666;
            return $instance;
        });

        $scopedContainer = $container->newScopedInstance();

        $made = $scopedContainer->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $made);
        $this->assertEquals(666, $made->state);

        $madeAgain = $scopedContainer->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgain);
        $this->assertSame($made, $madeAgain);
    }

    #[Test]
    public function it_can_make_a_class_that_was_bound_as_scoped_using_the_binding(): void
    {
        $container = new AutoWiredServiceContainer();

        $container->bindOncePerScope(ServiceA::class, function () {
            $instance = new ServiceA();
            $instance->state = 666;
            return $instance;
        });

        $made = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $made);
        $this->assertEquals(666, $made->state);

        $madeAgain = $container->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgain);
        $this->assertSame($made, $madeAgain);
    }

    #[Test]
    public function it_can_make_a_class_that_was_bound_as_scoped_singleton_using_the_binding_for_each_scope(): void
    {
        $container = new AutoWiredServiceContainer();

        $previouslyUsedContainer = null;
        $incrementor = 666;
        $container->bindOncePerScope(
            ServiceA::class,
            function (ServiceContainer $serviceContainer) use ($container, &$previouslyUsedContainer, &$incrementor) {
                $this->assertNotSame($container, $serviceContainer);
                $this->assertNotSame($previouslyUsedContainer, $serviceContainer);
                $previouslyUsedContainer = $serviceContainer;

                $instance = new ServiceA();
                $instance->state = $incrementor;
                $incrementor++;
                return $instance;
            }
        );

        $scopedContainerA = $container->newScopedInstance();
        $scopedContainerB = $container->newScopedInstance();

        $madeByA = $scopedContainerA->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeByA);
        $this->assertEquals(666, $madeByA->state);

        $madeByB = $scopedContainerB->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeByB);
        $this->assertEquals(667, $madeByB->state);

        $this->assertNotSame($madeByA, $madeByB);

        $madeAgainByA = $scopedContainerA->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgainByA);
        $this->assertSame($madeByA, $madeAgainByA);

        $madeAgainByB = $scopedContainerB->make(ServiceA::class);
        $this->assertInstanceOf(ServiceA::class, $madeAgainByB);
        $this->assertSame($madeByB, $madeAgainByB);

        $this->assertNotSame($madeAgainByA, $madeAgainByB);
    }
}

interface ServiceAInterface
{
    public function getState(): int;
}

class ServiceA implements ServiceAInterface
{
    public int $state = 0;

    public function getState(): int
    {
        return $this->state;
    }
}
