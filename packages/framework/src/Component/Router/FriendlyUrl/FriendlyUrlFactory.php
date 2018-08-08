<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlFactory implements FriendlyUrlFactoryInterface
{
    public function create(
        string $routeName,
        int $entityId,
        int $domainId,
        string $slug
    ): FriendlyUrl {
        return new FriendlyUrl($routeName, $entityId, $domainId, $slug);
    }
}
