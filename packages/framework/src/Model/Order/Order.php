<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\ExcludeLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportData;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
class Order
{
    public const int MAX_TRANSACTION_COUNT = 2;

    protected const array SORTED_TYPES = [
        OrderItemTypeEnum::TYPE_PRODUCT,
        OrderItemTypeEnum::TYPE_DISCOUNT,
        OrderItemTypeEnum::TYPE_PAYMENT,
        OrderItemTypeEnum::TYPE_TRANSPORT,
        OrderItemTypeEnum::TYPE_ROUNDING,
    ];

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, unique=true, nullable=true)
     */
    protected $number;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(nullable=true, name="customer_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $customerUser;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $orderPaymentStatusPageValidFrom;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Order\Item\OrderItem",
     *     mappedBy="order",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $items;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $status;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $totalPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $totalPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    #[ExcludeLog]
    protected $totalProductPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    #[ExcludeLog]
    protected $totalProductPriceWithVat;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $street;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $city;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $country;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deliveryAddressSameAsBillingAddress;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryFirstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryLastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryCompanyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $deliveryTelephone;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryStreet;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $deliveryCity;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $deliveryPostcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="delivery_country_id", referencedColumnName="id", nullable=true)
     */
    protected $deliveryCountry;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, unique=true, nullable=true)
     */
    protected $urlHash;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=true, name="administrator_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdAsAdministrator;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $createdAsAdministratorName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $origin;

    /**
     * @var string|null
     * @ORM\Column(type="guid", nullable=true)
     */
    protected $orderPaymentStatusPageValidityHash;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction", mappedBy="order", cascade={"persist"})
     */
    protected $paymentTransactions;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $goPayBankSwift;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $heurekaAgreement;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $pickupPlaceIdentifier;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $trackingNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $promoCode;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     * @ORM\ManyToMany(
     *     targetEntity="\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode"
     * )
     * @ORM\JoinTable(name="order_promo_codes")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $promoCodes;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $transportWatchedPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $paymentWatchedPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param string|null $orderNumber
     * @param string|null $urlHash
     */
    public function __construct(
        OrderData $orderData,
        ?string $orderNumber = null,
        ?string $urlHash = null,
    ) {
        $this->fillCommonFields($orderData);

        $this->transport = $orderData->transport;
        $this->payment = $orderData->payment;

        $this->items = new ArrayCollection();

        $this->number = $orderNumber;

        $this->customerUser = $orderData->customerUser;
        $this->deleted = false;

        $this->createdAt = $orderData->createdAt;
        $this->domainId = $orderData->domainId;
        $this->urlHash = $urlHash;
        $this->currency = $orderData->currency;
        $this->createdAsAdministrator = $orderData->createdAsAdministrator;
        $this->createdAsAdministratorName = $orderData->createdAsAdministratorName;
        $this->origin = $orderData->origin;
        $this->uuid = $orderData->uuid ?: Uuid::uuid4()->toString();
        $this->setTotalPrices(Price::zero(), Price::zero());
        $this->orderPaymentStatusPageValidityHash = Uuid::uuid4()->toString();
        $this->paymentTransactions = new ArrayCollection();
        $this->goPayBankSwift = $orderData->goPayBankSwift;
        $this->pickupPlaceIdentifier = $orderData->pickupPlaceIdentifier;
        $this->promoCodes = new ArrayCollection();

        $this->setModifiedNow();
    }

    /**
     * @return string|null
     */
    public function getGoPayBankSwift()
    {
        return $this->goPayBankSwift;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getGoPayTransactions(): array
    {
        $paymentTransactions = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()?->isGoPay()) {
                $paymentTransactions[] = $paymentTransaction;
            }
        }

        return $paymentTransactions;
    }

    /**
     * @return bool
     */
    public function isMaxTransactionCountReached(): bool
    {
        return $this->paymentTransactions->count() >= static::MAX_TRANSACTION_COUNT;
    }

    /**
     * @return string[]
     */
    public function getGoPayTransactionStatusesIndexedByGoPayId(): array
    {
        $returnArray = [];

        foreach ($this->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getPayment()?->isGoPay()) {
                $returnArray[$paymentTransaction->getExternalPaymentIdentifier()] = $paymentTransaction->getExternalPaymentStatus();
            }
        }

        return $returnArray;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        foreach ($this->paymentTransactions as $paymentTransaction) {
            if ($paymentTransaction->isPaid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getPaymentTransactionsCount(): int
    {
        return $this->paymentTransactions->count();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     */
    public function addPaymentTransaction(PaymentTransaction $paymentTransaction): void
    {
        $this->paymentTransactions->add($paymentTransaction);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction[]
     */
    public function getPaymentTransactions()
    {
        return $this->paymentTransactions->getValues();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->count() === 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editData(OrderData $orderData): void
    {
        $this->fillCommonFields($orderData);

        $this->editOrderTransport($orderData);
        $this->editOrderPayment($orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillCommonFields(OrderData $orderData): void
    {
        $this->firstName = $orderData->firstName;
        $this->lastName = $orderData->lastName;
        $this->email = $orderData->email;
        $this->telephone = $orderData->telephone;
        $this->street = $orderData->street;
        $this->city = $orderData->city;
        $this->postcode = $orderData->postcode;
        $this->country = $orderData->country;
        $this->note = $orderData->note;
        $this->trackingNumber = $orderData->trackingNumber;

        if ($orderData->isCompanyCustomer === true) {
            $this->setCompanyInfo(
                $orderData->companyName,
                $orderData->companyNumber,
                $orderData->companyTaxNumber,
            );
        } else {
            $this->setCompanyInfo();
        }

        $this->status = $orderData->status;
        $this->heurekaAgreement = $orderData->heurekaAgreement;

        $this->setDeliveryAddress($orderData);

        $this->promoCode = $orderData->promoCode;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editOrderTransport(OrderData $orderData): void
    {
        $orderTransportData = $orderData->orderTransport;
        if ($orderTransportData === null) {
            $this->transport = null;
        } else {
            $this->transport = $orderTransportData->transport;
            $this->getTransportItem()->edit($orderTransportData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function editOrderPayment(OrderData $orderData): void
    {
        $orderPaymentData = $orderData->orderPayment;
        if ($orderPaymentData === null) {
            $this->payment = null;
        } else {
            $this->payment = $orderPaymentData->payment;
            $this->getPaymentItem()->edit($orderPaymentData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setDeliveryAddress(OrderData $orderData): void
    {
        $this->deliveryAddressSameAsBillingAddress = $orderData->deliveryAddressSameAsBillingAddress;

        if ($orderData->deliveryAddressSameAsBillingAddress) {
            $this->deliveryFirstName = $orderData->firstName;
            $this->deliveryLastName = $orderData->lastName;
            $this->deliveryCompanyName = $orderData->companyName;
            $this->deliveryTelephone = $orderData->telephone;
            $this->deliveryStreet = $orderData->street;
            $this->deliveryCity = $orderData->city;
            $this->deliveryPostcode = $orderData->postcode;
            $this->deliveryCountry = $orderData->country;
        } else {
            $this->deliveryFirstName = $orderData->deliveryFirstName;
            $this->deliveryLastName = $orderData->deliveryLastName;
            $this->deliveryCompanyName = $orderData->deliveryCompanyName;
            $this->deliveryTelephone = $orderData->deliveryTelephone;
            $this->deliveryStreet = $orderData->deliveryStreet;
            $this->deliveryCity = $orderData->deliveryCity;
            $this->deliveryPostcode = $orderData->deliveryPostcode;
            $this->deliveryCountry = $orderData->deliveryCountry;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $item
     */
    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $item
     */
    public function removeItem(OrderItem $item): void
    {
        if ($item->isTypeTransport()) {
            $this->transport = null;
        }

        if ($item->isTypePayment()) {
            $this->payment = null;
        }
        $this->items->removeElement($item);
        $this->setModifiedNow();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @param string|null $companyName
     * @param string|null $companyNumber
     * @param string|null $companyTaxNumber
     */
    public function setCompanyInfo($companyName = null, $companyNumber = null, $companyTaxNumber = null): void
    {
        $this->companyName = $companyName;
        $this->companyNumber = $companyNumber;
        $this->companyTaxNumber = $companyTaxNumber;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getPayment()
    {
        $payment = $this->getPaymentItem()->getPayment();

        if ($payment === null) {
            throw new OrderItemNotFoundException('Order item `payment` not found.');
        }

        return $payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getTransport()
    {
        // TODO it would be nice to have some "findTransport" and "findTransportItem" methods to avoid try-catching everywhere
        $transport = $this->getTransportItem()->getTransport();

        if ($transport === null) {
            throw new OrderItemNotFoundException('Order item `transport` not found.');
        }

        return $transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalPriceWithVat()
    {
        return $this->totalPriceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalPriceWithoutVat()
    {
        return $this->totalPriceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice()
    {
        return new Price($this->totalPriceWithoutVat, $this->totalPriceWithVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTotalVatAmount(): Money
    {
        return $this->totalPriceWithVat->subtract($this->totalPriceWithoutVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $orderTotalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsTotalPrice
     */
    public function setTotalPrices(Price $orderTotalPrice, Price $productsTotalPrice): void
    {
        $this->totalPriceWithVat = $orderTotalPrice->getPriceWithVat();
        $this->totalPriceWithoutVat = $orderTotalPrice->getPriceWithoutVat();
        $this->totalProductPriceWithVat = $productsTotalPrice->getPriceWithVat();
        $this->totalProductPriceWithoutVat = $productsTotalPrice->getPriceWithoutVat();
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function markAsDeleted()
    {
        $this->deleted = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    #[EntityLogIdentify]
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getOrderPaymentStatusPageValidFrom()
    {
        return $this->orderPaymentStatusPageValidFrom;
    }

    public function setOrderPaymentStatusPageValidFromNow(): void
    {
        $this->orderPaymentStatusPageValidFrom = new DateTime();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsSortedWithRelatedItems(): array
    {
        $itemsSortedWithRelatedItems = [];

        $items = clone $this->items;

        foreach (static::SORTED_TYPES as $orderItemType) {
            foreach ($this->getItemsByType($orderItemType) as $orderItem) {
                if (!$items->contains($orderItem)) {
                    continue;
                }

                $itemsSortedWithRelatedItems[] = $orderItem;
                $items->removeElement($orderItem);

                foreach ($orderItem->getRelatedItems() as $relatedOrderItem) {
                    if (!$items->contains($relatedOrderItem)) {
                        continue;
                    }

                    $itemsSortedWithRelatedItems[] = $relatedOrderItem;
                    $items->removeElement($relatedOrderItem);
                }
            }
        }

        return $itemsSortedWithRelatedItems;
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsByType(string $type): array
    {
        return array_filter(
            $this->items->getValues(),
            fn (OrderItem $item) => $item->isType($type),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getProductItems(): array
    {
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function getTransportItem(): OrderItem
    {
        $transports = $this->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT);

        if (count($transports) === 0) {
            throw new OrderItemNotFoundException('Order item `transport` not found.');
        }

        return reset($transports);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function getPaymentItem(): OrderItem
    {
        $payments = $this->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        if (count($payments) === 0) {
            throw new OrderItemNotFoundException('Order item `payment` not found.');
        }

        return reset($payments);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getDiscountItems(): array
    {
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getRoundingItems(): array
    {
        return $this->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getItemsWithoutTransportAndPayment()
    {
        $itemsWithoutTransportAndPayment = [];

        foreach ($this->getItems() as $orderItem) {
            if (!($orderItem->isTypeTransport() || $orderItem->isTypePayment())) {
                $itemsWithoutTransportAndPayment[] = $orderItem;
            }
        }

        return $itemsWithoutTransportAndPayment;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return string|null
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * @return string|null
     */
    public function getCompanyTaxNumber()
    {
        return $this->companyTaxNumber;
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return bool
     */
    public function isDeliveryAddressSameAsBillingAddress()
    {
        return $this->deliveryAddressSameAsBillingAddress;
    }

    /**
     * @return string
     */
    public function getDeliveryFirstName()
    {
        return $this->deliveryFirstName;
    }

    /**
     * @return string
     */
    public function getDeliveryLastName()
    {
        return $this->deliveryLastName;
    }

    /**
     * @return string|null
     */
    public function getDeliveryCompanyName()
    {
        return $this->deliveryCompanyName;
    }

    /**
     * @return string|null
     */
    public function getDeliveryTelephone()
    {
        return $this->deliveryTelephone;
    }

    /**
     * @return string|null
     */
    public function getDeliveryStreet()
    {
        return $this->deliveryStreet;
    }

    /**
     * @return string|null
     */
    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    /**
     * @return string|null
     */
    public function getDeliveryPostcode()
    {
        return $this->deliveryPostcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getDeliveryCountry()
    {
        return $this->deliveryCountry;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getUrlHash()
    {
        return $this->urlHash;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function getCreatedAsAdministrator()
    {
        return $this->createdAsAdministrator;
    }

    /**
     * @return string|null
     */
    public function getCreatedAsAdministratorName()
    {
        return $this->createdAsAdministratorName;
    }

    /**
     * @return string|null
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status?->getType() === OrderStatusTypeEnum::TYPE_CANCELED;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderEditResult
     */
    public function edit(OrderData $orderData): OrderEditResult
    {
        $statusChanged = $this->getStatus() !== $orderData->status;
        $this->editData($orderData);

        return new OrderEditResult($statusChanged);
    }

    /**
     * @return string|null
     */
    public function getOrderPaymentStatusPageValidityHash()
    {
        return $this->orderPaymentStatusPageValidityHash;
    }

    public function setOrderPaymentStatusPageValidityHashToNull(): void
    {
        $this->orderPaymentStatusPageValidityHash = null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalProductsPrice(): Price
    {
        return new Price($this->totalProductPriceWithoutVat, $this->totalProductPriceWithVat);
    }

    /**
     * @return bool
     */
    public function isHeurekaAgreement()
    {
        return $this->heurekaAgreement;
    }

    /**
     * @return string|null
     */
    public function getPickupPlaceIdentifier()
    {
        return $this->pickupPlaceIdentifier;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber($trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string|null
     */
    public function getTrackingUrl(): ?string
    {
        $trackingUrl = $this->transport->getTrackingUrl();
        $trackingNumber = $this->getTrackingNumber();

        if ($trackingUrl === null || $trackingNumber === null) {
            return null;
        }

        return strtr($trackingUrl, [
            OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => $trackingNumber,
        ]);
    }

    /**
     * @return string|null
     */
    public function getPromoCode()
    {
        return $this->promoCode;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function setCustomerUser($customerUser): void
    {
        $this->customerUser = $customerUser;
    }

    /**
     * @param \DateTime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    public function setModifiedNow(): void
    {
        $this->modifiedAt = new DateTime();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProducts(): array
    {
        $quantifiedProducts = [];

        foreach ($this->getProductItems() as $item) {
            try {
                $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
            } catch (ProductNotFoundException) {
                continue;
            }
        }

        return $quantifiedProducts;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProducts(): array
    {
        return array_map(
            static fn (QuantifiedProduct $quantifiedProduct) => $quantifiedProduct->getProduct(),
            $this->getQuantifiedProducts(),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAllAppliedPromoCodes()
    {
        return $this->promoCodes->getValues();
    }

    /**
     * @param int $promoCodeId
     */
    public function removePromoCodeById(int $promoCodeId): void
    {
        foreach ($this->promoCodes as $promoCode) {
            if ($promoCode->getId() === $promoCodeId) {
                $this->promoCodes->removeElement($promoCode);
                $this->setModifiedNow();

                return;
            }
        }
        $message = 'Promo code with ID = ' . $promoCodeId . ' is not applied.';

        throw new InvalidCartItemException($message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $paymentWatchedPrice
     */
    public function setPaymentWatchedPrice($paymentWatchedPrice): void
    {
        $this->paymentWatchedPrice = $paymentWatchedPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $transportWatchedPrice
     */
    public function setTransportWatchedPrice($transportWatchedPrice): void
    {
        $this->transportWatchedPrice = $transportWatchedPrice;
    }

    public function getTransportWatchedPrice(): ?Money
    {
        return $this->transportWatchedPrice;
    }

    public function getPaymentWatchedPrice(): ?Money
    {
        return $this->paymentWatchedPrice;
    }

    public function unsetCartTransport(): void
    {
        $this->transport = null;
        $this->transportWatchedPrice = null;
        $this->pickupPlaceIdentifier = null;
        $this->setModifiedNow();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportData $cartTransportData
     */
    public function editCartTransport(CartTransportData $cartTransportData): void
    {
        $this->transport = $cartTransportData->transport;
        $this->transportWatchedPrice = $cartTransportData->watchedPrice;
        $this->pickupPlaceIdentifier = $cartTransportData->pickupPlaceIdentifier;
        $this->setModifiedNow();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData $cartPaymentData
     */
    public function editCartPayment(CartPaymentData $cartPaymentData): void
    {
        $this->payment = $cartPaymentData->payment;
        $this->paymentWatchedPrice = $cartPaymentData->watchedPrice;
        $this->goPayBankSwift = $cartPaymentData->goPayBankSwift;
        $this->setModifiedNow();
    }

    public function unsetCartPayment(): void
    {
        $this->payment = null;
        $this->paymentWatchedPrice = null;
        $this->goPayBankSwift = null;
        $this->setModifiedNow();
    }

    public function getTotalWeight(): int
    {
        $totalWeight = 0;

        foreach ($this->getProductItems() as $item) {
            try {
                $product = $item->getProduct();
                $totalWeight += $product->getWeight() * $item->getQuantity();
            } catch (ProductNotFoundException $productNotFoundException) {
                continue;
            }
        }

        return $totalWeight;
    }
    public function unsetPickupPlaceIdentifier(): void
    {
        $this->pickupPlaceIdentifier = null;
    }

    /**
     * @param string $promoCodeCode
     * @return bool
     */
    public function isPromoCodeApplied(string $promoCodeCode): bool
    {
        return $this->promoCodes->exists(
            static function ($key, PromoCode $promoCode) use ($promoCodeCode) {
                return $promoCode->getCode() === $promoCodeCode;
            },
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function applyPromoCode(PromoCode $promoCode): void
    {
        if (!$this->promoCodes->contains($promoCode)) {
            $this->promoCodes->add($promoCode);
            $this->setModifiedNow();
        }
    }
}
