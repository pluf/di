# Performances

## A note about caching

With Pluf DI the main point for optimization is now to compile the container into highly optimized code.
Compiling the container is simpler and faster.

## Compiling the container

Pluf DI performs two tasks that can be expensive:

- reading definitions from your [configuration](php-definitions.md), from [autowiring](autowiring.md) or from [annotations](annotations.md)
- resolving those definitions to create your services

In order to avoid those two tasks, the container can be compiled into PHP code optimized especially for your configuration and your classes.

### Setup

Compiling the container is as easy as calling the `setEnableCompilation()` method on the container builder:

```php
$containerBuilder = new \Pluf\Di\ContainerBuilder();
$container = $containerBuilder
	->setEnableCompilation(true)
	->setCachePath(__DIR__ . '/var/cache')
	// […]
 	->build();
```

The `setEnableCompilation()` method takes the path of the directory in which to store the compiled container.

### Deployment in production

When a container is configured to be compiled, **it will be compiled once and never be regenerated again**. That allows for maximum performances in production.

When you deploy new versions of your code to production **you must delete the generated file** (or the directory that contains it) to ensure that the container is re-compiled.

If your production handles a lot of traffic you may also want to generate the compiled container *before* the new version of your code goes live. That phase is known as the "warmup" phase. To do this, simply create the container (call `$containerBuilder->build()`) during your deployment step and the compiled container will be created.

### Development environment

**Do not compile the container in a development environment**, else all the changes you make to the definitions (annotations, configuration files, etc.) will not be taken into account. Here is an example of what you can do:

```php
$containerBuilder = new \Pluf\Di\ContainerBuilder();
$container = $containerBuilder
	->setEnableCompilation(true /* is production or false */)
	->setCachePath(__DIR__ . '/var/cache')
	// […]
 	->build();
```

### Optimizing for compilation

As you can read in the "*How it works*" section, Pluf Di will take all the definitions it can find and compile them. That means that definitions like **autowired classes that are not listed in the configuration cannot be compiled** since Pluf Di doesn't know about them.

If you want to optimize performances to a maximum in exchange for more verbosity, you can let Pluf Di know about all the autowired classes by listing them in definition files:

```php
#[Component]
#[Qualifier("dbConnection")]
function createDbConnection(){
	// ...
	return $dbConnection;
}


#[Component]
#[Qualifier("configuration")]
function createDbConnection(){
	// ...
	return $configs;
}
```

Currently Pluf DI does not traverse directories to find autowired or annotated classes automatically.

It also does not resolve [wildcard definitions](php-definitions.md#wildcards) during the compilation process. Those definitions will still work perfectly, they will simply not get a performance boost when using a compiled container.

On the other hand factory definitions (either defined with closures or with class factories) are supported in the compiled container. However please note that:

- you should not use `$this` inside closures
- you should not import variables inside the closure using the `use` keyword, like in `function () use ($foo) { ...`

These limitations exist because the code of each closure is copied into the compiled container. It is safe to say that you should probably not do these things even if you do not compile the container.

## Optimizing lazy injection

If you are using the [Lazy Injection](lazy-injection.md) feature you should read the section ["Optimizing performances" of the guide](lazy-injection.md#optimizing-performances).

## Caching

Compiling the container is the most efficient solution, but it has some limits. The following cases are not optimized:

- autowired (or annotated) classes that are not declared in the configuration
- wildcard definitions
- usage of `Container::make()` or `Container::injectOn()` (because those are not using the compiled code)

If you make heavy use of those features, and if it slows down your application you can enable the caching system. The cache will ensure annotations or the reflection is not read again on every request.

The cache relies on APCu directly because it is the only cache system that makes sense (very fast to write and read). Other caches are not good options, this is why PHP-DI does not use PSR-6 or PSR-16 for this cache.

To enable the cache:

```php
$containerBuilder = new \Pluf\Di\ContainerBuilder();
$containerBuilder
	->setEnableDefinitionCache(true /* is production */)
	->build();
```

Heads up:

- do not use a cache in a development environment, else changes you make to the definitions (annotations, configuration files, etc.) may not be taken into account
- clear the APCu cache on each deployment in production (to avoid using a stale cache)
