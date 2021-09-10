<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Order\OrderData;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\Store;
use App\Model\Store\StoreFacade;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method \App\Model\Order\OrderData withResolvedFields(array $input, \App\Model\Order\OrderData $orderData)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        OrderDataFactoryInterface $orderDataFactory,
        Domain $domain,
        PaymentFacade $paymentFacade,
        TransportFacade $transportFacade,
        CurrencyFacade $currencyFacade,
        CountryFacade $countryFacade,
        ProductFacade $productFacade,
        StoreFacade $storeFacade
    ) {
        parent::__construct($orderDataFactory, $domain, $paymentFacade, $transportFacade, $currencyFacade, $countryFacade, $productFacade);

        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Order\OrderData
     */
    public function createOrderDataFromArgument(Argument $argument): BaseOrderData
    {
        /** @var \App\Model\Order\OrderData $orderData */
        $orderData = parent::createOrderDataFromArgument($argument);

        if ($orderData->companyName !== null && $orderData->companyNumber !== null) {
            $orderData->isCompanyCustomer = true;
        }
        $input = $argument['input'];
        if (isset($input['personalPickupStoreUuid'])) {
            try {
                $store = $this->storeFacade->getByUuid($input['personalPickupStoreUuid']);
                $this->setOrderDataByStore($orderData, $store);
            } catch (StoreByUuidNotFoundException $exception) {
                throw new UserError($exception->getMessage());
            }
        }

        return $orderData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Store\Store $store
     */
    private function setOrderDataByStore(OrderData $orderData, Store $store): void
    {
        $orderData->personalPickupStore = $store;
        $orderData->deliveryAddressSameAsBillingAddress = false;

        $orderData->deliveryFirstName = $orderData->deliveryFirstName ?? $orderData->firstName;
        $orderData->deliveryLastName = $orderData->deliveryLastName ?? $orderData->lastName;
        $orderData->deliveryCompanyName = $orderData->deliveryCompanyName ?? $orderData->companyName;
        $orderData->deliveryTelephone = $orderData->deliveryTelephone ?? $orderData->telephone;

        $orderData->deliveryStreet = $store->getStreet();
        $orderData->deliveryCity = $store->getCity();
        $orderData->deliveryPostcode = $store->getPostcode();
        $orderData->deliveryCountry = $store->getCountry();
    }
}
