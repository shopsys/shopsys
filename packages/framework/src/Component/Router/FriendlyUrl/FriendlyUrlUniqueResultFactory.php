<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FriendlyUrlUniqueResultFactory
{
    public function __construct(
        protected readonly FriendlyUrlFactory $friendlyUrlFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResult
     */
    public function create(
        int $attempt,
        FriendlyUrl $friendlyUrl,
        string $entityName,
        ?array $matchedRouteData = null,
    ) {
        if ($matchedRouteData === null && !$this->isSlugConflictingWithAnotherDomainPostfix($friendlyUrl)) {
            return new FriendlyUrlUniqueResult(true, $friendlyUrl);
        }

        if ($matchedRouteData !== null
            && $friendlyUrl->getRouteName() === $matchedRouteData['_route']
            && $friendlyUrl->getEntityId() === $matchedRouteData['id']
        ) {
            return new FriendlyUrlUniqueResult(true, null);
        }

        $newIndexedFriendlyUrl = $this->friendlyUrlFactory->createIfValid(
            $friendlyUrl->getRouteName(),
            $friendlyUrl->getEntityId(),
            (string)$entityName,
            $friendlyUrl->getDomainId(),
            $attempt + 1, // if URL is duplicate, try again with "url-2", "url-3" and so on
        );

        return new FriendlyUrlUniqueResult(false, $newIndexedFriendlyUrl);
    }

    protected function isSlugConflictingWithAnotherDomainPostfix(FriendlyUrl $friendlyUrl): bool
    {
        $inputDomainConfig = $this->domain->getDomainConfigById($friendlyUrl->getDomainId());
        $domainsWithSameBaseUrl = $this->domain->getAllWithSameBaseUrl($inputDomainConfig);

        foreach ($domainsWithSameBaseUrl as $domainConfig) {
            if ($domainConfig->getId() === $inputDomainConfig->getId()) {
                continue;
            }

            $postfix = $domainConfig->getPostfix();

            if ($postfix !== null && ($friendlyUrl->getSlug() === trim($postfix, '/'))) {
                return true;
            }
        }

        return false;
    }
}
