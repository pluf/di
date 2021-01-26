# PHP Dependecy Injection

The goal of the Pluf Dependency Injection (DI) is to free a business developer from the responsibility
for obtaining objects that they need for its operation (which is called 
(separation of concerns)[https://en.wikipedia.org/wiki/Separation_of_concerns]).
Pluf DI is one of the most interesting parts of the framework. It is a compiled DI container, an important
part of the platform which is used directly in data-flow processing and workflows.

* [Getting started](doc/getting-started.md)
* [Understanding dependency injection](doc/understanding-di.md)
* [Best practices guide](doc/best-practices.md)

## Usage

The container is responsible to hold all services, objects, or values and serve them to others. There are many
configurations to manage the container and its behaviors. The container can support up-down dependency and simulates
a dependency scope. However, the easiest way to create a container is:

```php
$container = new Pluf\Contaienr();
```

So that is ready to use. For more information about how to create and manage a container read the following documents.

* [Configure the container](doc/container-configuration.md)
* [Use the container](doc/container.md)

## Definitions

Pluf DI works based on factories. A factory is a piece of code that creates a new instance of an object, service, or 
value. There are many ways to add a factory into the container. The simplest way is to define a factory
directly as follow:

```php
$container = new Pluf\Contaienr();
$container['dbConnection'] = function(){
	$dbConnection = ....;
	return $dbConnection;
};
```

To let Pluf DI know what to inject and where, you have several options:

- use [autowiring](doc/autowiring.md)
- use [annotations](doc/annotations.md)
- use [PHP definitions](doc/pphp-definitions.md)

You can also use several or all these options at the same time if you want to.

If you combine several sources, there are priorities that apply. From the highest priority to the least:

- Explicit definition on the container (i.e. defined with `$container->set()`)
- PHP file definitions (if you add several configuration files, then the last one can override entries from the previous ones)
- Annotations
- Autowiring

Read more in the [Definition overriding documentation](definition-overriding.md)

## Frameworks integration

Pluf DI is nothing itself except a DI contaienr manager. It is valueable if can combine with existed frameworks and manages there dependencies.
While Pluf DI implements standard PSR container interface, we add direct sopports of some common frameworks.

- [Symfony](doc/frameworks/symfony2.md)
- [Silex](doc/frameworks/silex.md)
- [Zend Framework 1](doc/frameworks/zf1.md)
- [Zend Framework 2](doc/frameworks/zf2.md)
- [Zend Expressive](doc/frameworks/zend-expressive.md)
- [Slim](doc/frameworks/slim.md)
- [Silly](doc/frameworks/silly.md)

## Advanced topics

* [Performances](doc/performances.md)
* [Lazy injection](doc/lazy-injection.md)
* [Inject on an existing instance](doc/inject-on-instance.md)
* [Injections depending on the environment](doc/environments.md)
* [IDE integration](doc/ide-integration.md)

## Internals

* [Contribute](CONTRIBUTING.md)
* [How Pluf DI works](doc/how-it-works.md)

## License

This project is released under the GNU GENERAL PUBLIC LICENSE V3 license. For more information see the [License file](LICENSE).

This documentation is also embedded in [Pluf DI's git repository](https://github.com/pluf/di/tree/master/doc)
so you can read it offline (in the `doc/` folder).


