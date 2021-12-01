<?php

declare(strict_types=1);

namespace App\Model\Product\Exception;

use App\Model\Product\Product;
use Exception;

class ProductCannotBeTransformedException extends Exception
{
    /**
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Product $product)
    {
        $message = sprintf('Product with ID %d adn SKU %s cannot be transformed to main variant because is main variant or variant now.', $product->getId(), $product->getCatnum());

        parent::__construct($message);
    }
}
