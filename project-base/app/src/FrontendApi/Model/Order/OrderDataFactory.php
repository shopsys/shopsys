<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\FrontendApi\Model\Order\Exception\InvalidPacketeryAddressIdUserError;
use App\FrontendApi\Resolver\Store\Exception\StoreNotFoundUserError;
use App\Model\Cart\Cart;
use App\Model\Order\OrderData;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\Store;
use App\Model\Store\StoreFacade;
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
    public function createOrderDataFromArgument(Argument $argument): OrderData
    {
        /** @var \App\Model\Order\OrderData $orderData */
        $orderData = parent::createOrderDataFromArgument($argument);

        $input = $argument['input'];
        $orderData->isCompanyCustomer = $input['onCompanyBehalf'];
        $orderData->newsletterSubscription = $input['newsletterSubscription'];

        return $orderData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Cart\Cart $cart
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
                $store = $this->storeFacade->getByUuidEnabledOnDomain(
                    $pickupPlaceIdentifier,
                    $this->domain->getId()
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

    /**
     * @param string $pickupPlaceIdentifier
     * @return bool
     */
    private function isPickupPlaceIdentifierIntegerInString(string $pickupPlaceIdentifier): bool
    {
        return (string)(int)$pickupPlaceIdentifier !== $pickupPlaceIdentifier;
    }

    /**
     * @param array $input
     * @param \App\Model\Order\OrderData $orderData
     * @return \App\Model\Order\OrderData
     */
    protected function withResolvedFields(array $input, BaseOrderData $orderData): OrderData
    {
        /** @var \App\Model\Order\OrderData $cloneOrderData */
        $cloneOrderData = clone $orderData;

        $cloneOrderData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        $cloneOrderData->country = $this->countryFacade->findByCode($input['country']);

        if ($input['differentDeliveryAddress']) {
            $cloneOrderData->deliveryCountry = $this->countryFacade->findByCode($input['deliveryCountry']);
        }

        return $cloneOrderData;
    }
}
