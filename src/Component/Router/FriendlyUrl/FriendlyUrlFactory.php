<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory as BaseFriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class FriendlyUrlFactory extends BaseFriendlyUrlFactory implements FriendlyUrlFactoryInterface
{
    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     * @param int|null $indexPostfix
     * @param array $prefixes
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function createFromPartsIfValid(
        string $routeName,
        int $entityId,
        string $entityName,
        int $domainId,
        ?int $indexPostfix = null,
        ?array $prefixes = []
    ): ?FriendlyUrl {
        if ($entityName === '') {
            return null;
        }

        $slug = '';
        foreach ($prefixes as $prefix) {
            $nameForUrl = $prefix . ($indexPostfix === null ? '' : '-' . $indexPostfix);
            $slug .= TransformString::stringToFriendlyUrlSlug($nameForUrl) . '/';
        }

        $nameForUrl = $entityName . ($indexPostfix === null ? '' : '-' . $indexPostfix);
        $slug .= TransformString::stringToFriendlyUrlSlug($nameForUrl) . '/';

        if ($slug === '') {
            return null;
        }

        return $this->create($routeName, $entityId, $domainId, $slug);
    }
}
