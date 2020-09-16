<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade as BaseProductVisibilityFacade;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductVisibilityFacade extends BaseProductVisibilityFacade
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $routeName = $event->getRequest()->attributes->get('_route');

        if ($routeName !== null && strpos($routeName, 'admin_') === 0) {
            parent::onKernelResponse($event);
        }
    }
}
