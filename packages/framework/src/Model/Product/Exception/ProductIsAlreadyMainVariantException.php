<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class ProductIsAlreadyMainVariantException extends Exception implements VariantException
{
    /**
     * @param int $productId
     * @param \Exception|null $previous
     */
    public function __construct($productId, Exception $previous = null)
    {
        $message = 'Product with ID ' . $productId . ' is already main variant.';
        parent::__construct($message, 0, $previous);
    }
}
