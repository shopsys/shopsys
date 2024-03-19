<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List\Exception;

use Exception;

class UnknownProductListTypeException extends Exception
{
    /**
     * @param string $productListType
     */
    public function __construct(string $productListType)
    {
        parent::__construct(sprintf('Unknown product list type "%s"', $productListType));
    }
}
