<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory as BaseFriendlyUrlDataFactory;

/**
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrlData create()
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrlData createFromIdAndName(int $id, string $name)
 */
class FriendlyUrlDataFactory extends BaseFriendlyUrlDataFactory
{
    /**
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrlData
     */
    protected function createInstance(): FriendlyUrlData
    {
        return new FriendlyUrlData();
    }

    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @return \App\Component\Router\FriendlyUrl\FriendlyUrlData
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
