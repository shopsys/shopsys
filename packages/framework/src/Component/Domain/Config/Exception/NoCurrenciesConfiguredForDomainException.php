<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config\Exception;

use Exception;

class NoCurrenciesConfiguredForDomainException extends Exception
{
    public function __construct(int $domainId, ?Exception $previous = null)
    {
        parent::__construct(
            sprintf('No currencies are configured for domain with ID %d. Add the "currencies" key to the domain configuration in domains.yaml.', $domainId),
            0,
            $previous,
        );
    }
}
