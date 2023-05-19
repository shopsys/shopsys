<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\Exception;

use Exception;

class CategoryDomainNotFoundException extends Exception implements CategoryException
{
    /**
     * @param int $domainId
     * @param int|null $categoryId
     * @param \Exception|null $previous
     */
    public function __construct(int $domainId, ?int $categoryId = null, ?Exception $previous = null)
    {
        $categoryDescription = $categoryId !== null ? sprintf('with ID %d', $categoryId) : 'without ID';
        $message = sprintf(
            'CategoryDomain for category %s and domain ID %d not found.',
            $categoryDescription,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
