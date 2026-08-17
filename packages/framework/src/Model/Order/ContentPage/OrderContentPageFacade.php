<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\ContentPage;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade;

class OrderContentPageFacade
{
    public const VARIABLE_PAYMENT_INSTRUCTIONS = '{payment_instructions}';
    public const VARIABLE_TRANSPORT_INSTRUCTIONS = '{transport_instructions}';
    public const VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const VARIABLE_NUMBER = '{number}';

    public function __construct(
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
        protected readonly PaymentInstructionFacade $paymentInstructionFacade,
    ) {
    }

    public function getOrderSentPageContent(Order $order): string
    {
        $orderSentPageContent = $this->orderContentPageSettingFacade->getOrderSentPageContent($order->getDomainId());

        return $this->replaceVariables($order, $orderSentPageContent);
    }

    public function getPaymentSuccessfulPageContent(Order $order): string
    {
        $orderSentPageContent = $this->orderContentPageSettingFacade->getPaymentSuccessfulPageContent($order->getDomainId());

        return $this->replaceVariables($order, $orderSentPageContent);
    }

    public function getPaymentFailedPageContent(Order $order): string
    {
        $orderSentPageContent = $this->orderContentPageSettingFacade->getPaymentFailedPageContent($order->getDomainId());

        return $this->replaceVariables($order, $orderSentPageContent);
    }

    public function getPaymentInProcessPageContent(Order $order): string
    {
        $orderSentPageContent = $this->orderContentPageSettingFacade->getPaymentInProcessPageContent($order->getDomainId());

        return $this->replaceVariables($order, $orderSentPageContent);
    }

    protected function replaceVariables(Order $order, string $orderSentPageContent): string
    {
        $orderDetailUrl = $this->orderUrlGenerator->getOrderDetailUrl($order);

        $variables = [
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => $order->getTransport()->getInstructions(),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $this->paymentInstructionFacade->getPaymentInstructionsForOrderSubmittedPage($order),
            self::VARIABLE_ORDER_DETAIL_URL => $orderDetailUrl,
            self::VARIABLE_NUMBER => $order->getNumber(),
        ];

        return strtr($orderSentPageContent, $variables);
    }
}
