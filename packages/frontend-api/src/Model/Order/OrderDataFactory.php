<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as FrameworkOrderDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Order\Exception\InvalidPacketeryAddressIdUserError;
use Shopsys\FrontendApiBundle\Model\Store\Exception\StoreNotFoundUserError;

class OrderDataFactory
{
    protected const ORDER_ORIGIN_GRAPHQL = 'Frontend API';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
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

    /**
     * @param array $input
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    protected function withResolvedFields(array $input, OrderData $orderData): OrderData
    {
        $cloneOrderData = clone $orderData;

        $cloneOrderData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        $cloneOrderData->country = $this->countryFacade->findByCode($input['country']);

        if ($input['isDeliveryAddressDifferentFromBilling'] && array_key_exists('deliveryCountry', $input)) {
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function updateOrderDataFromCart(OrderData $orderData, Cart $cart): void
    {
        $orderData->payment = $cart->getPayment();
        $orderData->transport = $cart->getTransport();
        $orderData->goPayBankSwift = $cart->getPaymentGoPayBankSwift();
        $pickupPlaceIdentifier = $cart->getPickupPlaceIdentifier();

        if ($cart->getPickupPlaceIdentifier() === null) {
            return;
        }

        if ($orderData->transport->isPersonalPickup()) {
            try {
                $store = $this->storeFacade->getByUuidAndDomainId(
                    $pickupPlaceIdentifier,
                    $this->domain->getId(),
                );
                $this->setOrderDataByStore($orderData, $store);
            } catch (StoreByUuidNotFoundException $exception) {
                throw new StoreNotFoundUserError($exception->getMessage());
            }
        }

        if (
            $orderData->transport->isPacketery() &&
            $this->isPickupPlaceIdentifierIntegerInString($pickupPlaceIdentifier)
        ) {
            throw new InvalidPacketeryAddressIdUserError('Wrong packetery address ID');
        }

        $orderData->pickupPlaceIdentifier = $pickupPlaceIdentifier;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    protected function setOrderDataByStore(OrderData $orderData, Store $store): void
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

    /**
     * @param string $pickupPlaceIdentifier
     * @return bool
     */
    protected function isPickupPlaceIdentifierIntegerInString(string $pickupPlaceIdentifier): bool
    {
        return (string)(int)$pickupPlaceIdentifier !== $pickupPlaceIdentifier;
    }
}
