<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Search;

interface SearchResultsProviderInterface
{
    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool;
}
