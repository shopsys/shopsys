<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\FrontOrderData as BaseFrontOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderDataMapper as BaseOrderDataMapper;

/**
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 */
class OrderDataMapper extends BaseOrderDataMapper
{
    /**
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(OrderDataFactoryInterface $orderDataFactory)
    {
        parent::__construct($orderDataFactory);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @return \App\Model\Order\OrderData
     */
    public function getOrderDataFromFrontOrderData(BaseFrontOrderData $frontOrderData)
    {
        /** @var \App\Model\Order\OrderData $orderData */
        $orderData = parent::getOrderDataFromFrontOrderData($frontOrderData);

        return $orderData;
    }
}
