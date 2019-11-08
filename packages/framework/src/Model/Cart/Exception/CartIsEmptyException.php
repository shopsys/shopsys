<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Exception;

use Exception;

/**
 * @deprecated exception is not used and will be removed in 9.0
 */
class CartIsEmptyException extends Exception implements CartException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct('Cart is empty.', 0, $previous);
    }
}
