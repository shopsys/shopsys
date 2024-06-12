<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class NavigationSaveOrderingListener
{
    protected const SAVE_ORDERING_URI = '/admin/_grid/save-ordering/';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $controllerEvent
     */
    public function onKernelController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->getRequest()->getRequestUri() === static::SAVE_ORDERING_URI && $controllerEvent->getRequest()->get('entityClass') === NavigationItem::class) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);
        }
    }
}
