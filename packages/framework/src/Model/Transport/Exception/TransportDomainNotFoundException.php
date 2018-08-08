<?php

namespace Shopsys\FrameworkBundle\Model\Transport\Exception;

use Exception;

class TransportDomainNotFoundException extends Exception implements TransportException
{
    public function __construct(int $transportId = null, int $domainId, Exception $previous = null)
    {
        $transportDescription = $transportId !== null ? sprintf('with ID %d', $transportId) : 'without ID';
        $message = sprintf('TransportDomain for transport %s and domain ID %d not found.', $transportDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
