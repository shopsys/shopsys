<?php

namespace Shopsys\FrameworkBundle\Model\Category\Exception;

use Exception;

class CategoryDomainNotFoundException extends Exception implements CategoryException
{
    /**
     * @param int $categoryId
     * @param int $domainId
     * @param \Exception|null $previous
     */
    public function __construct(int $categoryId, int $domainId, Exception $previous = null)
    {
        $message = sprintf('CategoryDomain for category with ID %d and domain ID %d not found.', $categoryId, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
