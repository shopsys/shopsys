<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Exception;

use Exception;

class MissingBaseFriendlyUrlForDomainException extends Exception implements ProductSeriesException
{
    /**
     * @param mixed $domainName
     */
    public function __construct($domainName)
    {
        parent::__construct('Missing base friendly url for domain ' . $domainName . '. You have to add row with url part to "ProductSeries::BASE_FRIENDY_URL"');
    }
}
