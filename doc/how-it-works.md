# How DI works

Do you want to help out improving DI? Or are you simply curious? Here is a short presentation of how DI works.

## Global architecture

The main component is the `Container` class. It can be created by a `ContainerBuilder`, which is just a helper class.

It is the entry point from the user's point of view, it is also the component that coordinates all other sub-components.

Its main role is to return **entries** by their **entry name**:

```php
$entry = $container->get('entryName');
```

A container instance has the following sub-components:

- a factory that build an entry
- a invoker that resolves factory parameters.

This is a PHP form of spring boot.

### Definitions

Pluf DI just supports `factory`, that build a new instance of entery on each call. However you may implement a factory that
returns a constant value or a service.

There are helper method to:

- Add factory
- Add value
- Add service

The entry type describes how the factory should create a class instance (what parameters the constructor takes, …).
