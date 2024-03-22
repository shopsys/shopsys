<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Product\ProductFacade $productFacade
 */
class OrderDataFactory extends BaseOrderDataFactory
{
}
