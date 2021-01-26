# Understanding Dependency Injection

*Dependency injection* and *dependency injection containers* are different things:

- **dependency injection is a method** for writing better code
- **a container is a tool** to help injecting dependencies

You don't *need* a container to do dependency injection. However a container can help you.

Pluf DI is about this: making dependency injection more practical in PHP.


## The theory

In simple terms, Dependency Injection is a design pattern that helps avoid hard-coded 
dependencies for some piece of code or software.

The dependencies can be changed at run time as well as compile time. We can use 
Dependency Injection to write modular, testable and maintainable code:

- Modular: The Dependency Injection helps create completely self-sufficient classes or modules
- Testable: It helps write testable code easily eg unit tests for example
- Maintainable: Since each class becomes modular, it becomes easier to manage it



### The problem

Here is how a common code will roughly work:

* Application needs Foo (e.g. a controller), so:
* Application creates Foo
* Application calls Foo
    * Foo needs Bar (e.g. a service), so:
    * Foo creates Bar
    * Foo calls Bar
        * Bar needs Bim (a service, a repository, …), so:
        * Bar creates Bim
        * Bar does something

We have dependencies almost always in our code. Consider the following procedural example which 
is pretty common:

```php
class User 
{
    private $database = null;

    public function __construct() {
        $this->database = new database('host', 'user', 'pass', 'dbname');
    }

    public function getUsers() {
        return $this->database->getAll('users');
    }
}

$user = new User();
$user->getUsers();
```

This code has these problems:

- The class User has implicit dependency on the specific database. All dependencies should always be explicit 
not implicit. This defeats Dependency inversion principle

- If we wanted to change database credentials, we need to edit the User class which is not good; every class 
should be completely modular or black box. If we need to operate further on it, we should actually use its 
public properties and methods instead of editing it again and again. This defeats Open/closed principle

- Let's assume right now class is using MySQL as database. What if we wanted to use some other type of 
database ? You will have to modify it.

- The User class does not necessarily need to know about database connection, it should be confined to 
its own functionality only. So writing database connection code in User class doesn't make it modular. 
This defeats the Single responsibility principle. Think of this analogy: A cat knows how to meow and a 
dog knows how to woof; you cannot mix them or expect dog to say meow. Just like real world, each object 
of a class should be responsible for its own specific task.

- It would become harder to write unit tests for the User class because we are instantiating the 
database class inside its constructor so it would be impossible to write unit tests for the User 
class without also testing the database class.


### Using dependency injection

Let's see how we can easily take care of above issues by using Dependency Injection. The Dependency 
Injection is nothing but injecting a dependency explicitly. Let's re-write above class:

```php
class User 
{
    private $database = null;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function getUsers() {
        return $this->database->getAll('users');
    }
}

$database = new Database('host', 'user', 'pass', 'dbname');
$user = new User($database);
$user->getUsers();
```

Let's see if this explicit dependency injection now solves problems we mentioned above.

We have already made database dependency explicit by requiring it into the constructor of the User class.

The User class now does not need to worry about how database is connected. All it expects is Database 
instance. We no more need to edit User class for it's dependency, we have just provided it with what it needed.

Again, the User class doesn't need to know which type of database is used. For the Database, we could now 
create different adapters for different types of database and pass to User class. For example, we could 
create an interface that would enforce common methods for all different types of database classes that 
must be implement by them. For our example, we pretend that interface would enforce to have a getUser() 
method requirement in different types of database classes.

Of course User class now doesn't know how database was connected. It just needs a valid connected Database instance.

If you have wrote unit tests, you know now it will be a breeze to write tests for the User class using 
something like Mockery or similar to create mock object for the Database.

Here is how a code using DI will roughly work:

* Application needs Foo, which needs Bar, which needs Bim, so:
* Application creates Bim
* Application creates Bar and gives it Bim
* Application creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

This is the pattern of **Inversion of Control**. The control of the dependencies is **inverted** from one being called to the one calling.

The main advantage: the one at the top of the caller chain is always **you**. You can control all dependencies 
and have complete control over how your application works. You can replace a dependency by another (one you made for example).

### Using a container

Of course it would be difficult to manage dependencies manually; this is why you need a Dependency Injection Container. 
A Dependency Injection Container is something that handles dependencies for your class(es) automatically. 
If you have worked with Laravel or Symfony, you know that their components have dependencies on on other classes. 
How do they manage all of those dependencies ? Yes they use some sort of Dependency Injection Container.

Now how does a code using PHP-DI works:

* Application needs Foo so:
* Application gets Foo from the Container, so:
    * Container creates Bim
    * Container creates Bar and gives it Bim
    * Container creates Foo and gives it Bar
* Application calls Foo
    * Foo calls Bar
        * Bar does something

In short, **the container takes away all the work of creating and injecting dependencies**.


## Understanding with an example

This is a real life example comparing a classic implementation (using `new` or singletons) VS using dependency injection.

### Without dependency injection

Say you have:

```php
class GoogleMaps
{
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}
class OpenStreetMap
{
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

The classic way of doing things is:

```php
class StoreService
{
    public function getStoreCoordinates($store) {
        $geolocationService = new GoogleMaps();
        // or $geolocationService = GoogleMaps::getInstance() if you use singletons

        return $geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

Now we want to use the `OpenStreetMap` instead of `GoogleMaps`, how do we do?
We have to change the code of `StoreService`, and all the other classes that use `GoogleMaps`.

**Without dependency injection, your classes are tightly coupled to their dependencies.**

### With dependency injection

The `StoreService` now uses dependency injection:

```php
class StoreService {
    private $geolocationService;

    public function __construct(GeolocationService $geolocationService) {
        $this->geolocationService = $geolocationService;
    }

    public function getStoreCoordinates($store) {
        return $this->geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

And the services are defined using an interface:

```php
interface GeolocationService {
    public function getCoordinatesFromAddress($address);
}

class GoogleMaps implements GeolocationService { ...

class OpenStreetMap implements GeolocationService { ...
```

Now, it is for the user of the StoreService to decide which implementation to use. And it can be changed anytime, without
having to rewrite the `StoreService`.

**The `StoreService` is no longer tightly coupled to its dependency.**

## With DI

You may see that dependency injection has one drawback: you now have to handle injecting dependencies.

That's where a container, and specifically DI, can help you.

Instead of writing:

```php
$geolocationService = new GoogleMaps();
$storeService = new StoreService($geolocationService);
```

You can write:

```php
$storeService = $container->get('StoreService');
```

and configure which GeolocationService DI should automatically inject in StoreService through configuration:

```php
$container->set('GeolocationService', new GoogleMaps());
```

If you change your mind, there's just one line of configuration to change now.

Interested? Go ahead and read the [Getting started](getting-started.md) guide!


