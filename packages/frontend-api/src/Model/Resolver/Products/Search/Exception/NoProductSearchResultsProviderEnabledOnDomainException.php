<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\Exception;

use Exception;

class NoProductSearchResultsProviderEnabledOnDomainException extends Exception
{
    /**
     * @param int $domainId
     */
    public function __construct(int $domainId)
    {
        parent::__construct(sprintf('No product search results provider enabled on domain with id "%d".', $domainId));
    }
}
