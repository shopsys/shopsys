<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as FrameworkOrderDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class OrderDataFactory
{
    protected const string ORDER_ORIGIN_GRAPHQL = 'Frontend API';

    public function __construct(
        protected readonly FrameworkOrderDataFactory $orderDataFactory,
        protected readonly Domain $domain,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly TransportFacade $transportFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CountryFacade $countryFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    public function createOrderDataFromArgument(Argument $argument): OrderData
    {
        $input = $argument['input'];

        $orderData = $this->orderDataFactory->create();

        $orderData->domainId = $this->domain->getId();
        $orderData->origin = static::ORDER_ORIGIN_GRAPHQL;
        $orderData->deliveryAddressSameAsBillingAddress = !$input['isDeliveryAddressDifferentFromBilling'];
        $orderData->isCompanyCustomer = $input['onCompanyBehalf'];

        $orderData = $this->withResolvedFields($input, $orderData);

        return $orderData;
    }

    protected function withResolvedFields(array $input, OrderData $orderData): OrderData
    {
        $cloneOrderData = clone $orderData;

        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
        $cloneOrderData->fillCurrencyFieldsFromCurrency($currency);

        $cloneOrderData->country = $this->countryFacade->findByCode($input['country']);

        if ($input['isDeliveryAddressDifferentFromBilling'] && array_key_exists('deliveryCountry', $input) && $input['deliveryCountry'] !== null) {
            $cloneOrderData->deliveryCountry = $this->countryFacade->findByCode($input['deliveryCountry']);
        }

        unset($input['currency'], $input['country'], $input['deliveryCountry']);

        foreach ($input as $key => $value) {
            if (property_exists(get_class($orderData), $key)) {
                $cloneOrderData->{$key} = $value;
            }
        }

        return $cloneOrderData;
    }
}
