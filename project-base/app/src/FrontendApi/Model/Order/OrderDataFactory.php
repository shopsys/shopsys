<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @method __construct(\App\Model\Order\OrderDataFactory $orderDataFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade)
 * @method \App\Model\Order\OrderData createOrderDataFromArgument(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Order\OrderData withResolvedFields(array $input, \App\Model\Order\OrderData $orderData)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
}
