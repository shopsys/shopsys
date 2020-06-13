<?php

declare(strict_types=1);

namespace App\Model\Product\Exception;

use App\Model\Product\Product;

class DeleteDefaultVariantException extends \Exception
{
    /**
     * @param \App\Model\Product\Product $product
     */
    public function __construct(Product $product)
    {
        $message = sprintf('Unable to delete default variant (product id %d). First, set another variation as the default. (available in Akeneo)', $product->getId());
        parent::__construct($message);
    }
}
