<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Exception;

use Exception;

class TransportDomainNotFoundException extends Exception implements TransportException
{
    /**
     * @param int $domainId
     * @param int|null $transportId
     * @param \Exception|null $previous
     */
    public function __construct(int $domainId, ?int $transportId = null, ?Exception $previous = null)
    {
        $transportDescription = $transportId !== null ? sprintf('with ID %d', $transportId) : 'without ID';
        $message = sprintf(
            'TransportDomain for transport %s and domain ID %d not found.',
            $transportDescription,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
