<?php
namespace Pluf\Tests;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Di\ServiceIterator;
use Pluf\Tests\Fixtures\Service;

/**
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *        
 */
class ServiceIteratorTest extends TestCase
{

    public function testIsIterable()
    {
        $container = new Container();
        $container['service1'] = Container::service(function () {
            return new Service();
        });
        $container['service2'] = Container::service(function () {
            return new Service();
        });
        $container['service3'] = Container::service(function () {
            return new Service();
        });
        $iterator = new ServiceIterator($container, [
            'service1',
            'service2'
        ]);

        $this->assertSame([
            'service1' => $container['service1'],
            'service2' => $container['service2']
        ], iterator_to_array($iterator));
    }
}
