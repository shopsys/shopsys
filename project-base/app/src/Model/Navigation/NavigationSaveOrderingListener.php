<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use App\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class NavigationSaveOrderingListener
{
    private const SAVE_ORDERING_URI = '/admin/_grid/save-ordering/';

    /**
     * @param \App\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        private readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $controllerEvent
     */
    public function onKernelController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->getRequest()->getRequestUri() === self::SAVE_ORDERING_URI && $controllerEvent->getRequest()->get('entityClass') === NavigationItem::class) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);
        }
    }
}
