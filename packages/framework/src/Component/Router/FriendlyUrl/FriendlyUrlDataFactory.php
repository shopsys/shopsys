<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlDataFactory
{
    protected function createInstance(): FriendlyUrlData
    {
        return new FriendlyUrlData();
    }

    public function create(): FriendlyUrlData
    {
        return $this->createInstance();
    }

    public function createFromIdAndName(int $id, string $name): FriendlyUrlData
    {
        $friendlyUrlData = $this->create();
        $friendlyUrlData->entityId = $id;
        $friendlyUrlData->name = $name;

        return $friendlyUrlData;
    }

    public function createFromFriendlyUrl(FriendlyUrl $friendlyUrl): FriendlyUrlData
    {
        $friendlyUrlData = $this->createInstance();
        $friendlyUrlData->name = $friendlyUrl->getRouteName();
        $friendlyUrlData->slug = $friendlyUrl->getSlug();
        $friendlyUrlData->entityId = $friendlyUrl->getEntityId();
        $friendlyUrlData->redirectTo = $friendlyUrl->getRedirectTo();
        $friendlyUrlData->redirectCode = $friendlyUrl->getRedirectCode();
        $friendlyUrlData->lastModification = $friendlyUrl->getLastModification();

        return $friendlyUrlData;
    }
}
