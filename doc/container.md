# Using the container

This documentation describes the API of the container object itself.

## get() & has()

The container implements the [PSR-11](http://www.php-fig.org/psr/psr-11/) standard. That means it 
implements [`Psr\Container\ContainerInterface`](https://github.com/php-fig/container/blob/master/src/ContainerInterface.php):

```php
namespace Psr\Container;

interface ContainerInterface
{
    public function get($id);
    public function has($id);
}
```

You are encouraged to type-hint against this interface instead of the implementation (`Pluf\Di\Container`) whenever possible. 
Doing so means your code is decoupled from PHP-DI and you can switch to another container anytime.

## set()

You can set entries directly on the container:

```php
$container->set('foo', Container::value('bar'));
$container->set('MyInterface', Container::factory('MyClass'));
$container->set('MyInterface', Container::service('MyServiceClass'));
```

However it is recommended to use definition files. See the [definition documentation](definition.md).


## call()

The container exposes a `call()` method that can invoke any PHP callable.

It offers the following additional features over using `call_user_func()`:

- named parameters (pass parameters indexed by name instead of position)

    ```php
    $container->call(function ($foo, $bar) {
        // ...
    }, [
        'foo' => 'Hello',
        'bar' => 'World',
    ]);

    // Can also be useful in a micro-framework for example
    $container->call($controller, $_GET + $_POST);
    ```

- dependency injection based on the type-hinting

    ```php
    $container->call(function (Logger $logger, EntityManager $em) {
        // ...
    });
    ```

- dependency injection based on explicit definition

    ```php
    $container->call(function ($dbHost) {
        // ...
    }, [
        // Either indexed by parameter names
        'dbHost' => \DI\get('db.host'),
    ]);

    $container->call(function ($dbHost) {
        // ...
    }, [
        // Or not indexed
        \DI\get('db.host'),
    ]);
    ```

The best part is that you can mix all that:

```php
$container->call(function (Logger $logger, $dbHost, $operation) {
    // ...
}, [
    'operation' => 'delete',
    'dbHost'    => \DI\get('db.host'),
]);
```

The `call()` method is particularly useful to invoke controllers, for example:

```php
$controller = function ($name, EntityManager $em) {
    // ...
}

$container->call($controller, $_GET); // $_GET contains ['name' => 'John']
```

This leaves the liberty to the developer writing controllers to get request parameters
*and* services using dependency injection.

As with `call()` is defined in `Invoker\Invoker` so that you can type-hint against that 
interface without coupling yourself to the container. Invoker is automatically bound 
to `Container` so you can inject it without any configuration.


`Container::call()` can call any callable, that means:

- closures
- functions
- object methods and static methods
- invokable objects (objects that implement [__invoke()](http://php.net/manual/en/language.oop5.magic.php#object.invoke))

Additionally you can call:

- name of [invokable](http://php.net/manual/en/language.oop5.magic.php#object.invoke) classes: `$container->call('My\CallableClass')`
- object methods (give the class name, not an object): `$container->call(['MyClass', 'someMethod'])`

In both case, `'My\CallableClass'` and `'MyClass'` will be resolved by the container using `$container->get()`.

That saves you from a more verbose form, for example:

```php
$object = $container->get('My\CallableClass');
$container->call($object);

// can be written as
$container->call('My\CallableClass');
```

## injectOn()

Sometimes you want to inject dependencies on an object that is already created.

For example, some old frameworks don't allow you to control how controllers are created.
With `injectOn`, you can ask the container to fulfill the dependencies after the object is created.

Keep in mind it's usually always better to use `get()` or `make()` instead of `injectOn()`,
use it only where you really have to.

Example:

```php
class UserController extends BaseController
{
	#[Inject]
    private ?SomeService $someService;

    public function __construct()
    {
        // The framework doesn't let us control how the controller is created, so
        // we can't use the container to create the controller
        // So we ask the container to inject dependencies
        $container->injectOn($this);

        // Now the dependencies are injected
        $this->someService->doSomething();
    }
}
```

As you might have guessed, you can't use constructor injection with this method.
But other kind of injections (property or setter) will work, whether you use annotations
or whether you configured your object in a definition file.
