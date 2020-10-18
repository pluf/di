<?php
namespace Pluf\Tests\Fixtures;

/**
 * 
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 *
 */
class Invokable
{

    public function __invoke($value = null)
    {
        $service = new Service();
        $service->value = $value;

        return $service;
    }
}
