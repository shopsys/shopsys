<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method __construct(\App\Model\Order\OrderDataFactory $orderDataFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Payment\PaymentFacade $paymentFacade, \App\Model\Transport\TransportFacade $transportFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade, \App\Model\Product\ProductFacade $productFacade, \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade)
 * @method \App\Model\Order\OrderData createOrderDataFromArgument(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Order\OrderData withResolvedFields(array $input, \App\Model\Order\OrderData $orderData)
 * @method updateOrderDataFromCart(\App\Model\Order\OrderData $orderData, \App\Model\Cart\Cart $cart)
 * @method setOrderDataByStore(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Store\Store $store)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
}
