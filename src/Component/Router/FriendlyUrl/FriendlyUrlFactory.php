<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory as BaseFriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class FriendlyUrlFactory extends BaseFriendlyUrlFactory
{
    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     * @param int|null $indexPostfix
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
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
