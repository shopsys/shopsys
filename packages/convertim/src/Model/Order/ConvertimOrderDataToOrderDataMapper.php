<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Convertim\Order\ConvertimOrderPaymentData;
use Convertim\Order\ConvertimOrderTransportData;
use Shopsys\ConvertimBundle\Model\Order\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class ConvertimOrderDataToOrderDataMapper
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Order\ConvertimOrderDataToCartMapper $convertimOrderDataToCartMapper
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly ConvertimOrderDataToCartMapper $convertimOrderDataToCartMapper,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly CartFacade $cartFacade,
        protected readonly CountryFacade $countryFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function mapConvertimOrderDataToOrderData(ConvertimOrderData $convertimOrderData): OrderData
    {
        $cart = $this->convertimOrderDataToCartMapper->mapConvertimOrderDataToCart($convertimOrderData);

        $convertimCustomerData = $convertimOrderData->getCustomerData();

        $orderData = $this->orderDataFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());
        $orderData->domainId = $this->domain->getId();
        $orderData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
        $orderData->firstName = $convertimCustomerData->getFirstName();
        $orderData->lastName = $convertimCustomerData->getLastName();
        $orderData->email = $convertimCustomerData->getEmail();
        $orderData->telephone = $convertimCustomerData->getTelephoneNumberWithPrefix();

        $convertimBillingAddressData = $convertimCustomerData->getConvertimCustomerBillingAddressData();
        $convertimDeliveryAddressData = $convertimCustomerData->getConvertimCustomerDeliveryAddressData();

        if ($convertimBillingAddressData !== null) {
            $orderData->companyName = $convertimBillingAddressData->getCompanyName();
            $orderData->companyNumber = $convertimBillingAddressData->getIdentificationNumber();
            $orderData->companyTaxNumber = $convertimBillingAddressData->getVatNumber();
            $orderData->isCompanyCustomer = $convertimBillingAddressData->getCompanyName() !== null && $convertimBillingAddressData->getIdentificationNumber() !== null;
            $orderData->street = $convertimBillingAddressData->getStreet();
            $orderData->city = $convertimBillingAddressData->getCity();
            $orderData->postcode = $convertimBillingAddressData->getPostcode();
            $orderData->country = $this->getCountryByName($convertimBillingAddressData->getCountry());
        } else {
            $orderData->companyName = $convertimDeliveryAddressData->getCompanyName();
            $orderData->street = $convertimDeliveryAddressData->getStreet();
            $orderData->city = $convertimDeliveryAddressData->getCity();
            $orderData->postcode = $convertimDeliveryAddressData->getPostcode();
            $orderData->country = $this->getCountryByName($convertimDeliveryAddressData->getCountry());
        }

        $orderData->deliveryAddressSameAsBillingAddress = $convertimOrderData->isBillingAddressSameAsDeliveryAddress();

        if (!$convertimOrderData->isBillingAddressSameAsDeliveryAddress()) {
            $orderData->deliveryFirstName = $convertimDeliveryAddressData->getName();
            $orderData->deliveryLastName = $convertimDeliveryAddressData->getLastName();
            $orderData->deliveryCompanyName = $convertimDeliveryAddressData->getCompanyName();
            $orderData->deliveryTelephone = $convertimDeliveryAddressData->getCurrierTelephoneNumberWithPrefix();
            $orderData->deliveryStreet = $convertimDeliveryAddressData->getStreet();
            $orderData->deliveryCity = $convertimDeliveryAddressData->getCity();
            $orderData->deliveryPostcode = $convertimDeliveryAddressData->getPostCode();
            $orderData->deliveryCountry = $this->getCountryByName($convertimDeliveryAddressData->getCountry());
        }
        $orderData->note = $convertimOrderData->getNote();

        $this->mapProducts($convertimOrderData, $orderData);
        $this->mapConvertimTransportOrPaymentDataToOrderItem($convertimOrderData->getPaymentData(), $orderData);
        $this->mapConvertimTransportOrPaymentDataToOrderItem($convertimOrderData->getTransportData(), $orderData);

        $this->cartFacade->deleteCart($cart);

        return $orderData;
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[] $orderItemDatas
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function getOrderItemByUuid(string $uuid, array $orderItemDatas): OrderItemData
    {
        foreach ($orderItemDatas as $orderItemData) {
            if ($orderItemData->getItemUuid() === $uuid) {
                return $orderItemData;
            }
        }

        throw new OrderItemNotFoundException(sprintf('Order item with uuid "%s" not found.', $uuid));
    }

    /**
     * @param string|null $countryName
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    protected function getCountryByName(?string $countryName): Country
    {
        $countryReturn = null;
        $i = 1;

        foreach ($this->countryFacade->getAllEnabledOnCurrentDomain() as $country) {
            if ($i === 1) {
                $countryReturn = $country;
            }

            if ($country->getName() === $countryName) {
                return $country;
            }

            $i++;
        }

        return $countryReturn;
    }

    /**
     * @param \Convertim\Order\ConvertimOrderTransportData|\Convertim\Order\ConvertimOrderPaymentData $convertimOrderTransportPaymentData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapConvertimTransportOrPaymentDataToOrderItem(
        ConvertimOrderTransportData|ConvertimOrderPaymentData $convertimOrderTransportPaymentData,
        OrderData $orderData,
    ): void {
        $transportOrderItemData = $this->getOrderItemByUuid($convertimOrderTransportPaymentData->getUuid(), $orderData->items);
        $transportOrderItemData->totalPriceWithVat = Money::create($convertimOrderTransportPaymentData->getPriceWithVat());
        $transportOrderItemData->totalPriceWithoutVat = Money::create($convertimOrderTransportPaymentData->getPriceWithoutVat());
        $transportOrderItemData->unitPriceWithVat = Money::create($convertimOrderTransportPaymentData->getPriceWithVat());
        $transportOrderItemData->unitPriceWithoutVat = Money::create($convertimOrderTransportPaymentData->getPriceWithoutVat());

        if ($convertimOrderTransportPaymentData->getVatRate() > 0) {
            $transportOrderItemData->vatPercent = Money::create(100)->divide((Money::create($convertimOrderTransportPaymentData->getPriceWithoutVat())->divide($convertimOrderTransportPaymentData->getVatRate(), 6))->getAmount(), 6)->round(6)->getAmount();
        } else {
            $transportOrderItemData->vatPercent = '0';
        }
        $transportOrderItemData->usePriceCalculation = false;
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapProducts(ConvertimOrderData $convertimOrderData, OrderData $orderData): void
    {
        foreach ($convertimOrderData->getOrderItemsData() as $convertimOrderItemData) {
            $orderItemData = $this->getOrderItemByUuid($convertimOrderItemData->getProductId(), $orderData->items);
            $orderItemData->totalPriceWithVat = (Money::create($convertimOrderItemData->getPriceWithVat())->multiply($convertimOrderItemData->getQuantity()));
            $orderItemData->totalPriceWithoutVat = (Money::create($convertimOrderItemData->getPriceWithoutVat())->multiply($convertimOrderItemData->getQuantity()));
            $orderItemData->quantity = $convertimOrderItemData->getQuantity();
            $orderItemData->unitPriceWithVat = Money::create($convertimOrderItemData->getPriceWithVat());
            $orderItemData->unitPriceWithoutVat = Money::create($convertimOrderItemData->getPriceWithoutVat());

            if ($convertimOrderItemData->getVatRate() > 0) {
                $orderItemData->vatPercent = Money::create(100)->divide((Money::create($convertimOrderItemData->getPriceWithoutVat())->divide($convertimOrderItemData->getVatRate(), 6))->getAmount(), 6)->round(6)->getAmount();
            } else {
                $orderItemData->vatPercent = '0';
            }

            $orderItemData->usePriceCalculation = false;
        }
    }
}
