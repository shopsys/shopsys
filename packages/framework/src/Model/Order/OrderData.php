<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class OrderData
{
    public const string NEW_ITEM_PREFIX = 'new_';

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string|null
     */
    public $orderNumber;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null
     */
    public $status;

    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $telephone;

    /**
     * @var string|null
     */
    public $companyName;

    /**
     * @var string|null
     */
    public $companyNumber;

    /**
     * @var string|null
     */
    public $companyTaxNumber;

    /**
     * @var string|null
     */
    public $street;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public $country;

    /**
     * @var bool
     */
    public $deliveryAddressSameAsBillingAddress;

    /**
     * @var string|null
     */
    public $deliveryFirstName;

    /**
     * @var string|null
     */
    public $deliveryLastName;

    /**
     * @var string|null
     */
    public $deliveryCompanyName;

    /**
     * @var string|null
     */
    public $deliveryTelephone;

    /**
     * @var string|null
     */
    public $deliveryStreet;

    /**
     * @var string|null
     */
    public $deliveryCity;

    /**
     * @var string|null
     */
    public $deliveryPostcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public $deliveryCountry;

    /**
     * @var string|null
     */
    public $note;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public $items = [];

    /**
     * @var \DateTimeImmutable|null
     */
    public $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $deliveredAt;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var string|null
     */
    public $currencyCode;

    /**
     * @var string|null
     */
    public $currencyRoundingType;

    /**
     * @var int|null
     */
    public $currencyRoundingPlacesPriceWithoutVat;

    /**
     * @var int|null
     */
    public $currencyMinFractionDigits;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public $createdAsAdministrator;

    /**
     * @var string|null
     */
    public $createdAsAdministratorName;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    public $orderPayment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    public $orderTransport;

    /**
     * @var string|null
     */
    public $origin;

    /**
     * @var string|null
     */
    public $goPayBankSwift;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData[]
     */
    public $paymentTransactionRefunds;

    /**
     * @var bool
     */
    public $heurekaAgreement;

    /**
     * @var bool|null
     */
    public $isCompanyCustomer;

    /**
     * @var bool|null
     */
    public $newsletterSubscription;

    /**
     * @var string|null
     */
    public $pickupPlaceIdentifier;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\Store|null
     */
    public $personalPickupStore;

    /**
     * @var string|null
     */
    public $trackingNumber;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData|null
     */
    public $withdrawalRequestData;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public $totalPrice;

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Model\Pricing\Price>
     */
    public $totalPricesByItemType = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public $customerUser;

    /**
     * @var string|null
     */
    public $promoCode;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var bool
     */
    public $freeTransportAndPaymentApplied;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public $totalProductPriceAdjustmentsDiscount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public $basicTotalItemsPrice;

    public function __construct()
    {
        $this->deliveryAddressSameAsBillingAddress = false;
        $this->paymentTransactionRefunds = [];
        $this->heurekaAgreement = false;
        $this->isCompanyCustomer = false;
        $this->freeTransportAndPaymentApplied = false;

        $this->totalPrice = Price::zero();
        $this->basicTotalItemsPrice = Price::zero();
        $this->totalProductPriceAdjustmentsDiscount = Price::zero();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public function getNewItemsWithoutTransportAndPayment()
    {
        $newItemsWithoutTransportAndPayment = [];

        foreach ($this->getItemsWithoutTransportAndPayment() as $index => $item) {
            if (str_starts_with((string)$index, self::NEW_ITEM_PREFIX)) {
                $newItemsWithoutTransportAndPayment[] = $item;
            }
        }

        return $newItemsWithoutTransportAndPayment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public function getItemsByType(string $type): array
    {
        return array_values(array_filter(
            $this->items,
            fn (OrderItemData $item) => $item->type === $type,
        ));
    }

    public function addTotalPrice(PriceInterface $priceToAdd, string $type): void
    {
        $this->totalPricesByItemType[$type] = $this->totalPricesByItemType[$type]->add($priceToAdd);
        $this->totalPrice = $this->totalPrice->add($priceToAdd);
    }

    public function addBasicTotalItemsPrice(PriceInterface $priceToAdd): void
    {
        $this->basicTotalItemsPrice = $this->basicTotalItemsPrice->add($priceToAdd);
    }

    public function addTotalProductPriceAdjustmentsDiscount(PriceInterface $priceToAdd): void
    {
        $this->totalProductPriceAdjustmentsDiscount = $this->totalProductPriceAdjustmentsDiscount->add($priceToAdd);
    }

    public function addItem(OrderItemData $item): void
    {
        $count = 0;

        foreach ($this->items as $key => $value) {
            if (str_starts_with((string)$key, self::NEW_ITEM_PREFIX)) {
                $count++;
            }
        }

        $this->items[self::NEW_ITEM_PREFIX . $count] = $item;
    }

    /**
     * @param string[] $itemTypes
     */
    public function getTotalPriceForItemTypes(array $itemTypes): PriceInterface
    {
        $totalPrice = new Price(Money::zero(), Money::zero());

        foreach ($itemTypes as $itemType) {
            $totalPrice = $totalPrice->add($this->totalPricesByItemType[$itemType]);
        }

        return $totalPrice;
    }

    public function getProductsTotalPriceAfterAppliedDiscounts(): PriceInterface
    {
        return $this->getTotalPriceForItemTypes([
            OrderItemTypeEnum::TYPE_PRODUCT,
            OrderItemTypeEnum::TYPE_PRODUCT_GIFT,
            OrderItemTypeEnum::TYPE_DISCOUNT,
            OrderItemTypeEnum::TYPE_PROMOTION,
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public function getItemsWithoutTransportAndPayment(): array
    {
        return array_filter(
            $this->items,
            fn (OrderItemData $item) => !in_array($item->type, [OrderItemTypeEnum::TYPE_TRANSPORT, OrderItemTypeEnum::TYPE_PAYMENT], true),
        );
    }

    /**
     * Method is used for \Shopsys\FrameworkBundle\Form\OrderItemsType to set items without transport and payment during order edit
     *
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[] $items
     */
    public function setItemsWithoutTransportAndPayment(array $items): void
    {
        $this->items = $items;
    }

    public function getTotalDiscountPrice(): PriceInterface
    {
        return $this->totalProductPriceAdjustmentsDiscount->add($this->getPromoCodeDiscountPrice());
    }

    public function getPromoCodeDiscountPrice(): PriceInterface
    {
        return $this->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT])->inverse();
    }

    public function fillCurrencyFieldsFromCurrency(Currency $currency): void
    {
        $this->currencyCode = $currency->getCode();
        $this->currencyRoundingType = $currency->getRoundingType();
        $this->currencyRoundingPlacesPriceWithoutVat = $currency->getRoundingPlacesPriceWithoutVat();
        $this->currencyMinFractionDigits = $currency->getMinFractionDigits();
    }
}
