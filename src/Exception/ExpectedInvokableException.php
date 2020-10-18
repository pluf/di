<?php
namespace Pluf\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * A closure or invokable object was expected.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class ExpectedInvokableException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
