<?php

declare(strict_types=1);

namespace App\Model\Product\Type\Exception;

use App\Model\Product\Type\ProductType;
use Exception;
use Throwable;

class ProductTypeIsBeingUsedException extends Exception implements ProductTypeException
{
    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @param \Throwable|null $previous
     */
    public function __construct(ProductType $productType, ?Throwable $previous = null)
    {
        parent::__construct(
            'ProductType with ID = `' . $productType->getId() . '` is being used, It cannot be deleted.',
            0,
            $previous
        );
    }
}
