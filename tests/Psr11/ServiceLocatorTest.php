<?php
namespace Pluf\Tests\Psr11;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Di\ServiceLocator;
use Pluf\Di\Exception\UnknownIdentifierException;
use Pluf\Tests\Fixtures;

/**
 * ServiceLocator test case.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ServiceLocatorTest extends TestCase
{

    public function testCanAccessServices()
    {
        $container = new Container();
        $container['service'] = Container::service(function () {
            return new Fixtures\Service();
        });
        $locator = new ServiceLocator($container, [
            'service'
        ]);

        $this->assertSame($container['service'], $locator->get('service'));
    }

    public function testCanAccessAliasedServices()
    {
        $container = new Container();
        $container['service'] = Container::service(function () {
            return new Fixtures\Service();
        });
        $locator = new ServiceLocator($container, [
            'alias' => 'service'
        ]);

        $this->assertSame($container['service'], $locator->get('alias'));
    }

    public function testCannotAccessAliasedServiceUsingRealIdentifier()
    {
        $this->expectException(UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "service" is not defined.');

        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };
        $locator = new ServiceLocator($container, [
            'alias' => 'service'
        ]);

        $service = $locator->get('service');
        return $service;
    }

    public function testGetValidatesServiceCanBeLocated()
    {
        $this->expectException(UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "foo" is not defined.');

        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };
        $locator = new ServiceLocator($container, [
            'alias' => 'service'
        ]);

        $service = $locator->get('foo');
        return $service;
    }

    public function testGetValidatesTargetServiceExists()
    {
        $this->expectException(UnknownIdentifierException::class);
        $this->expectExceptionMessage('Identifier "invalid" is not defined.');

        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };
        $locator = new ServiceLocator($container, [
            'alias' => 'invalid'
        ]);

        $service = $locator->get('alias');
        return $service;
    }

    public function testHasValidatesServiceCanBeLocated()
    {
        $container = new Container();
        $container['service1'] = function () {
            return new Fixtures\Service();
        };
        $container['service2'] = function () {
            return new Fixtures\Service();
        };
        $locator = new ServiceLocator($container, [
            'service1'
        ]);

        $this->assertTrue($locator->has('service1'));
        $this->assertFalse($locator->has('service2'));
    }

    public function testHasChecksIfTargetServiceExists()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Fixtures\Service();
        };
        $locator = new ServiceLocator($container, [
            'foo' => 'service',
            'bar' => 'invalid'
        ]);

        $this->assertTrue($locator->has('foo'));
        $this->assertFalse($locator->has('bar'));
    }
}
