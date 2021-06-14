<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Order\OrderData
     */
    public function createOrderDataFromArgument(Argument $argument): OrderData
    {
        /** @var \App\Model\Order\OrderData $orderData */
        $orderData = parent::createOrderDataFromArgument($argument);

        if ($orderData->companyName !== null && $orderData->companyNumber !== null) {
            $orderData->isCompanyCustomer = true;
        }

        return $orderData;
    }
}
