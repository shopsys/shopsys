<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class ProductIsAlreadyVariantException extends VariantException
{
    /**
     * @param int $productId
     */
    public function __construct($productId, ?Exception $previous = null)
    {
        $message = 'Product with ID ' . $productId . ' is already variant.';

        parent::__construct($message, 0, $previous);
    }
}
