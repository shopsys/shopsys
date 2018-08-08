<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class FriendlyUrlService
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface
     */
    protected $friendlyUrlFactory;

    public function __construct(Domain $domain, FriendlyUrlFactoryInterface $friendlyUrlFactory)
    {
        $this->domain = $domain;
        $this->friendlyUrlFactory = $friendlyUrlFactory;
    }

    /**
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function createFriendlyUrls(string $routeName, int $entityId, $namesByLocale): array
    {
        $friendlyUrls = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            if (array_key_exists($domainConfig->getLocale(), $namesByLocale)) {
                $friendlyUrl = $this->createFriendlyUrlIfValid(
                    $routeName,
                    $entityId,
                    $namesByLocale[$domainConfig->getLocale()],
                    $domainConfig->getId()
                );

                if ($friendlyUrl !== null) {
                    $friendlyUrls[] = $friendlyUrl;
                }
            }
        }

        return $friendlyUrls;
    }

    /**
     * @param array|null $matchedRouteData
     */
    public function getFriendlyUrlUniqueResult(
        int $attempt,
        FriendlyUrl $friendlyUrl,
        string $entityName,
        array $matchedRouteData = null
    ): \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResult {
        if ($matchedRouteData === null) {
            return new FriendlyUrlUniqueResult(true, $friendlyUrl);
        }

        if ($friendlyUrl->getRouteName() === $matchedRouteData['_route']
            && $friendlyUrl->getEntityId() === $matchedRouteData['id']
        ) {
            return new FriendlyUrlUniqueResult(true, null);
        }

        $newIndexedFriendlyUrl = $this->createFriendlyUrlIfValid(
            $friendlyUrl->getRouteName(),
            $friendlyUrl->getEntityId(),
            $entityName,
            $friendlyUrl->getDomainId(),
            $attempt + 1 // if URL is duplicate, try again with "url-2", "url-3" and so on
        );

        return new FriendlyUrlUniqueResult(false, $newIndexedFriendlyUrl);
    }

    public function createFriendlyUrlIfValid(
        string $routeName,
        int $entityId,
        string $entityName,
        int $domainId,
        ?int $indexPostfix = null
    ): ?\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl {
        if ($entityName !== null
            && $entityName !== ''
        ) {
            $nameForUrl = $entityName . ($entityName === null ? '' : '-' . $indexPostfix);
            $slug = TransformString::stringToFriendlyUrlSlug($nameForUrl) . '/';

            return $this->friendlyUrlFactory->create($routeName, $entityId, $domainId, $slug);
        }

        return null;
    }

    public function getAbsoluteUrlByFriendlyUrl(FriendlyUrl $friendlyUrl): string
    {
        $domainConfig = $this->domain->getDomainConfigById($friendlyUrl->getDomainId());

        return $domainConfig->getUrl() . '/' . $friendlyUrl->getSlug();
    }
    
    public function getAbsoluteUrlByDomainConfigAndSlug(DomainConfig $domainConfig, string $slug): string
    {
        return $domainConfig->getUrl() . '/' . $slug;
    }
}
