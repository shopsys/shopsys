<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class GoPayController extends FrontBaseController
{
    /**
     * @var \App\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        OrderFacade $orderFacade
    ) {
        $this->orderFacade = $orderFacade;
    }

    /**
     * @param int $orderId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function goPayStatusNotifyAction(int $orderId): Response
    {
        try {
            $order = $this->orderFacade->getById($orderId);
        } catch (OrderNotFoundException $e) {
            return $this->orderNotFoundRedirect();
        }

        if (!$order->getPayment()->isGoPay()) {
            return $this->orderNotFoundRedirect();
        }

        $this->checkOrderGoPayStatus($order);

        return new Response();
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    private function checkOrderGoPayStatus(Order $order): void
    {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function orderNotFoundRedirect(): RedirectResponse
    {
        $this->addErrorFlash(t('Order not found.'));

        return $this->redirectToRoute('front_cart');
    }
}
