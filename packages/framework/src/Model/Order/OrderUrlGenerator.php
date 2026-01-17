<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderUrlGenerator
{
    public function __construct(protected readonly DomainRouterFactory $domainRouterFactory)
    {
    }

    /**
     * @return string
     */
    public function getOrderDetailUrl(Order $order)
    {
        return $this->domainRouterFactory->getRouter($order->getDomainId())->generate(
            'front_customer_order_detail_unregistered',
            ['urlHash' => $order->getUrlHash()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
