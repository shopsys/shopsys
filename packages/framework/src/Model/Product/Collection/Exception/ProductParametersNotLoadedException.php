<?php

namespace Shopsys\FrameworkBundle\Model\Product\Collection\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductParametersNotLoadedException extends Exception implements ProductCollectionException
{
    public function __construct(Product $product, DomainConfig $domainConfig, Exception $previous = null)
    {
        $message = sprintf(
            'Parameters for product with ID %d on %s have not been loaded via ProductParametersBatchLoader::loadForProducts().',
            $product->getId(),
            $domainConfig->getName()
        );
        parent::__construct($message, 0, $previous);
    }
}
