<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Di\CallableResolver;
use Pluf\Di\Container;
use Pluf\Di\Exception\NotCallableException;
use Pluf\Tests\Fixtures\InvokerTestFixture;
use Pluf\Tests\Mock\CallableSpy;
use stdClass;

class CallableResolverTest extends TestCase
{

    /**
     *
     * @var CallableResolver
     */
    private $resolver;

    /**
     *
     * @var Container
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->resolver = new CallableResolver($this->container);
    }

    /**
     *
     * @test
     */
    public function resolves_function()
    {
        $result = $this->resolver->resolve('strlen');

        $this->assertSame(strlen('Hello world!'), $result('Hello world!'));
    }

    /**
     *
     * @test
     */
    public function resolves_namespaced_function()
    {
        $result = $this->resolver->resolve(__NAMESPACE__ . '\foo');

        $this->assertEquals('bar', $result());
    }

    /**
     *
     * @test
     */
    public function resolves_callable_from_container()
    {
        $callable = function () {};
        $this->container['thing-to-call'] = function () use (&$callable) {
            return $callable;
        };

        $this->assertSame($callable, $this->resolver->resolve('thing-to-call'));
    }

    /**
     *
     * @test
     */
    public function resolves_invokable_class()
    {
        $callable = new CallableSpy();
        $this->container[CallableSpy::class] = function () use (&$callable) {
            return $callable;
        };

        $this->assertSame($callable, $this->resolver->resolve(CallableSpy::class));
    }

    /**
     *
     * @test
     */
    public function resolve_array_method_call()
    {
        $fixture = new InvokerTestFixture();
        $this->container[InvokerTestFixture::class] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->resolver->resolve(array(
            InvokerTestFixture::class,
            'foo'
        ));

        $result();
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function resolve_string_method_call()
    {
        $fixture = new InvokerTestFixture();
        $this->container[InvokerTestFixture::class] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->resolver->resolve(InvokerTestFixture::class . '::foo');

        $result();
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function resolves_array_method_call_with_service()
    {
        $fixture = new InvokerTestFixture();
        $this->container['thing-to-call'] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->resolver->resolve(array(
            'thing-to-call',
            'foo'
        ));

        $result();
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function resolves_string_method_call_with_service()
    {
        $fixture = new InvokerTestFixture();
        $this->container['thing-to-call'] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->resolver->resolve('thing-to-call::foo');

        $result();
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function throws_resolving_non_callable_from_container()
    {
        $this->expectExceptionMessage("'foo' is neither a callable nor a valid container entry");
        $this->expectException(NotCallableException::class);
        $resolver = new CallableResolver(new Container());
        $resolver->resolve('foo');
    }

    /**
     *
     * @test
     */
    public function handles_objects_correctly_in_exception_message()
    {
        $this->expectExceptionMessage("Instance of stdClass is not a callable");
        $this->expectException(NotCallableException::class);
        $resolver = new CallableResolver(new Container());
        $resolver->resolve(new stdClass());
    }

    /**
     *
     * @test
     */
    public function handles_method_calls_correctly_in_exception_message()
    {
        $this->expectExceptionMessage("stdClass::test() is not a callable");
        $this->expectException(NotCallableException::class);
        $resolver = new CallableResolver(new Container());
        $resolver->resolve(array(
            new stdClass(),
            'test'
        ));
    }
}

function foo()
{
    return 'bar';
}