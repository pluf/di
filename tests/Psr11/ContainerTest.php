<?php
namespace Pluf\Tests\Psr11;

use PHPUnit\Framework\TestCase;
use Pluf\Di\Container;
use Pluf\Tests\Fixtures\Service;

/**
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *        
 */
class ContainerTest extends TestCase
{

    public function testGetReturnsExistingService()
    {
        $psr = new Container();
        $psr['service'] = Container::service(function () {
            return new Service();
        });

        $this->assertSame($psr['service'], $psr->get('service'));
    }

    public function testGetThrowsExceptionIfServiceIsNotFound()
    {
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Identifier "service" is not defined.');

        $psr = new Container();

        $psr->get('service');
    }

    public function testHasReturnsTrueIfServiceExists()
    {
        $psr = new Container();
        $psr['service'] = function () {
            return new Service();
        };

        $this->assertTrue($psr->has('service'));
    }

    public function testHasReturnsFalseIfServiceDoesNotExist()
    {
        $psr = new Container();
        $this->assertFalse($psr->has('service'));
    }
}
