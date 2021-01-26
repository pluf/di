<?php
namespace Pluf\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

class DependencyException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
