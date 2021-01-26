<?php
require_once 'vendor/autoload.php';

use Pluf\Di\Container;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand
{
    public function __invoke($name, OutputInterface $output)
    {
        if ($name) {
            $text = 'Hello, ' . $name;
        } else {
            $text = 'Hello';
        }
        $output->writeln($text);
    }
}

$app = new Silly\Application();
$container = new Container();
$container->addFactory(MyCommand::class, function () {
    return new MyCommand();
});
$app->useContainer($container);
$app->command('greet [name]', MyCommand::class);
$app->run();