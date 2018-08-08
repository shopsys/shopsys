<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class VariantCanBeAddedOnlyToMainVariantException extends Exception implements VariantException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(int $productId, int $variantId, Exception $previous = null)
    {
        $message = 'Product with ID ' . $productId . ' is not main variant for add variant ID ' . $variantId . '.';
        parent::__construct($message, 0, $previous);
    }
}
