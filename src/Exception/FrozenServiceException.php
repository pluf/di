<?php
namespace Pluf\Di\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * An attempt to modify a frozen service was made.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class FrozenServiceException extends RuntimeException implements ContainerExceptionInterface
{

    /**
     *
     * @param string $id
     *            Identifier of the frozen service
     */
    public function __construct($id)
    {
        parent::__construct(sprintf('Cannot override frozen service "%s".', $id));
    }
}
