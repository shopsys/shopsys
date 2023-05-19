<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class ProductDomainNotFoundException extends Exception implements ProductException
{
    /**
     * @param int $domainId
     * @param int|null $productId
     * @param \Exception|null $previous
     */
    public function __construct(int $domainId, ?int $productId = null, ?Exception $previous = null)
    {
        $productDescription = $productId !== null ? sprintf('with ID %d', $productId) : 'without ID';
        $message = sprintf('ProductDomain for product %s and domain ID %d not found.', $productDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
