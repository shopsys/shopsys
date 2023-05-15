<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlCacheKeyProvider
{
    /**
     * @param string $routeName
     * @param int $domainId
     * @param int $entityId
     * @return string
     */
    public function getMainFriendlyUrlSlugCacheKey(string $routeName, int $domainId, int $entityId): string
    {
        return sprintf(
            '%s_%s_%s',
            $routeName,
            $domainId,
            $entityId,
        );
    }
}
