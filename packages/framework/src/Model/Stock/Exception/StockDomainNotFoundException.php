<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock\Exception;

use Exception;

class StockDomainNotFoundException extends Exception
{
    public function __construct(?int $stockId, int $domainId, ?Exception $previous = null)
    {
        $stockDescription = $stockId !== null ? sprintf('with ID "%d"', $stockId) : 'without ID';
        $message = sprintf(
            'StockDomain for stock %s and domain ID "%d" not found.',
            $stockDescription,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
