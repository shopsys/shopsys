<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\ReachMaxUrlUniqueResolveAttemptException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlFacade
{
    protected const MAX_URL_UNIQUE_RESOLVE_ATTEMPT = 100;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface $friendlyUrlFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider
     * @param \Symfony\Contracts\Cache\CacheInterface $mainFriendlyUrlSlugCache
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFactoryInterface $friendlyUrlFactory,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
    ) {
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string[] $namesByLocale
     */
    public function createFriendlyUrls($routeName, $entityId, array $namesByLocale)
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
    public function createFriendlyUrlForDomain($routeName, $entityId, $entityName, $domainId)
    {
        $friendlyUrl = $this->friendlyUrlFactory->createIfValid($routeName, $entityId, (string)$entityName, $domainId);

        if ($friendlyUrl !== null) {
            $this->resolveUniquenessOfFriendlyUrl($friendlyUrl, $entityName);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param string $entityName
     */
    protected function resolveUniquenessOfFriendlyUrl(FriendlyUrl $friendlyUrl, $entityName)
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
     * @param string $routeName
     * @param int $entityId
     * @return array<int, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null>
     */
    public function getMainFriendlyUrlsIndexedByDomains(string $routeName, int $entityId): array
    {
        return $this->friendlyUrlRepository->getMainFriendlyUrlsIndexedByDomains($routeName, $entityId);
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    public function getAbsoluteUrlByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): string
    {
        $mainFriendlyUrl = $this->findMainFriendlyUrl($domainId, $routeName, $entityId);

        if ($mainFriendlyUrl === null) {
            throw new FriendlyUrlNotFoundException();
        }

        return $this->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    public function getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain(string $routeName, int $entityId): string
    {
        return $this->getAbsoluteUrlByRouteNameAndEntityId($this->domain->getId(), $routeName, $entityId);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData $urlListData
     */
    public function saveUrlListFormData($routeName, $entityId, UrlListData $urlListData)
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl
     */
    protected function setFriendlyUrlAsMain(FriendlyUrl $mainFriendlyUrl)
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @return string
     */
    public function getAbsoluteUrlByFriendlyUrl(FriendlyUrl $friendlyUrl): string
    {
        $domainConfig = $this->domain->getDomainConfigById($friendlyUrl->getDomainId());

        return $domainConfig->getUrl() . '/' . $friendlyUrl->getSlug();
    }

    /**
     * @param string $routeName
     * @param int $entityId
     */
    public function removeFriendlyUrlsForAllDomains(string $routeName, int $entityId): void
    {
        foreach ($this->getAllByRouteNameAndEntityId($routeName, $entityId) as $friendlyUrl) {
            $this->em->remove($friendlyUrl);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl
     */
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

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    public function getMainFriendlyUrl(int $domainId, string $routeName, int $entityId): FriendlyUrl
    {
        $friendlyUrl = $this->findMainFriendlyUrl($domainId, $routeName, $entityId);

        if ($friendlyUrl === null) {
            throw new FriendlyUrlNotFoundException(sprintf('Main friendly URL not found for route "%s", domain ID "%d", and entity ID "%d".', $routeName, $domainId, $entityId));
        }

        return $friendlyUrl;
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
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
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string[]
     */
    public function getAllSlugsByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): array
    {
        return $this->friendlyUrlRepository->getAllSlugsByRouteNameAndDomainId($domainId, $routeName, $entityId);
    }

    /**
     * @param int $domainId
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findByDomainIdAndSlug(int $domainId, string $slug): ?FriendlyUrl
    {
        return $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);
    }
}
