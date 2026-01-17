<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class VariantCanBeAddedOnlyToMainVariantException extends VariantException
{
    /**
     * @param int $productId
     * @param int $variantId
     */
    public function __construct($productId, $variantId, ?Exception $previous = null)
    {
        $message = 'Product with ID ' . $productId . ' is not main variant for add variant ID ' . $variantId . '.';

        parent::__construct($message, 0, $previous);
    }
}
