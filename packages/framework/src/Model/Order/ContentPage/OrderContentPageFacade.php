<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\ContentPage;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;

class OrderContentPageFacade
{
    public const VARIABLE_PAYMENT_INSTRUCTIONS = '{payment_instructions}';
    public const VARIABLE_TRANSPORT_INSTRUCTIONS = '{transport_instructions}';
    public const VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const VARIABLE_NUMBER = '{number}';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageSettingFacade $orderContentPageSettingFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly OrderContentPageSettingFacade $orderContentPageSettingFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    public function getOrderSentPageContent(Order $order): string
    {
        $orderSentPageContent = $this->orderContentPageSettingFacade->getOrderSentPageContent($order->getDomainId());

        return $this->replaceVariables($order, $orderSentPageContent);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $orderSentPageContent
     * @return string
     */
    protected function replaceVariables(Order $order, string $orderSentPageContent): string
    {
        $orderDetailUrl = $this->orderUrlGenerator->getOrderDetailUrl($order);

        $variables = [
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => $order->getTransport()->getInstructions(),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $order->getPayment()->getInstructions(),
            self::VARIABLE_ORDER_DETAIL_URL => $orderDetailUrl,
            self::VARIABLE_NUMBER => $order->getNumber(),
        ];

        return strtr($orderSentPageContent, $variables);
    }
}
