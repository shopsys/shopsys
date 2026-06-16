<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContent;
use Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContentFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class OrderConfirmationPageContentQuery extends AbstractQuery
{
    public function __construct(
        protected readonly OrderContentPageFacade $orderContentPageFacade,
        protected readonly ConfirmationPageContentFactory $confirmationPageContentFactory,
    ) {
    }

    public function orderConfirmationPageContentQuery(Order $order): ConfirmationPageContent
    {
        if (!$order->getPayment()->isGatewayPayment()) {
            return $this->confirmationPageContentFactory->createSuccessful(
                $this->orderContentPageFacade->getOrderSentPageContent($order),
            );
        }

        if ($order->isPaid()) {
            return $this->confirmationPageContentFactory->createSuccessful(
                $this->orderContentPageFacade->getPaymentSuccessfulPageContent($order),
            );
        }

        if ($order->hasPaymentInProcess()) {
            return $this->confirmationPageContentFactory->createInProcess(
                $this->orderContentPageFacade->getPaymentInProcessPageContent($order),
            );
        }

        return $this->confirmationPageContentFactory->createFailed(
            $this->orderContentPageFacade->getPaymentFailedPageContent($order),
        );
    }
}
