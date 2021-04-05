# Pluf DI in Silly

Silly can work with any dependency injection container. So it is possible to add Pluf DI to the silly easy.

## Installation

```bash
$ composer require mnapoli/silly
$ composer require pluf/di
```

## Usage

Simply use the `Application` class to manage the application commands.

```php
$app = new Silly\Application();
```

Thanks to Pluf DI autowiring capabilities you can define your commands in classes:

```php
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand
{
    public function __invoke($name, OutputInterface $output)
    {
        if ($name) {
            $text = 'Hello, '.$name;
        } else {
            $text = 'Hello';
        }
        $output->writeln($text);
    }
}

$app = new Silly\Application();
$container = new Continer();
$container->addFactory(MyCommand::class, function(){
	return new MyCommand();
});
$app->useContainer($container);
$app->command('greet [name]', MyCommand::class);
$app->run();
```

DI will automatically create a new instance of `MyCommand` when the `greet` command is called.

## Dependency injection in parameters

You can also use dependency injection in parameters:

```php
use Psr\Logger\LoggerInterface;

// ...

$container = $app->getContainer();

$container->addValue('dbHost', 'localhost');
// Monolog's configuration is voluntarily skipped
$container->addService(LoggerInterface::class, function(){
	return new Monolog\Logger();
});

$app->command('greet [name]', function ($name, $dbHost, LoggerInterface $logger) {
    // ...
});

$app->run();
```

Dependency injection in parameters follows the precedence rules explained in the [dependency injection](dependency-injection.md) documentation:

- command parameters are matched in priority using the parameter names (`$name`)
- then container entries are matched using the callable type-hint (`Psr\Logger\LoggerInterface`)
- finally container entries are matched using the parameter names (`$dbHost`)
