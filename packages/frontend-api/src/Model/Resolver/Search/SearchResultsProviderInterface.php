<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Search;

interface SearchResultsProviderInterface
{
    public function isEnabledOnDomain(int $domainId): bool;
}
