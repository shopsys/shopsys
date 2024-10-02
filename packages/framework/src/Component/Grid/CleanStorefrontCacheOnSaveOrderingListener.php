<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class CleanStorefrontCacheOnSaveOrderingListener
{
    protected const string SAVE_ORDERING_URI = '/admin/_grid/save-ordering/';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    protected function getEntityClassWithQueryKeyMap(): array
    {
        return [
            NavigationItem::class => CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART,
            SliderItem::class => CleanStorefrontCacheFacade::SLIDER_ITEMS_QUERY_KEY_PART,
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $controllerEvent
     */
    public function onKernelController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->getRequest()->getRequestUri() !== static::SAVE_ORDERING_URI) {
            return;
        }

        $entityClass = $controllerEvent->getRequest()->get('entityClass');

        if (array_key_exists($entityClass, $this->getEntityClassWithQueryKeyMap())) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache($this->getEntityClassWithQueryKeyMap()[$entityClass]);
        }
    }
}
