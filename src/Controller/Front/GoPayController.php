<?php

declare(strict_types = 1);

namespace App\Controller\Front;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\GoPay\GoPayTransactionFacade;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class GoPayController extends FrontBaseController
{
    /**
     * @var \App\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \App\Model\GoPay\GoPayTransactionFacade
     */
    private $goPayTransactionFacade;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\GoPay\GoPayTransactionFacade $goPayTransactionFacade
     */
    public function __construct(
        OrderFacade $orderFacade,
        GoPayTransactionFacade $goPayTransactionFacade
    ) {
        $this->orderFacade = $orderFacade;
        $this->goPayTransactionFacade = $goPayTransactionFacade;
    }

    /**
     * @param int $orderId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function goPayStatusNotifyAction(int $orderId): Response
    {
        try {
            $order = $this->orderFacade->getById($orderId);
        } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $e) {
            return $this->orderNotFoundRedirect();
        }

        if ($order->getPayment()->isGoPay()) {
            $this->checkOrderGoPayStatus($order);
        } else {
            return $this->orderNotFoundRedirect();
        }

        return new Response();
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    private function checkOrderGoPayStatus(Order $order): void
    {
        try {
            $this->goPayTransactionFacade->updateOrderTransactions($order);
        } catch (GoPayNotConfiguredException | GoPayPaymentDownloadException $e) {
            $this->addErrorFlash(t('Connection to GoPay gateway failed.'));
        }
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
