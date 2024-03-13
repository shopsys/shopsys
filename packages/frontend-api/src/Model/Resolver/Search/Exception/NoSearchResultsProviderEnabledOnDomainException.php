<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Search\Exception;

use Exception;

class NoSearchResultsProviderEnabledOnDomainException extends Exception
{
    /**
     * @param int $domainId
     * @param string $searchedEntityName
     */
    public function __construct(int $domainId, string $searchedEntityName)
    {
        parent::__construct(sprintf('No %s search results provider enabled on domain with id "%d".', $searchedEntityName, $domainId));
    }
}
