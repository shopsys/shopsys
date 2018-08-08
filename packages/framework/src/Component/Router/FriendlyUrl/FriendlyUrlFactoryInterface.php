<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

interface FriendlyUrlFactoryInterface
{

    public function create(
        string $routeName,
        int $entityId,
        int $domainId,
        string $slug
    ): FriendlyUrl;
}
