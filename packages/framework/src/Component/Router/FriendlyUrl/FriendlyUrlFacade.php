<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\ReachMaxUrlUniqueResolveAttemptException;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlFacade
{
    protected const int MAX_URL_UNIQUE_RESOLVE_ATTEMPT = 100;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFactory $friendlyUrlFactory,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string[] $namesByLocale
     */
    public function createFriendlyUrls($routeName, $entityId, array $namesByLocale): void
    {
        $friendlyUrls = $this->friendlyUrlFactory->createForAllDomains($routeName, $entityId, $namesByLocale);

        foreach ($friendlyUrls as $friendlyUrl) {
            $locale = $this->domain->getDomainConfigById($friendlyUrl->getDomainId())->getLocale();
            $this->resolveUniquenessOfFriendlyUrl($friendlyUrl, $namesByLocale[$locale]);
        }

        $this->em->flush();
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     */
    public function createFriendlyUrlForDomain($routeName, $entityId, $entityName, $domainId): void
    {
        $friendlyUrl = $this->friendlyUrlFactory->createIfValid($routeName, $entityId, (string)$entityName, $domainId);

        if ($friendlyUrl !== null) {
            $this->resolveUniquenessOfFriendlyUrl($friendlyUrl, $entityName);
        }

        $this->em->flush();
    }

    /**
     * @param string $entityName
     */
    protected function resolveUniquenessOfFriendlyUrl(FriendlyUrl $friendlyUrl, $entityName): void
    {
        $attempt = 0;

        do {
            $attempt++;

            if ($attempt > static::MAX_URL_UNIQUE_RESOLVE_ATTEMPT) {
                throw new ReachMaxUrlUniqueResolveAttemptException(
                    $friendlyUrl,
                    $attempt,
                );
            }

            $domainRouter = $this->domainRouterFactory->getRouter($friendlyUrl->getDomainId());

            try {
                $matchedRouteData = $domainRouter->match('/' . $friendlyUrl->getSlug());
            } catch (ResourceNotFoundException $e) {
                $matchedRouteData = null;
            }

            $friendlyUrlUniqueResult = $this->friendlyUrlUniqueResultFactory->create(
                $attempt,
                $friendlyUrl,
                (string)$entityName,
                $matchedRouteData,
            );
            $friendlyUrl = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        } while (!$friendlyUrlUniqueResult->isUnique());

        if ($friendlyUrl === null) {
            return;
        }

        $this->em->persist($friendlyUrl);
        $this->setFriendlyUrlAsMain($friendlyUrl);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId($routeName, $entityId)
    {
        return $this->friendlyUrlRepository->getAllByRouteNameAndEntityId($routeName, $entityId);
    }

    /**
     * @param int[] $domainIds
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameDomainIdsAndEntityIds(string $routeName, int $entityId, array $domainIds): array
    {
        return $this->friendlyUrlRepository->getAllByRouteNameDomainIdsAndEntityIds($routeName, $entityId, $domainIds);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        return $this->friendlyUrlRepository->findMainFriendlyUrl($domainId, $routeName, $entityId);
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null>
     */
    public function getMainFriendlyUrlsIndexedByDomains(string $routeName, int $entityId): array
    {
        return $this->friendlyUrlRepository->getMainFriendlyUrlsIndexedByDomains($routeName, $entityId);
    }

    public function getAbsoluteUrlByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): string
    {
        $mainFriendlyUrlSlug = $this->getMainFriendlyUrlSlug($domainId, $routeName, $entityId);
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($mainFriendlyUrlSlug === SeoPage::SEO_PAGE_HOMEPAGE_SLUG) {
            return $domainConfig->getUrl();
        }

        return $domainConfig->getUrl() . '/' . $mainFriendlyUrlSlug;
    }

    public function getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain(string $routeName, int $entityId): string
    {
        return $this->getAbsoluteUrlByRouteNameAndEntityId($this->domain->getId(), $routeName, $entityId);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     */
    public function saveUrlListFormData($routeName, $entityId, UrlListData $urlListData): void
    {
        $toFlush = [];

        foreach ($urlListData->mainFriendlyUrlsByDomainId as $friendlyUrl) {
            if ($friendlyUrl !== null) {
                $this->setFriendlyUrlAsMain($friendlyUrl);
                $toFlush[] = $friendlyUrl;
            }
        }

        foreach ($urlListData->toDelete as $friendlyUrls) {
            foreach ($friendlyUrls as $friendlyUrl) {
                if (!$friendlyUrl->isMain()) {
                    $this->em->remove($friendlyUrl);
                    $toFlush[] = $friendlyUrl;
                }
            }
        }

        foreach ($urlListData->newUrls as $urlData) {
            $domainId = $urlData[UrlListData::FIELD_DOMAIN];
            $newSlug = $urlData[UrlListData::FIELD_SLUG];
            $newFriendlyUrl = $this->friendlyUrlFactory->create($routeName, $entityId, $domainId, $newSlug);
            $this->em->persist($newFriendlyUrl);
            $toFlush[] = $newFriendlyUrl;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    protected function setFriendlyUrlAsMain(FriendlyUrl $mainFriendlyUrl): void
    {
        $friendlyUrls = $this->friendlyUrlRepository->getAllByRouteNameAndEntityIdAndDomainId(
            $mainFriendlyUrl->getRouteName(),
            $mainFriendlyUrl->getEntityId(),
            $mainFriendlyUrl->getDomainId(),
        );

        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrl->setMain(false);
        }
        $mainFriendlyUrl->setMain(true);
        $this->renewMainFriendlyUrlSlugCache($mainFriendlyUrl);
    }

    public function getAbsoluteUrlByFriendlyUrl(FriendlyUrl $friendlyUrl): string
    {
        $domainConfig = $this->domain->getDomainConfigById($friendlyUrl->getDomainId());

        return $domainConfig->getUrl() . '/' . $friendlyUrl->getSlug();
    }

    protected function renewMainFriendlyUrlSlugCache(FriendlyUrl $mainFriendlyUrl): void
    {
        $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(
            $mainFriendlyUrl->getRouteName(),
            $mainFriendlyUrl->getDomainId(),
            $mainFriendlyUrl->getEntityId(),
        );
        $this->mainFriendlyUrlSlugCache->delete($cacheKey);
        $this->mainFriendlyUrlSlugCache->get($cacheKey, function () use ($mainFriendlyUrl) {
            return $mainFriendlyUrl->getSlug();
        });
    }

    public function getMainFriendlyUrl(int $domainId, string $routeName, int $entityId): FriendlyUrl
    {
        $friendlyUrl = $this->findMainFriendlyUrl($domainId, $routeName, $entityId);

        if ($friendlyUrl === null) {
            throw new FriendlyUrlNotFoundException(sprintf('Main friendly URL not found for route "%s", domain ID "%d", and entity ID "%d".', $routeName, $domainId, $entityId));
        }

        return $friendlyUrl;
    }

    public function getMainFriendlyUrlSlug(int $domainId, string $routeName, int $entityId): string
    {
        $cacheKey = $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(
            $routeName,
            $domainId,
            $entityId,
        );

        /** @var string|null $friendlyUrlSlug */
        $friendlyUrlSlug = $this->mainFriendlyUrlSlugCache->get($cacheKey, function () use ($domainId, $routeName, $entityId) {
            $friendlyUrl = $this->friendlyUrlRepository->findMainFriendlyUrl($domainId, $routeName, $entityId);

            return $friendlyUrl?->getSlug();
        });

        if ($friendlyUrlSlug === null) {
            throw new FriendlyUrlNotFoundException(sprintf('Main friendly URL not found for route "%s", domain ID "%d", and entity ID "%d".', $routeName, $domainId, $entityId));
        }

        return $friendlyUrlSlug;
    }

    /**
     * @return string[]
     */
    public function getAllSlugsByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): array
    {
        return $this->friendlyUrlRepository->getAllSlugsByRouteNameAndDomainId($domainId, $routeName, $entityId);
    }

    public function findByDomainIdAndSlug(int $domainId, string $slug): ?FriendlyUrl
    {
        return $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);
    }

    public function getEntityClassByRouteName(string $routeName): string
    {
        $routeNameMapping = $this->friendlyUrlRepository->getRouteNameToEntityMap();

        if (array_key_exists($routeName, $routeNameMapping)) {
            return $routeNameMapping[$routeName];
        }

        throw new FriendlyUrlNotFoundException();
    }

    /**
     * @return array<int, string>
     */
    public function getUndefinedRouteNamesInMapping(): array
    {
        $routeNameMapping = $this->friendlyUrlRepository->getRouteNameToEntityMap();
        $allUsedRouteNames = $this->friendlyUrlRepository->getAllRouteNames();

        $undefinedRouteNameMappings = [];

        foreach ($allUsedRouteNames as $usedRouteName) {
            if (!array_key_exists($usedRouteName, $routeNameMapping)) {
                $undefinedRouteNameMappings[] = $usedRouteName;
            }
        }

        return $undefinedRouteNameMappings;
    }

    public function setRedirect(int $domainId, string $slug, FriendlyUrlData $friendlyUrlData): void
    {
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);

        if ($friendlyUrl === null) {
            return;
        }

        $friendlyUrl->setRedirectCode($friendlyUrlData->redirectCode);
        $friendlyUrl->setRedirectTo($friendlyUrlData->redirectTo);
        $friendlyUrl->setLastModification($this->clock->now());
        $this->em->flush();
    }

    public function getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
        int $domainId,
        QuickSearchFormData $quickSearchFormData,
    ): QueryBuilder {
        return $this->friendlyUrlRepository->getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
            $domainId,
            $quickSearchFormData,
        );
    }

    public function removeFriendlyUrl(int $domainId, string $slug): void
    {
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);

        if ($friendlyUrl === null) {
            return;
        }

        $this->em->remove($friendlyUrl);
        $this->em->flush();
    }
}
