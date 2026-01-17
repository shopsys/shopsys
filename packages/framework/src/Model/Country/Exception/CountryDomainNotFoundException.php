<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country\Exception;

use Exception;

class CountryDomainNotFoundException extends Exception
{
    public function __construct(int $domainId, ?int $countryId = null, ?Exception $previous = null)
    {
        $countryDescription = $countryId !== null ? sprintf('with ID %d', $countryId) : 'without ID';
        $message = sprintf('CountryDomain for country %s and domain ID %d not found.', $countryDescription, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
