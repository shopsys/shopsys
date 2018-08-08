<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class ProductIsAlreadyMainVariantException extends Exception implements VariantException
{
    public function __construct(int $productId, Exception $previous = null)
    {
        $message = 'Product with ID ' . $productId . ' is already main variant.';
        parent::__construct($message, 0, $previous);
    }
}
