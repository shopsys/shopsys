<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService\Exception;

use Exception;

class AdditionalServiceDomainNotFoundException extends Exception
{
    public function __construct(int $domainId, ?int $additionalServiceId = null, ?Exception $previous = null)
    {
        $additionalServiceDescription = $additionalServiceId !== null ? sprintf('with ID %d', $additionalServiceId) : 'without ID';
        $message = sprintf('AdditionalServiceDomain for additional service %s and domain ID %d not found.', $additionalServiceDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
