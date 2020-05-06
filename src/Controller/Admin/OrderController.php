<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\OrderController as BaseOrderController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @method __construct(\App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade $advancedSearchOrderFacade, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade $orderItemFacade, \App\Component\Domain\Domain $domain, \App\Model\Order\OrderDataFactory $orderDataFactory)
 * @property \App\Component\Domain\Domain $domain
 */
class OrderController extends BaseOrderController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): Response
    {
        // prevent editing of order on POST request
        if ($request->getMethod() === Request::METHOD_POST) {
            return $this->redirectToRoute('shopsys_framework_admin_order_edit', ['id' => $id]);
        }

        return parent::editAction($request, $id);
    }
}
