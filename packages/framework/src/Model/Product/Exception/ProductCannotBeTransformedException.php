<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCannotBeTransformedException extends Exception
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function __construct(Product $product)
    {
        $message = sprintf('Product with ID "%d" and catalog number "%s" cannot be transformed to main variant because is main variant or variant now.', $product->getId(), $product->getCatnum());

        parent::__construct($message);
    }
}
