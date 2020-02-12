<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade as BaseFriendlyUrlFacade;

class FriendlyUrlFacade extends BaseFriendlyUrlFacade
{
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
}
