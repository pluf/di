<?php
namespace Pluf\Di\Exception;

use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

/**
 * An attempt to perform an operation that requires a service identifier was made.
 *
 * @author Mostafa Barmshory<mostafa.barmshory@gmail.com>
 */
class InvalidServiceIdentifierException extends InvalidArgumentException implements NotFoundExceptionInterface
{

    /**
     *
     * @param string $id
     *            The invalid identifier
     */
    public function __construct($id)
    {
        parent::__construct(sprintf('Identifier "%s" does not contain an object definition.', $id));
    }
}
