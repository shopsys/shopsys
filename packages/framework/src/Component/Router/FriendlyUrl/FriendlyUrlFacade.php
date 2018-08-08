<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

class FriendlyUrlFacade
{
    const MAX_URL_UNIQUE_RESOLVE_ATTEMPT = 100;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlService
     */
    protected $friendlyUrlService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    protected $friendlyUrlRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface
     */
    protected $friendlyUrlFactory;

    public function __construct(
        EntityManagerInterface $em,
        DomainRouterFactory $domainRouterFactory,
        FriendlyUrlService $friendlyUrlService,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        FriendlyUrlFactoryInterface $friendlyUrlFactory
    ) {
        $this->em = $em;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->friendlyUrlService = $friendlyUrlService;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->domain = $domain;
        $this->friendlyUrlFactory = $friendlyUrlFactory;
    }

    /**
     * @param string[] $namesByLocale
     */
    public function createFriendlyUrls(string $routeName, int $entityId, array $namesByLocale): void
    {
        $friendlyUrls = $this->friendlyUrlService->createFriendlyUrls($routeName, $entityId, $namesByLocale);
        foreach ($friendlyUrls as $friendlyUrl) {
            $locale = $this->domain->getDomainConfigById($friendlyUrl->getDomainId())->getLocale();
            $this->resolveUniquenessOfFriendlyUrlAndFlush($friendlyUrl, $namesByLocale[$locale]);
        }
    }

    public function createFriendlyUrlForDomain(string $routeName, int $entityId, string $entityName, int $domainId): void
    {
        $friendlyUrl = $this->friendlyUrlService->createFriendlyUrlIfValid($routeName, $entityId, $entityName, $domainId);
        if ($friendlyUrl !== null) {
            $this->resolveUniquenessOfFriendlyUrlAndFlush($friendlyUrl, $entityName);
        }
    }

    protected function resolveUniquenessOfFriendlyUrlAndFlush(FriendlyUrl $friendlyUrl, string $entityName): void
    {
        $attempt = 0;
        do {
            $attempt++;
            if ($attempt > self::MAX_URL_UNIQUE_RESOLVE_ATTEMPT) {
                throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\ReachMaxUrlUniqueResolveAttemptException(
                    $friendlyUrl,
                    $attempt
                );
            }

            $domainRouter = $this->domainRouterFactory->getRouter($friendlyUrl->getDomainId());
            try {
                $matchedRouteData = $domainRouter->match('/' . $friendlyUrl->getSlug());
            } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
                $matchedRouteData = null;
            }

            $friendlyUrlUniqueResult = $this->friendlyUrlService->getFriendlyUrlUniqueResult(
                $attempt,
                $friendlyUrl,
                $entityName,
                $matchedRouteData
            );
            $friendlyUrl = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        } while (!$friendlyUrlUniqueResult->isUnique());

        if ($friendlyUrl !== null) {
            $this->em->persist($friendlyUrl);
            $this->em->flush($friendlyUrl);
            $this->setFriendlyUrlAsMain($friendlyUrl);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId(string $routeName, int $entityId): array
    {
        return $this->friendlyUrlRepository->getAllByRouteNameAndEntityId($routeName, $entityId);
    }

    public function findMainFriendlyUrl(int $domainId, string $routeName, int $entityId): ?\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
    {
        return $this->friendlyUrlRepository->findMainFriendlyUrl($domainId, $routeName, $entityId);
    }

    public function saveUrlListFormData(string $routeName, int $entityId, UrlListData $urlListData): void
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
            $this->em->flush($toFlush);
        }
    }

    protected function setFriendlyUrlAsMain(FriendlyUrl $mainFriendlyUrl): void
    {
        $friendlyUrls = $this->friendlyUrlRepository->getAllByRouteNameAndEntityIdAndDomainId(
            $mainFriendlyUrl->getRouteName(),
            $mainFriendlyUrl->getEntityId(),
            $mainFriendlyUrl->getDomainId()
        );
        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrl->setMain(false);
        }
        $mainFriendlyUrl->setMain(true);

        $this->em->flush($friendlyUrls);
    }
}
