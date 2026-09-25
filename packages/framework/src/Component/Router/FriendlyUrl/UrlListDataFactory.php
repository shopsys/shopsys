<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class UrlListDataFactory
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
    ) {
    }

    protected function createInstance(): UrlListData
    {
        return new UrlListData();
    }

    public function create(): UrlListData
    {
        return $this->createInstance();
    }

    public function createForDomain(string $routeName, int $entityId, int $domainId): UrlListData
    {
        $urlListData = $this->createInstance();
        $urlListData->mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, $routeName, $entityId);

        return $urlListData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData[]
     */
    public function createForAllDomainsIndexedByDomainId(string $routeName, int $entityId): array
    {
        $mainFriendlyUrlsByDomainId = $this->friendlyUrlFacade->getMainFriendlyUrlsIndexedByDomains($routeName, $entityId);
        $urlListDataByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $urlListData = $this->createInstance();
            $urlListData->mainFriendlyUrl = $mainFriendlyUrlsByDomainId[$domainId] ?? null;
            $urlListDataByDomainId[$domainId] = $urlListData;
        }

        return $urlListDataByDomainId;
    }
}
