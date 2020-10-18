<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Di\Exception\ExpectedInvokableException;
use Pluf\Di\Exception\FrozenServiceException;
use Pluf\Di\Exception\UnknownIdentifierException;

/**
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ContainerTest extends TestCase
{

    public function testWithString()
    {
        $container = new Container();
        $container['param'] = Container::value('value');

        $this->assertEquals('value', $container['param']);
    }

    public function testWithClosure()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };

        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $container['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $container = new Container();
        $container['item'] = Container::factory(function () {
            return new Fixtures\Service();
        });

        $serviceOne = $container['item'];
        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $container['item'];
        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $container = new Container();
        // Default is factory
        $container['factory'] = function () {
            return new Fixtures\Service();
        };

        // Container is registerd by default
        // $container['container'] = function ($container) {
        // return $container;
        // };

        $this->assertNotSame($container, $container['factory']);
        $this->assertSame($container, $container['container']);
    }

    public function testIsset()
    {
        $container = new Container();
        $container['param'] = Container::value('value');
        $container['service'] = Container::service(function () {
            return new Fixtures\Service();
        });

        $container['null'] = Container::value(null);

        $this->assertTrue(isset($container['param']));
        $this->assertTrue(isset($container['service']));
        $this->assertTrue(isset($container['null']));
        $this->assertFalse(isset($container['non_existent']));
    }

    // public function testConstructorInjection()
    // {
    // $params = [
    // 'param' => 'value'
    // ];
    // $container = new Container();

    // $this->assertSame($params['param'], $container['param']);
    // }
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $this->expectException(UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');

        $container = new Container();
        echo $container['foo'];
    }

    /**
     *
     * @group legacy
     */
    public function testLegacyOffsetGetValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');

        $container = new Container();
        echo $container['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $container = new Container();
        $container['foo'] = Container::value(null);
        $this->assertNull($container['foo']);
    }

    public function testUnset()
    {
        $container = new Container();
        $container['param'] = Container::value('value');
        $container['service'] = Container::service(function () {
            return new Fixtures\Service();
        });

        unset($container['param'], $container['service']);
        $this->assertFalse(isset($container['param']));
        $this->assertFalse(isset($container['service']));
    }

    /**
     *
     * @dataProvider serviceDefinitionProvider
     */
    public function testShare($service)
    {
        $container = new Container();
        $container['shared_service'] = Container::service($service);
        $container['value'] = Container::value('value');

        $serviceOne = $container['shared_service'];
        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceOne);

        $serviceTwo = $container['shared_service'];
        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     *
     * @dataProvider serviceDefinitionProvider
     */
    public function testProtect($service)
    {
        $container = new Container();
        $container['protected'] = Container::value($service);

        $this->assertSame($service, $container['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $container = new Container();
        $container['global_function'] = Container::value('strlen');
        $this->assertSame('strlen', $container['global_function']);
    }

    public function testRaw()
    {
        $container = new Container();
        $container['factory'] = $definition = function () {
            return 'foo';
        };
        $this->assertSame($definition, $container->raw('factory'));
    }

    /**
     *
     * @test
     */
    public function testRawValidatesKeyIsPresent()
    {
        $this->expectException(UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');

        $container = new Container();
        $container->raw('foo');
    }

    /**
     *
     * @group legacy
     */
    public function testLegacyRawValidatesKeyIsPresent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');

        $container = new Container();
        $container->raw('foo');
    }

    // /**
    // *
    // * @dataProvider serviceDefinitionProvider
    // */
    // public function testExtend($service)
    // {
    // $container = new Container();
    // $container['shared_service'] = Container::service(function () {
    // return new Fixtures\Service();
    // });
    // $container['factory_service'] = function () {
    // return new Fixtures\Service();
    // };

    // $container->extend('shared_service', $service);
    // $serviceOne = $container['shared_service'];
    // $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceOne);
    // $serviceTwo = $container['shared_service'];
    // $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceTwo);
    // $this->assertSame($serviceOne, $serviceTwo);
    // $this->assertSame($serviceOne->value, $serviceTwo->value);

    // $container->extend('factory_service', $service);
    // $serviceOne = $container['factory_service'];
    // $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceOne);
    // $serviceTwo = $container['factory_service'];
    // $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $serviceTwo);
    // $this->assertNotSame($serviceOne, $serviceTwo);
    // $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    // }

    // public function testExtendDoesNotLeakWithFactories()
    // {
    // if (\extension_loaded('pimple')) {
    // $this->markTestSkipped('Pimple extension does not support this test');
    // }
    // $container = new Container();

    // $container['foo'] = $container->factory(function () {
    // return;
    // });
    // $container['foo'] = $container->extend('foo', function ($foo, $container) {
    // return;
    // });
    // unset($container['foo']);

    // $p = new \ReflectionProperty($container, 'values');
    // $p->setAccessible(true);
    // $this->assertEmpty($p->getValue($container));

    // $p = new \ReflectionProperty($container, 'factories');
    // $p->setAccessible(true);
    // $this->assertCount(0, $p->getValue($container));
    // }

    // public function testExtendValidatesKeyIsPresent()
    // {
    // $this->expectException(UnknownIdentifierException::class);
    // $this->expectExceptionMessage('Identifier "foo" is not defined.');

    // $container = new Container();
    // $container->extend('foo', function () {});
    // }

    // /**
    // *
    // * @group legacy
    // */
    // public function testLegacyExtendValidatesKeyIsPresent()
    // {
    // $this->expectException(\InvalidArgumentException::class);
    // $this->expectExceptionMessage('Identifier "foo" is not defined.');

    // $container = new Container();
    // $container->extend('foo', function () {});
    // }
    public function testKeys()
    {
        $containr = new Container();
        $containr['foo'] = Container::value(123);
        $containr['bar'] = Container::value(123);

        $this->assertEquals([
            'container',
            'foo',
            'bar'
        ], $containr->keys());
    }

    /**
     *
     * @test
     */
    public function settingAnInvokableObjectShouldTreatItAsFactory()
    {
        $container = new Container();
        $container['invokable'] = new Fixtures\Invokable();

        $this->assertInstanceOf('Pluf\Tests\Fixtures\Service', $container['invokable']);
    }

    /**
     *
     * @test
     */
    public function settingNonInvokableObjectShouldTreatItAsParameter()
    {
        $container = new Container();
        $container['non_invokable'] = Container::value(new Fixtures\NonInvokable());

        $this->assertInstanceOf('Pluf\Tests\Fixtures\NonInvokable', $container['non_invokable']);
    }

    /**
     *
     * @dataProvider badServiceDefinitionProvider
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(ExpectedInvokableException::class);
        // $this->expectExceptionMessage('Service definition is not a Closure or invokable object.');

        $container = new Container();
        $container['key'] = $service;
        return $container;
    }

    /**
     *
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyFactoryFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Service definition is not a Closure or invokable object.');

        $container = new Container();
        $container['key'] = $service;
        return $container;
    }

    /**
     *
     * @dataProvider badServiceDefinitionProvider
     */
    public function testProtectFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(ExpectedInvokableException::class);
        // $this->expectExceptionMessage('Factory is not a Closure or invokable object.');

        $container = new Container();
        $container['key'] = $service;
        return $container;
    }

    /**
     *
     * @group legacy
     * @dataProvider badServiceDefinitionProvider
     */
    public function testLegacyProtectFailsForInvalidServiceDefinitions($service)
    {
        $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Callable is not a Closure or invokable object.');

        $container = new Container();
        $container['key'] = $service;
        return $container;
    }

    // /**
    // *
    // * @dataProvider badServiceDefinitionProvider
    // */
    // public function testExtendFailsForKeysNotContainingServiceDefinitions($service)
    // {
    // $this->expectException(InvalidServiceIdentifierException::class);
    // $this->expectExceptionMessage('Identifier "foo" does not contain an object definition.');

    // $container = new Container();
    // $container['foo'] = $service;
    // $container->extend('foo', function () {});
    // }

    // /**
    // *
    // * @group legacy
    // * @dataProvider badServiceDefinitionProvider
    // */
    // public function testLegacyExtendFailsForKeysNotContainingServiceDefinitions($service)
    // {
    // $this->expectException(\InvalidArgumentException::class);
    // $this->expectExceptionMessage('Identifier "foo" does not contain an object definition.');

    // $container = new Container();
    // $container['foo'] = $service;
    // $container->extend('foo', function () {});
    // }

    // /**
    // *
    // * @group legacy
    // * @expectedDeprecation How Pimple behaves when extending protected closures will be fixed in Pimple 4. Are you sure "foo" should be protected?
    // */
    // public function testExtendingProtectedClosureDeprecation()
    // {
    // $container = new Container();
    // $container['foo'] = $container->protect(function () {
    // return 'bar';
    // });

    // $container->extend('foo', function ($value) {
    // return $value . '-baz';
    // });

    // $this->assertSame('bar-baz', $container['foo']);
    // }

    // /**
    // *
    // * @dataProvider badServiceDefinitionProvider
    // */
    // public function testExtendFailsForInvalidServiceDefinitions($service)
    // {
    // $this->expectException(ExpectedInvokableException::class);
    // $this->expectExceptionMessage('Extension service definition is not a Closure or invokable object.');

    // $container = new Container();
    // $container['foo'] = function () {};
    // $container->extend('foo', $service);
    // }

    // /**
    // *
    // * @group legacy
    // * @dataProvider badServiceDefinitionProvider
    // */
    // public function testLegacyExtendFailsForInvalidServiceDefinitions($service)
    // {
    // $this->expectException(\InvalidArgumentException::class);
    // $this->expectExceptionMessage('Extension service definition is not a Closure or invokable object.');

    // $container = new Container();
    // $container['foo'] = function () {};
    // $container->extend('foo', $service);
    // }

    // public function testExtendFailsIfFrozenServiceIsNonInvokable()
    // {
    // $this->expectException(FrozenServiceException::class);
    // $this->expectExceptionMessage('Cannot override frozen service "foo".');

    // $container = new Container();
    // $container['foo'] = function () {
    // return new Fixtures\NonInvokable();
    // };
    // $foo = $container['foo'];

    // $container->extend('foo', function () {});
    // }

    // public function testExtendFailsIfFrozenServiceIsInvokable()
    // {
    // $this->expectException(FrozenServiceException::class);
    // $this->expectExceptionMessage('Cannot override frozen service "foo".');

    // $container = new Container();
    // $container['foo'] = function () {
    // return new Fixtures\Invokable();
    // };
    // $foo = $container['foo'];

    // $container->extend('foo', function () {});
    // }

    /**
     * Provider for invalid service definitions.
     */
    public function badServiceDefinitionProvider()
    {
        return [
            [
                123
            ],
            [
                new Fixtures\NonInvokable()
            ]
        ];
    }

    /**
     * Provider for service definitions.
     */
    public function serviceDefinitionProvider()
    {
        return [
            [
                function ($value) {
                    $service = new Fixtures\Service();
                    $service->value = $value;

                    return $service;
                }
            ],
            [
                new Fixtures\Invokable()
            ]
        ];
    }

    public function testDefiningNewServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];
        $this->assertNotNull($foo);

        $container['bar'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['bar']);
    }

    public function testOverridingServiceAfterFreeze()
    {
        $this->expectException(FrozenServiceException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');

        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];
        $this->assertNotNull($foo);

        $container['foo'] = function () {
            return 'bar';
        };
    }

    /**
     *
     * @group legacy
     */
    public function testLegacyOverridingServiceAfterFreeze()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot override frozen service "foo".');

        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];
        $this->assertNotNull($foo);

        $container['foo'] = function () {
            return 'bar';
        };
    }

    public function testRemovingServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];
        $this->assertNotNull($foo);

        unset($container['foo']);
        $container['foo'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['foo']);
    }

    // public function testExtendingService()
    // {
    // $container = new Container();
    // $container['foo'] = function () {
    // return 'foo';
    // };
    // $container['foo'] = $container->extend('foo', function ($foo, $app) {
    // return "$foo.bar";
    // });
    // $container['foo'] = $container->extend('foo', function ($foo, $app) {
    // return "$foo.baz";
    // });
    // $this->assertSame('foo.bar.baz', $container['foo']);
    // }

    // public function testExtendingServiceAfterOtherServiceFreeze()
    // {
    // $container = new Container();
    // $container['foo'] = function () {
    // return 'foo';
    // };
    // $container['bar'] = function () {
    // return 'bar';
    // };
    // $foo = $container['foo'];

    // $container['bar'] = $container->extend('bar', function ($bar, $app) {
    // return "$bar.baz";
    // });
    // $this->assertSame('bar.baz', $container['bar']);
    // }

    /**
     * Use multilevel contaienr
     *
     * @test
     */
    public function testMultilevelContainer()
    {
        $root = new Container();
        $container = new Container($root);

        $root['foo'] = function () {
            return 'foo';
        };
        $container['bar'] = function () {
            return 'bar';
        };

        $this->assertSame('foo', $container['foo']);
        $this->assertSame('bar', $container['bar']);
    }

    /**
     * Use multilevel contaienr
     *
     * @test
     */
    public function testMultilevelContainerOverrid()
    {
        $root = new Container();
        $container = new Container($root);

        $root['foo'] = function () {
            return 'foo';
        };

        $root['foo'] = function () {
            return 'foo';
        };
        $container['foo'] = function () {
            return 'foo2';
        };
        $container['bar'] = function () {
            return 'bar';
        };

        $this->assertSame('foo2', $container['foo']);
        $this->assertSame('bar', $container['bar']);
    }
}
