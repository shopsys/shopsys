<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Collection\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAdditionalServicesNotLoadedException extends Exception
{
    public function __construct(Product $product, DomainConfig $domainConfig, ?Exception $previous = null)
    {
        $message = sprintf(
            'Additional services for product with ID %d on %s have not been loaded via ProductAdditionalServicesBatchLoader::loadShownInFeedsForProducts().',
            $product->getId(),
            $domainConfig->getName(),
        );

        parent::__construct($message, 0, $previous);
    }
}
