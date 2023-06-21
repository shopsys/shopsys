<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade as BaseProductVisibilityFacade;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @method __construct(\App\Model\Product\ProductVisibilityRepository $productVisibilityRepository)
 * @method markProductsForRecalculationAffectedByCategory(\App\Model\Category\Category $category)
 */
class ProductVisibilityFacade extends BaseProductVisibilityFacade
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $routeName = $event->getRequest()->attributes->get('_route');

        if ($routeName !== null && strpos($routeName, 'admin_') === 0) {
            parent::onKernelResponse($event);
        }
    }
}
