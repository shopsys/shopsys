<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade as BaseFriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory;

class FriendlyUrlFacade extends BaseFriendlyUrlFacade
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlCacheFacade
     */
    private $friendlyUrlCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFactory $friendlyUrlFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlCacheFacade $friendlyUrlCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        DomainRouterFactory $domainRouterFactory,
        FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        FriendlyUrlFactoryInterface $friendlyUrlFactory,
        FriendlyUrlCacheFacade $friendlyUrlCacheFacade
    ) {
        parent::__construct(
            $em,
            $domainRouterFactory,
            $friendlyUrlUniqueResultFactory,
            $friendlyUrlRepository,
            $domain,
            $friendlyUrlFactory
        );
        $this->friendlyUrlCacheFacade = $friendlyUrlCacheFacade;
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     * @param string[] $prefixes
     */
    public function createFriendlyUrlForDomain($routeName, $entityId, $entityName, $domainId, $prefixes = [])
    {
        /** @var \App\Component\Router\FriendlyUrl\FriendlyUrlFactory $friendlyUrlFactory */
        $friendlyUrlFactory = $this->friendlyUrlFactory;
        $friendlyUrl = $friendlyUrlFactory->createFromPartsIfValid($routeName, $entityId, (string)$entityName, $domainId, null, $prefixes);
        if ($friendlyUrl !== null) {
            $this->resolveUniquenessOfFriendlyUrlAndFlush($friendlyUrl, $entityName, $prefixes);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param string $entityName
     * @param string[] $prefixes
     */
    protected function resolveUniquenessOfFriendlyUrlAndFlush(FriendlyUrl $friendlyUrl, $entityName, $prefixes = [])
    {
        $attempt = 0;
        do {
            $attempt++;
            if ($attempt > static::MAX_URL_UNIQUE_RESOLVE_ATTEMPT) {
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

            /** @var \App\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory */
            $friendlyUrlUniqueResultFactory = $this->friendlyUrlUniqueResultFactory;
            $friendlyUrlUniqueResult = $friendlyUrlUniqueResultFactory->create(
                $attempt,
                $friendlyUrl,
                (string)$entityName,
                $matchedRouteData,
                $prefixes
            );
            $friendlyUrl = $friendlyUrlUniqueResult->getFriendlyUrlForPersist();
        } while (!$friendlyUrlUniqueResult->isUnique());

        if ($friendlyUrl !== null) {
            $this->em->persist($friendlyUrl);
            $this->em->flush();
            $this->setFriendlyUrlAsMain($friendlyUrl);
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
            $mainFriendlyUrl->getDomainId()
        );
        foreach ($friendlyUrls as $friendlyUrl) {
            $friendlyUrl->setMain(false);
        }
        $mainFriendlyUrl->setMain(true);

        $this->em->flush();

        $this->friendlyUrlCacheFacade->saveToCache($mainFriendlyUrl);
    }
}
