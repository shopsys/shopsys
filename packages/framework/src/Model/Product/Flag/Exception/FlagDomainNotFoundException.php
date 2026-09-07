<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag\Exception;

use Exception;

class FlagDomainNotFoundException extends Exception
{
    public function __construct(int $domainId, ?int $flagId = null, ?Exception $previous = null)
    {
        $flagDescription = $flagId !== null ? sprintf('with ID %d', $flagId) : 'without ID';
        $message = sprintf('FlagDomain for flag %s and domain ID %d not found.', $flagDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
