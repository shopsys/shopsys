<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\Exception;

use Exception;

class StoreDomainNotFoundException extends Exception implements StoreException
{
    /**
     * @param int $domainId
     * @param int|null $storeId
     * @param \Exception|null $previous
     */
    public function __construct(int $domainId, ?int $storeId = null, ?Exception $previous = null)
    {
        $storeDescription = $storeId !== null ? sprintf('with ID %d', $storeId) : 'without ID';
        $message = sprintf(
            'StoreDomain for store %s and domain ID %d not found.',
            $storeDescription,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
