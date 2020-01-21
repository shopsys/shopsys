<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactoryInterface as BaseFriendlyUrlFactoryInterface;

interface FriendlyUrlFactoryInterface extends BaseFriendlyUrlFactoryInterface
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
    ): ?FriendlyUrl;
}
