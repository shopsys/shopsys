<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderData
{
    public const string NEW_ITEM_PREFIX = 'new_';

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public $payment;

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
     * @var \DateTime|null
     */
    public $createdAt;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public $currency;

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

    public function __construct()
    {
        $this->deliveryAddressSameAsBillingAddress = false;
        $this->paymentTransactionRefunds = [];
        $this->heurekaAgreement = false;
        $this->isCompanyCustomer = false;

        $this->totalPrice = new Price(Money::zero(), Money::zero());
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
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public function getItemsByType(string $type): array
    {
        return array_values(array_filter(
            $this->items,
            fn (OrderItemData $item) => $item->type === $type,
        ));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $priceToAdd
     * @param string $type
     */
    public function addTotalPrice(Price $priceToAdd, string $type): void
    {
        $this->totalPricesByItemType[$type] = $this->totalPricesByItemType[$type]->add($priceToAdd);
        $this->totalPrice = $this->totalPrice->add($priceToAdd);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $item
     */
    public function addItem(OrderItemData $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param string[] $itemTypes
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceForItemTypes(array $itemTypes): Price
    {
        $totalPrice = new Price(Money::zero(), Money::zero());

        foreach ($itemTypes as $itemType) {
            $totalPrice = $totalPrice->add($this->totalPricesByItemType[$itemType]);
        }

        return $totalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getProductsTotalPriceAfterAppliedDiscounts(): Price
    {
        return $this->getTotalPriceForItemTypes([
            OrderItemTypeEnum::TYPE_PRODUCT,
            OrderItemTypeEnum::TYPE_DISCOUNT,
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceWithoutDiscountTransportAndPayment(): Price
    {
        return $this->totalPrice
            ->subtract($this->totalPricesByItemType[OrderItemTypeEnum::TYPE_TRANSPORT])
            ->subtract($this->totalPricesByItemType[OrderItemTypeEnum::TYPE_PAYMENT])
            ->subtract($this->totalPricesByItemType[OrderItemTypeEnum::TYPE_DISCOUNT]);
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
}
