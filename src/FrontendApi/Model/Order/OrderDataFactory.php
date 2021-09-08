<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method __construct(\App\Model\Order\OrderDataFactory $orderDataFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Payment\PaymentFacade $paymentFacade, \App\Model\Transport\TransportFacade $transportFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade, \App\Model\Product\ProductFacade $productFacade)
 * @method \App\Model\Order\OrderData withResolvedFields(array $input, \App\Model\Order\OrderData $orderData)
 */
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
