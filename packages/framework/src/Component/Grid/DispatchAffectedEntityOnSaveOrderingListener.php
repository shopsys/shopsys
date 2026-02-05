<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class DispatchAffectedEntityOnSaveOrderingListener
{
    protected const SAVE_ORDERING_URI = '/admin/_grid/save-ordering/';

    public function __construct(
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    public function onKernelController(ControllerEvent $controllerEvent): void
    {
        if ($controllerEvent->getRequest()->getRequestUri() !== static::SAVE_ORDERING_URI) {
            return;
        }

        $entityClass = $controllerEvent->getRequest()->request->getString('entityClass');

        switch ($entityClass) {
            case ParameterGroup::class:
                $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_PARAMETERS]);

                break;
        }
    }
}
