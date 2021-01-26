<?php
namespace Pluf\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

class DependencyNotFoundException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
