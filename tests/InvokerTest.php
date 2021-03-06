<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Di\Invoker;
use Pluf\Di\Exception\NotCallableException;
use Pluf\Di\Exception\NotEnoughParametersException;
use Pluf\Di\ParameterResolver\AssociativeArrayResolver;
use Pluf\Di\ParameterResolver\Container\ParameterNameContainerResolver;
use Pluf\Di\ParameterResolver\Container\TypeHintContainerResolver;
use Pluf\Tests\Fixtures\InvokerTestFixture;
use Pluf\Tests\Fixtures\InvokerTestMagicMethodFixture;
use Pluf\Tests\Fixtures\InvokerTestStaticFixture;
use Pluf\Tests\Mock\CallableSpy;
use stdClass;

/**
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *        
 */
class InvokerTest extends TestCase
{

    /**
     *
     * @var Invoker
     */
    private $invoker;

    /**
     *
     * @var Container
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->invoker = new Invoker(null, $this->container);
    }

    /**
     *
     * @test
     */
    public function should_invoke_closure()
    {
        $callable = new CallableSpy();

        $this->invoker->call($callable);

        $this->assertWasCalled($callable);
    }

    /**
     *
     * @test
     */
    public function should_invoke_method()
    {
        $fixture = new InvokerTestFixture();

        $this->invoker->call([
            $fixture,
            'foo'
        ]);

        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function cannot_invoke_unknown_method()
    {
        $this->expectExceptionMessage("Pluf\Tests\Fixtures\InvokerTestFixture::bar() is not a callable.");
        $this->expectException(NotCallableException::class);
        $this->invoker->call([
            new InvokerTestFixture(),
            'bar'
        ]);
    }

    /**
     *
     * @test
     */
    public function cannot_invoke_magic_method()
    {
        $this->expectExceptionMessage("Pluf\Tests\Fixtures\InvokerTestMagicMethodFixture::foo() is not a callable. A __call() method exists but magic methods are not supported.");
        $this->expectException(NotCallableException::class);
        $this->invoker->call(array(
            new InvokerTestMagicMethodFixture(),
            'foo'
        ));
    }

    /**
     *
     * @test
     */
    public function should_invoke_static_method()
    {
        $result = $this->invoker->call([
            InvokerTestStaticFixture::class,
            'foo'
        ]);

        $this->assertEquals('bar', $result);
    }

    /**
     *
     * @test
     */
    public function should_invoke_static_method_with_scope_resolution_syntax()
    {
        $result = $this->invoker->call('Pluf\Tests\Fixtures\InvokerTestStaticFixture::foo');

        $this->assertEquals('bar', $result);
    }

    /**
     *
     * @test
     */
    public function should_return_the_callable_return_value()
    {
        $result = $this->invoker->call(function () {
            return 42;
        });

        $this->assertEquals(42, $result);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_no_value_for_parameter()
    {
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 2 ($bar)');
        $this->expectException(NotEnoughParametersException::class);
        $this->invoker->call(function ($foo, $bar, $baz) {}, [
            'foo' => 'foo',
            'baz' => 'baz'
        ]);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_no_value_for_parameter_even_with_trailing_optional_parameters()
    {
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 2 ($bar)');
        $this->expectException(NotEnoughParametersException::class);
        $this->invoker->call(function ($foo, $bar, $baz = null) {}, [
            'foo' => 'foo',
            'baz' => 'baz'
        ]);
    }

    /**
     *
     * @test
     */
    public function should_invoke_callable_with_parameters_indexed_by_position()
    {
        $callable = new CallableSpy();

        $this->invoker->call($callable, [
            'foo',
            'bar'
        ]);

        $this->assertWasCalledWith($callable, [
            'foo',
            'bar'
        ]);
    }

    /**
     *
     * @test
     */
    public function should_invoke_callable_with_parameters_indexed_by_name()
    {
        $parameters = [
            'foo' => 'foo',
            'bar' => 'bar'
        ];

        $result = $this->invoker->call(function ($foo, $bar) {
            return $foo . $bar;
        }, $parameters);

        $this->assertEquals('foobar', $result);
    }

    /**
     *
     * @test
     */
    public function should_invoke_callable_with_default_value_for_undefined_parameters()
    {
        $parameters = [
            'foo', // Positioned parameter
            'baz' => 'baz' // Named parameter
        ];

        $result = $this->invoker->call(function ($foo, $bar = 'bar', $baz = null) {
            return $foo . $bar . $baz;
        }, $parameters);

        $this->assertEquals('foobarbaz', $result);
    }

    /**
     *
     * @test
     */
    public function should_do_dependency_injection_with_typehint_container_resolver()
    {
        $resolver = new TypeHintContainerResolver($this->container);
        $this->invoker->getParameterResolver()->prependResolver($resolver);

        $expected = new stdClass();
        $this->container['stdClass'] = function () use (&$expected) {
            return $expected;
        };

        $result = $this->invoker->call(function (stdClass $foo) {
            return $foo;
        });

        $this->assertSame($expected, $result);
    }

    /**
     *
     * @test
     */
    public function should_do_dependency_injection_with_parameter_name_container_resolver()
    {
        $resolver = new ParameterNameContainerResolver($this->container);
        $this->invoker->getParameterResolver()->prependResolver($resolver);

        $expected = new stdClass();
        $this->container['foo'] = function () use (&$expected) {
            return $expected;
        };

        $result = $this->invoker->call(function ($foo) {
            return $foo;
        });

        $this->assertSame($expected, $result);
    }

    /**
     *
     * @test
     */
    public function should_resolve_callable_from_container()
    {
        $callable = new CallableSpy();
        $this->container['thing-to-call'] = function () use (&$callable) {
            return $callable;
        };

        $this->invoker->call('thing-to-call');

        $this->assertWasCalled($callable);
    }

    /**
     *
     * @test
     */
    public function should_resolve_array_callable_from_container()
    {
        $fixture = new InvokerTestFixture();
        $this->container['thing-to-call'] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->invoker->call([
            'thing-to-call',
            'foo'
        ]);

        $this->assertEquals('bar', $result);
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function should_resolve_callable_from_container_with_scope_resolution_syntax()
    {
        $fixture = new InvokerTestFixture();
        $this->container['thing-to-call'] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->invoker->call('thing-to-call::foo');

        $this->assertEquals('bar', $result);
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function should_resolve_array_callable_from_container_with_class_name()
    {
        $fixture = new InvokerTestFixture();
        $this->container[InvokerTestFixture::class] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->invoker->call([
            InvokerTestFixture::class,
            'foo'
        ]);

        $this->assertEquals('bar', $result);
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     *
     * @test
     */
    public function should_resolve_callable_from_container_with_class_name_in_scope_resolution_syntax()
    {
        $fixture = new InvokerTestFixture();
        $this->container[InvokerTestFixture::class] = function () use (&$fixture) {
            return $fixture;
        };

        $result = $this->invoker->call('Pluf\Tests\Fixtures\InvokerTestFixture::foo');

        $this->assertEquals('bar', $result);
        $this->assertTrue($fixture->wasCalled);
    }

    /**
     * Mixing named parameters with positioned parameters is a really bad idea.
     * When that happens, the positioned parameters have the highest priority and will
     * override named parameters in case of conflicts.
     *
     * Note that numeric array indexes ignore string indexes. In our example, the
     * 'bar' value has the position `0`, which overrides the 'foo' value.
     *
     * @test
     */
    public function positioned_parameters_have_the_highest_priority()
    {
        $factory = function ($foo, $bar = 300) {
            return [
                $foo,
                $bar
            ];
        };
        $result = $this->invoker->call($factory, [
            'foo' => 'foo',
            'bar'
        ]);

        $this->assertEquals([
            'bar',
            300
        ], $result);
    }

    /**
     *
     * @test
     */
    public function should_not_invoke_statically_a_non_static_method()
    {
        $this->expectExceptionMessage("Cannot call foo() on Pluf\Tests\Fixtures\InvokerTestFixture because it is not a class nor a valid container entry");
        $this->expectException(NotCallableException::class);
        $this->invoker->call([
            InvokerTestFixture::class,
            'foo'
        ]);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_calling_non_callable_without_container()
    {
        $this->expectExceptionMessage("'foo' is not a callable");
        $this->expectException(NotCallableException::class);
        $invoker = new Invoker();
        $invoker->call('foo');
    }

    /**
     *
     * @test
     */
    public function should_throw_if_calling_non_callable_without_container_2()
    {
        $this->expectExceptionMessage("NULL is not a callable");
        $this->expectException(NotCallableException::class);
        $invoker = new Invoker();
        $invoker->call(null);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_calling_non_callable_with_container()
    {
        $this->expectExceptionMessage("'foo' is neither a callable nor a valid container entry");
        $this->expectException(NotCallableException::class);
        $invoker = new Invoker(null, new Container());
        $invoker->call('foo');
    }

    /**
     *
     * @test
     */
    public function should_throw_if_calling_non_callable_object()
    {
        $this->expectExceptionMessage('Instance of stdClass is not a callable');
        $this->expectException(NotCallableException::class);
        $invoker = new Invoker();
        $invoker->call(new stdClass());
    }

    /**
     *
     * @test
     */
    public function should_invoke_static_method_rather_than_resolving_entry_from_container()
    {
        // Register a non-callable so that test fails if we try to invoke that
        $this->container[InvokerTestStaticFixture::class] = function () {
            return 'foobar';
        };

        // Call the static method: shouldn't get from the container even though the
        // entry exist (because we are calling a static method)
        $result = $this->invoker->call([
            InvokerTestStaticFixture::class,
            'foo'
        ]);
        $this->assertEquals('bar', $result);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_no_value_for_optional_parameter_1()
    {
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 2 ($bar)');
        $this->expectException(NotEnoughParametersException::class);
        // Create without the DefaultValueResolver
        $this->invoker = new Invoker(new AssociativeArrayResolver(), $this->container);
        $this->invoker->call(function ($foo, $bar = null) {}, [
            'foo' => 'foo'
        ]);
    }

    /**
     *
     * @test
     */
    public function should_throw_if_no_value_for_optional_parameter_2()
    {
        $this->expectExceptionMessage('Unable to invoke the callable because no value was given for parameter 2 ($bar)');
        $this->expectException(NotEnoughParametersException::class);
        // Create without the DefaultValueResolver
        $this->invoker = new Invoker(new AssociativeArrayResolver(), $this->container);
        $this->invoker->call(function ($foo, $bar = null, $baz = null) {}, [
            'foo' => 'foo',
            'baz' => 'baz'
        ]);
    }

    private function assertWasCalled(CallableSpy $callableSpy)
    {
        $this->assertEquals(1, $callableSpy->getCallCount(), 'The callable should be called once');
    }

    private function assertWasCalledWith(CallableSpy $callableSpy, array $parameters)
    {
        $this->assertWasCalled($callableSpy);
        $this->assertEquals($parameters, $callableSpy->getLastCallParameters());
    }
}


