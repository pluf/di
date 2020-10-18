<?php
namespace Pluf\Tests\Mock;

use Psr\Container\NotFoundExceptionInterface;

/**
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class NotFound extends \Exception implements NotFoundExceptionInterface
{
}