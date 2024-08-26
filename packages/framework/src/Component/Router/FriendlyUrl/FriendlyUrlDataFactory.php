<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlDataFactory implements FriendlyUrlDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    protected function createInstance(): FriendlyUrlData
    {
        return new FriendlyUrlData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function create(): FriendlyUrlData
    {
        return $this->createInstance();
    }

    /**
     * @param int $id
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    public function createFromIdAndName(int $id, string $name): FriendlyUrlData
    {
        $friendlyUrlData = $this->create();
        $friendlyUrlData->entityId = $id;
        $friendlyUrlData->name = $name;

        return $friendlyUrlData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData
     */
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
