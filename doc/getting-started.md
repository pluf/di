# Getting started with Pluf DI (PHP DI)

Before start, you need to know what dependency injection is. If you don't, there's a whole article 
dedicated to it: [Understanding dependency injection](understanding-di.md).

## Installation

Install it with [Composer](http://getcomposer.org/doc/00-intro.md):

```
composer require pluf/di
```

Pluf requires PHP 7.2 or above. This project is a fresh one, we may refactor to PHP 8.0 very soon.

## Basic usage

### Use dependency injection

First, let's write code using dependency injection without thinking about DI:

```php
class Mailer
{
    public function mail($recipient, $content)
    {
        // send an email to the recipient
    }
}
```

```php
class UserManager
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function register($email, $password)
    {
        // The user just registered, we create his account
        // ...

        // We send him an email to say hello!
        $this->mailer->mail($email, 'Hello and welcome!');
    }
}
```

As we can see, the `UserManager` takes the `Mailer` as a constructor parameter. So developers 
have to create a new instance of the Mailer and pass it into the UserManager. This is a kind of a dependency from 
UserManager into the Mailer class.

### Create the container

You can create a container instance pre-configured for development very easily:

```php
$container = new Pluf\Di\Container();
```

### Create the objects

Without DI, we would have to "wire" the dependencies manually like this:

```php
$mailer = new Mailer();
$userManager = new UserManager($mailer);
```

Instead, we can let DI figure out the dependencies:

```php
$userManager = $container->get(UserManager::class);
```

Behind the scenes, DI will create both a Mailer object and a UserManager object.

> How does it know what to inject?

The container uses a technique called **autowiring**. This is not unique to DI, but this is still awesome.
It will scan the code and see what are the parameters needed in the constructors.

In our example, the `UserManager` constructor takes a `Mailer` object: DI knows that it needs to 
create one. Pretty basic, but very efficient.

> Isn't that weird and risky to scan PHP code like that?

Don't worry, DI uses [PHP's Reflection classes](http://php.net/manual/en/book.reflection.php) which is 
pretty standard: Laravel, Zend Framework and many other containers do the same. Performance wise, 
such information is read once and then cached, it has no impact.

## Defining injections

We have seen **autowiring**, which is when DI figures out automatically the dependencies a class needs. But we 
have many ways to define what to inject in a class:

- using [autowiring](autowiring.md)
- using [annotations](annotations.md)
- using [PHP definitions](php-definitions.md)
- using [PHP default values](php-definitions-default.md)

Every one of them is different and optional. 

It is possible to add a new resolver or disable any of them.

Here is an example of PHP definitions:

```php
$builder = new Pluf\Di\ContainerBuilder();
$builder
	->addResolver(new Pluf\Di\ParameterResolver\TypeHintResolver())
	->addResolver(new Pluf\Di\ParameterResolver\DefaultValueResolver());
$container = $builder->build();
```

Please read the [Defining injections](definition.md) documentation to learn about autowiring, annotations and
the other type of injections.

## Framework integration

In the example above the container is used to get objects:

```php
$userManager = $container->get('UserManager');
```

However we don't want to call the container everywhere in our application: 
it would **couple our code to the container**. 
This is known as the *service locator antipattern* - or dependency *fetching* rather than *injection*.

To quote the Symfony documentation:

> You will need to get [an object] from the container at some point but this should be as few times 
as possible at the entry point to your application.

For this reason, DI integrates with some frameworks so that you don't have to call the 
container (dependencies are injected in controllers):

- [Symfony](frameworks/symfony2.md)
- [Slim](frameworks/slim.md)
- [Silex](frameworks/silex.md)
- [Zend Framework 2](frameworks/zf2.md)
- [Silly](frameworks/silly.md)

If you want to use DI with another framework or your own code, try to use `$container->get()` in 
you root application class or front controller. Have a look at this 
[**demo application**](https://github.com/pluf/di/tree/master/demo) built around DI for a practical example.

## What's next

You can head over to [the documentation index](README.md). You can also read the [Best practices guide](best-practices.md), 
it's a good way to get a good view on when to use each of DI's features.

Here are some other topics that might interest you right now:

- [Configuring the container](container-configuration.md)
- [Defining injections](definition.md)
