<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Provider;

abstract class SearchResultsProvider
{
    /**
     * @param string $enabledDomainIds
     */
    public function __construct(
        protected readonly string $enabledDomainIds,
    ) {
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabledOnDomain(int $domainId): bool
    {
        $enabledDomainIds = array_map(static fn (string $domainId) => (int)$domainId, explode(',', $this->enabledDomainIds));

        return in_array($domainId, $enabledDomainIds, true);
    }
}
