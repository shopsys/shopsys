<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Convertim\Order\ConvertimOrderItemData;
use Convertim\Order\ConvertimOrderPaymentData;
use Convertim\Order\ConvertimOrderTransportData;
use Shopsys\ConvertimBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Country\Exception\CountryNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Shopsys\FrameworkBundle\Twig\PriceExtension;

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
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\ConvertimBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension
     */
    public function __construct(
        protected readonly ConvertimOrderDataToCartMapper $convertimOrderDataToCartMapper,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly CartFacade $cartFacade,
        protected readonly CountryFacade $countryFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly TransportFacade $transportFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly StoreFacade $storeFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly NumberFormatterExtension $numberFormatterExtension,
        protected readonly PriceExtension $priceExtension,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function mapConvertimOrderDataToOrderData(ConvertimOrderData $convertimOrderData): OrderData
    {
        $convertimCustomerData = $convertimOrderData->getCustomerData();

        $orderData = $this->orderDataFactory->create();
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
            $orderData->country = $this->getCountryByCode($convertimBillingAddressData->getCountry());
        } else {
            $orderData->companyName = $convertimDeliveryAddressData->getCompanyName();
            $orderData->street = $convertimDeliveryAddressData->getStreet();
            $orderData->city = $convertimDeliveryAddressData->getCity();
            $orderData->postcode = $convertimDeliveryAddressData->getPostcode();
            $orderData->country = $this->getCountryByCode($convertimDeliveryAddressData->getCountry());
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
            $orderData->deliveryCountry = $this->getCountryByCode($convertimDeliveryAddressData->getCountry());
        }

        $extraTransportData = $convertimOrderData->getTransportData()->getExtra();

        if ($extraTransportData !== null && $extraTransportData->getPickUpPointCode() !== null) {
            $orderData->deliveryFirstName = $convertimDeliveryAddressData->getName();
            $orderData->deliveryLastName = $convertimDeliveryAddressData->getLastName();
            $orderData->deliveryCompanyName = $extraTransportData->getPickupPointCompanyName();
            $orderData->deliveryStreet = $extraTransportData->getPickupPointStreet();
            $orderData->deliveryCity = $extraTransportData->getPickupPointCity();
            $orderData->deliveryPostcode = $extraTransportData->getPickupPointPostCode();
            $orderData->deliveryCountry = $this->getCountryByCode($extraTransportData->getPickupPointCountryCode());
            $orderData->deliveryAddressSameAsBillingAddress = false;
        }

        $orderData->note = $convertimOrderData->getNote();
        $orderData->heurekaAgreement = !$convertimOrderData->isDisallowHeurekaVerifiedByCustomers();
        $orderData->origin = 'Convertim';
        $orderData->convertimUuid = $convertimOrderData->getUuid();

        $this->mapProducts($convertimOrderData, $orderData);
        $this->mapConvertimTransportDataToOrderItem($convertimOrderData->getTransportData(), $orderData);
        $this->mapConvertimPaymentDataToOrderItem($convertimOrderData->getPaymentData(), $orderData);
        $this->mapPromoCodes($convertimOrderData, $orderData);

        if ($convertimCustomerData->getCustomerEshopUuid() !== null) {
            $orderData->customerUser = $this->customerUserFacade->getByUuid($convertimCustomerData->getCustomerEshopUuid());
        }

        return $orderData;
    }

    /**
     * @param string|null $countryCode
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    protected function getCountryByCode(?string $countryCode): Country
    {
        $countryReturn = null;
        $i = 1;

        foreach ($this->countryFacade->getAllEnabledOnCurrentDomain() as $country) {
            if ($i === 1) {
                $countryReturn = $country;
            }

            if ($country->getCode() === $countryCode) {
                return $country;
            }

            $i++;
        }

        if ($countryReturn === null) {
            throw new CountryNotFoundException('No country has been found on current domain.');
        }

        return $countryReturn;
    }

    /**
     * @param \Convertim\Order\ConvertimOrderPaymentData $convertimOrderPaymentData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapConvertimPaymentDataToOrderItem(
        ConvertimOrderPaymentData $convertimOrderPaymentData,
        OrderData $orderData,
    ): void {
        $payment = $this->paymentFacade->getByUuid($convertimOrderPaymentData->getUuid());
        $paymentOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PAYMENT);
        $paymentOrderItemData->payment = $payment;
        $paymentOrderItemData->name = $payment->getName();
        $paymentOrderItemData->quantity = 1;
        $paymentOrderItemData->totalPriceWithVat = Money::create($convertimOrderPaymentData->getPriceWithVat());
        $paymentOrderItemData->totalPriceWithoutVat = Money::create($convertimOrderPaymentData->getPriceWithoutVat());
        $paymentOrderItemData->unitPriceWithVat = Money::create($convertimOrderPaymentData->getPriceWithVat());
        $paymentOrderItemData->unitPriceWithoutVat = Money::create($convertimOrderPaymentData->getPriceWithoutVat());
        $paymentOrderItemData->vatPercent = (string)($convertimOrderPaymentData->getVatRate() ?? 0);
        $paymentOrderItemData->usePriceCalculation = false;

        $orderData->addItem($paymentOrderItemData);
        $orderData->orderPayment = $paymentOrderItemData;
        $orderData->payment = $payment;
        $orderData->addTotalPrice(new Price($paymentOrderItemData->totalPriceWithoutVat, $paymentOrderItemData->totalPriceWithVat), OrderItemTypeEnum::TYPE_PAYMENT);
    }

    /**
     * @param \Convertim\Order\ConvertimOrderTransportData $convertimOrderTransportData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapConvertimTransportDataToOrderItem(
        ConvertimOrderTransportData $convertimOrderTransportData,
        OrderData $orderData,
    ): void {
        $transport = $this->transportFacade->getByUuid($convertimOrderTransportData->getUuid());

        $transportOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_TRANSPORT);
        $transportOrderItemData->transport = $transport;
        $transportOrderItemData->name = $transport->getName();

        $pickupPlaceIdentifier = $convertimOrderTransportData->getExtra()?->getPickUpPointCode();

        if ($transport->isPersonalPickup()) {
            try {
                $store = $this->storeFacade->getByUuidAndDomainId(
                    $pickupPlaceIdentifier,
                    $this->domain->getId(),
                );

                $transportOrderItemData->name .= ' ' . $store->getName();
            } catch (StoreByUuidNotFoundException) {
            }
        }

        $transportOrderItemData->quantity = 1;
        $transportOrderItemData->totalPriceWithVat = Money::create($convertimOrderTransportData->getPriceWithVat());
        $transportOrderItemData->totalPriceWithoutVat = Money::create($convertimOrderTransportData->getPriceWithoutVat());
        $transportOrderItemData->unitPriceWithVat = Money::create($convertimOrderTransportData->getPriceWithVat());
        $transportOrderItemData->unitPriceWithoutVat = Money::create($convertimOrderTransportData->getPriceWithoutVat());
        $transportOrderItemData->vatPercent = (string)($convertimOrderTransportData->getVatRate() ?? 0);
        $transportOrderItemData->usePriceCalculation = false;

        $orderData->addItem($transportOrderItemData);
        $orderData->orderTransport = $transportOrderItemData;
        $orderData->transport = $transport;
        $orderData->pickupPlaceIdentifier = $pickupPlaceIdentifier;
        $orderData->addTotalPrice(new Price($transportOrderItemData->totalPriceWithoutVat, $transportOrderItemData->totalPriceWithVat), OrderItemTypeEnum::TYPE_TRANSPORT);
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapProducts(ConvertimOrderData $convertimOrderData, OrderData $orderData): void
    {
        $productUuids = array_map(
            static fn ($convertimOrderItemData) => $convertimOrderItemData->getProductId(),
            $convertimOrderData->getOrderItemsData(),
        );

        $productsByUuid = $this->productRepository->getProductsByUuidsIndexedByUuid($productUuids);

        foreach ($convertimOrderData->getOrderItemsData() as $convertimOrderItemData) {
            if (!array_key_exists($convertimOrderItemData->getProductId(), $productsByUuid)) {
                throw new ProductNotFoundException(
                    sprintf('Product with UUID "%s" not found.', $convertimOrderItemData->getProductId()),
                );
            }

            $product = $productsByUuid[$convertimOrderItemData->getProductId()];

            $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
            $orderItemData->product = $product;
            $orderItemData->catnum = $product->getCatnum();
            $orderItemData->name = $product->getName();
            $orderItemData->unitName = $product->getUnit()->getName();
            $orderItemData->totalPriceWithVat = (Money::create($convertimOrderItemData->getPriceWithVat())->multiply($convertimOrderItemData->getQuantity()));
            $orderItemData->totalPriceWithoutVat = (Money::create($convertimOrderItemData->getPriceWithoutVat())->multiply($convertimOrderItemData->getQuantity()));
            $orderItemData->quantity = $convertimOrderItemData->getQuantity();
            $orderItemData->unitPriceWithVat = Money::create($convertimOrderItemData->getPriceWithVat());
            $orderItemData->unitPriceWithoutVat = Money::create($convertimOrderItemData->getPriceWithoutVat());
            $orderItemData->vatPercent = (string)($convertimOrderItemData->getVatRate() ?? 0);
            $orderItemData->usePriceCalculation = false;

            $orderData->addItem($orderItemData);
            $orderData->addTotalPrice(new Price($orderItemData->totalPriceWithoutVat, $orderItemData->totalPriceWithVat), OrderItemTypeEnum::TYPE_PRODUCT);
            $this->mapDiscounts($convertimOrderItemData, $orderItemData, $orderData);
        }
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapPromoCodes(ConvertimOrderData $convertimOrderData, OrderData $orderData): void
    {
        $promoCodes = $convertimOrderData->getPromoCodes();

        if (count($promoCodes) > 0) {
            $orderData->promoCode = $promoCodes[0]->getCode();
        }
    }

    /**
     * @param \Convertim\Order\ConvertimOrderItemData $convertimOrderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function mapDiscounts(
        ConvertimOrderItemData $convertimOrderItemData,
        OrderItemData $orderItemData,
        OrderData $orderData,
    ): void {
        if ($convertimOrderItemData->getDiscounts() === null) {
            return;
        }

        /** @var array{ withVat: int|float, withoutVat: int|float } $discount */
        foreach ($convertimOrderItemData->getDiscounts() as $promoCodesCode => $discount) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodesCode, $this->domain->getId());

            if ($promoCode === null) {
                throw new PromoCodeNotFoundException(
                    sprintf('Promo code with code "%s" not found.', $promoCodesCode),
                );
            }

            $discountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_DISCOUNT);

            $totalWithoutVat = Money::create((string)$discount['withoutVat']);
            $totalWithVat = Money::create((string)$discount['withVat']);
            $unitWithoutVat = $totalWithoutVat->divide($convertimOrderItemData->getQuantity(), 6);
            $unitWithVat = $totalWithVat->divide($convertimOrderItemData->getQuantity(), 6);
            $unitPrice = (new Price($unitWithoutVat, $unitWithVat))->inverse();
            $totalPrice = (new Price($totalWithoutVat, $totalWithVat))->inverse();

            $name = sprintf(
                '%s %s',
                t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->domain->getLocale()),
                $this->priceExtension->priceFilter($totalWithVat),
            );

            $discountOrderItemData->name = $name;
            $discountOrderItemData->quantity = 1;
            $discountOrderItemData->setUnitPrice($unitPrice);
            $discountOrderItemData->setTotalPrice($totalPrice);
            $discountOrderItemData->vatPercent = $orderItemData->vatPercent;
            $discountOrderItemData->promoCode = $promoCode;
            $discountOrderItemData->usePriceCalculation = false;

            $orderData->addItem($discountOrderItemData);
            $orderData->addTotalPrice(new Price($discountOrderItemData->totalPriceWithoutVat, $discountOrderItemData->totalPriceWithVat), OrderItemTypeEnum::TYPE_DISCOUNT);
            $orderItemData->relatedOrderItemsData[] = $discountOrderItemData;
        }
    }
}
