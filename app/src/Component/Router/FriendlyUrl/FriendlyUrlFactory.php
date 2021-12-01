<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory as BaseFriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\String\TransformString;

/**
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl create(string $routeName, int $entityId, int $domainId, string $slug)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl[] createForAllDomains(string $routeName, int $entityId, string[] $namesByLocale)
 */
class FriendlyUrlFactory extends BaseFriendlyUrlFactory
{
    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     * @param int|null $indexPostfix
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function createIfValid(
        string $routeName,
        int $entityId,
        string $entityName,
        int $domainId,
        ?int $indexPostfix = null
    ): ?FriendlyUrl {
        if ($entityName === '') {
            return null;
        }

        $nameForUrl = $entityName . ($indexPostfix === null ? '' : '-' . $indexPostfix);
        $slug = TransformString::stringToFriendlyUrlSlug($nameForUrl);

        return $this->create($routeName, $entityId, $domainId, $slug);
    }
}
