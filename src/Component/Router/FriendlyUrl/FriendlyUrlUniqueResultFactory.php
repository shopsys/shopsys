<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResult;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory as BaseFriendlyUrlUniqueResultFactory;

class FriendlyUrlUniqueResultFactory extends BaseFriendlyUrlUniqueResultFactory
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface $friendlyUrlFactory
     */
    public function __construct(FriendlyUrlFactoryInterface $friendlyUrlFactory)
    {
        parent::__construct($friendlyUrlFactory);
    }

    /**
     * @param int $attempt
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param string $entityName
     * @param array|null $matchedRouteData
     * @param array $prefixes
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResult
     */
    public function create(int $attempt, FriendlyUrl $friendlyUrl, string $entityName, ?array $matchedRouteData = null, array $prefixes = [])
    {
        if ($matchedRouteData === null) {
            return new FriendlyUrlUniqueResult(true, $friendlyUrl);
        }

        if ($friendlyUrl->getRouteName() === $matchedRouteData['_route']
            && $friendlyUrl->getEntityId() === $matchedRouteData['id']
        ) {
            return new FriendlyUrlUniqueResult(true, null);
        }

        $newIndexedFriendlyUrl = $this->friendlyUrlFactory->createFromPartsIfValid(
            $friendlyUrl->getRouteName(),
            $friendlyUrl->getEntityId(),
            (string)$entityName,
            $friendlyUrl->getDomainId(),
            $attempt + 1, // if URL is duplicate, try again with "url-2", "url-3" and so on
            $prefixes
        );

        return new FriendlyUrlUniqueResult(false, $newIndexedFriendlyUrl);
    }
}
